<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class JournalEntry extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'journal_number',
        'entry_date',
        'fiscal_year_id',
        'reference_type',
        'reference_id',
        'description',
        'type',
        'status',
        'total_debit',
        'total_credit',
        'is_balanced',
        'metadata',
        'created_by',
        'posted_by',
        'posted_at',
        'is_locked',
        'locked_at',
        'locked_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'locked_at' => 'datetime',
        'is_balanced' => 'boolean',
        'is_locked' => 'boolean',
        'metadata' => 'array'
    ];

    // =========== RELATIONSHIPS ===========

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // =========== BOOT EVENTS ===========

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($entry) {
            // Auto-set fiscal year based on entry date
            if (!$entry->fiscal_year_id && $entry->entry_date) {
                $fiscalYear = FiscalYear::where('start_date', '<=', $entry->entry_date)
                    ->where('end_date', '>=', $entry->entry_date)
                    ->first();
                    
                if ($fiscalYear) {
                    $entry->fiscal_year_id = $fiscalYear->id;
                }
            }
        });

        // AFTER POSTING - Update account balances
        static::saved(function ($entry) {
            if ($entry->status === 'posted' && !$entry->getOriginal('posted_at')) {
                $entry->updateAccountBalances();
            }
        });

        // When status changes to posted
        static::updating(function ($entry) {
            // Check if trying to modify posted entry
            if ($entry->isDirty() && $entry->getOriginal('status') === 'posted') {
                // Allow only locking/unlocking for posted entries
                $allowedFields = ['is_locked', 'locked_at', 'locked_by'];
                $dirtyFields = array_keys($entry->getDirty());
                
                foreach ($dirtyFields as $field) {
                    if (!in_array($field, $allowedFields)) {
                        throw new \Exception("Posted entries cannot be modified. Only lock/unlock operations are allowed.");
                    }
                }
            }
            
            // Auto-lock when status changes to posted
            if ($entry->isDirty('status') && $entry->status === 'posted') {
                $entry->is_locked = true;
                $entry->locked_at = now();
                $entry->locked_by = auth()->id() ?? $entry->posted_by;
            }
        });

        // Prevent deletion of posted entries
        static::deleting(function ($entry) {
            if ($entry->status === 'posted') {
                throw new \Exception("Posted entries cannot be deleted.");
            }
        });
    }

    /**
     * Update account balances when journal is posted
     */
    public function updateAccountBalances()
    {
        DB::transaction(function () {
            foreach ($this->lines as $line) {
                $account = $line->account;
                
                if ($account) {
                    // Recalculate balance from ALL journal entries
                    $account->recalculateBalance();
                }
            }
        });
    }

    /**
     * Verify if entry is balanced
     */
    public function isBalanced()
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    /**
     * Lock a posted entry to prevent modifications
     */
    public function lock()
    {
        if ($this->status !== 'posted') {
            throw new \Exception("Only posted entries can be locked.");
        }
        
        if ($this->is_locked) {
            throw new \Exception("Entry is already locked.");
        }
        
        $this->is_locked = true;
        $this->locked_at = now();
        $this->locked_by = auth()->id();
        $this->saveQuietly(); // Use saveQuietly to avoid triggering events
        
        return $this;
    }

    /**
     * 🔒 FIXED: Unlock only allowed for NON-POSTED entries
     */
    public function unlock()
    {
        // 🚨 CRITICAL FIX: Posted entries cannot be unlocked
        if ($this->status === 'posted') {
            throw new \Exception('Posted entries are permanently locked and cannot be unlocked.');
        }

        if (!$this->is_locked) {
            throw new \Exception("Entry is not locked.");
        }
        
        $this->is_locked = false;
        $this->locked_at = null;
        $this->locked_by = null;
        $this->saveQuietly();
        
        return $this;
    }

    /**
     * Check if entry can be edited
     */
    public function canEdit()
    {
        return $this->status !== 'posted' && !$this->is_locked;
    }

    // =========== SCOPES ===========

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeEditable($query)
    {
        return $query->where('status', '!=', 'posted')
                     ->where('is_locked', false);
    }

    public function scopeForFiscalYear($query, $fiscalYearId)
    {
        return $query->where('fiscal_year_id', $fiscalYearId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;


class ChartOfAccount extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'category',
        'department_id',
        'parent_code',
        'level',
        'is_header',
        'is_active',
        'opening_balance',
        'current_balance',
        'currency',
        'description',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    // =========== RELATIONSHIPS ===========
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_code', 'account_code');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_code', 'account_code');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function journalEntries()
    {
        return $this->belongsToMany(JournalEntry::class, 'journal_entry_lines', 'account_id', 'journal_entry_id')
                    ->withPivot('debit', 'credit', 'description')
                    ->withTimestamps();
    }

    // =========== BALANCE RECALCULATION ===========
    
    /**
     * Calculate balance from journal entries
     * This is the SOURCE OF TRUTH
     */
    public function calculateBalanceFromJournal()
    {
        $debit = $this->journalLines()->sum('debit');
        $credit = $this->journalLines()->sum('credit');
        
        // For Asset and Expense: Debit increases, Credit decreases
        if (in_array($this->account_type, ['asset', 'expense'])) {
            return $this->opening_balance + $debit - $credit;
        }
        
        // For Liability, Equity, Revenue: Credit increases, Debit decreases
        return $this->opening_balance + $credit - $debit;
    }

    /**
     * Recalculate and update current_balance
     */
    public function recalculateBalance()
    {
        $calculated = $this->calculateBalanceFromJournal();
        
        if (abs($calculated - $this->current_balance) > 0.01) {
            $this->current_balance = $calculated;
            $this->saveQuietly(); // Save without triggering events
            
            return true;
        }
        
        return false;
    }

    /**
     * Recalculate all accounts
     */
    public static function recalculateAllBalances()
    {
        $accounts = self::all();
        $count = 0;
        $errors = [];
        
        foreach ($accounts as $account) {
            try {
                if ($account->recalculateBalance()) {
                    $count++;
                }
            } catch (\Exception $e) {
                $errors[] = "Account {$account->account_code}: " . $e->getMessage();
            }
        }
        
        return [
            'updated' => $count,
            'total' => $accounts->count(),
            'errors' => $errors
        ];
    }

    /**
     * Verify if balance is correct
     */
    public function isBalanceCorrect()
    {
        $calculated = $this->calculateBalanceFromJournal();
        return abs($calculated - $this->current_balance) < 0.01;
    }

    // =========== SCOPES ===========

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTransactional($query)
    {
        return $query->where('is_header', false);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // =========== ACCESSORS ===========

    public function getTypeNameAttribute()
    {
        $types = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expense'
        ];
        
        return $types[$this->account_type] ?? ucfirst($this->account_type);
    }

    public function getCategoryNameAttribute()
    {
        $categories = [
            'current_asset' => 'Current Asset',
            'fixed_asset' => 'Fixed Asset',
            'current_liability' => 'Current Liability',
            'long_term_liability' => 'Long Term Liability',
            'owners_equity' => 'Owner\'s Equity',
            'operating_revenue' => 'Operating Revenue',
            'other_revenue' => 'Other Revenue',
            'operating_expense' => 'Operating Expense',
            'administrative_expense' => 'Administrative Expense',
            'selling_expense' => 'Selling Expense',
            'other_expense' => 'Other Expense'
        ];
        
        return $categories[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}
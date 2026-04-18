<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiscalYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'notes',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * RELATIONSHIPS
     */
    
    // Created by user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // All journal entries in this fiscal year
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'fiscal_year_id');
    }

    // ✅ ADD THIS: All department budgets for this fiscal year
    public function budgets()
    {
        return $this->hasMany(DepartmentBudget::class, 'fiscal_year', 'name');
    }

    /**
     * SCOPES
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * HELPERS
     */
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'open' => 'badge bg-success',
            'closed' => 'badge bg-danger',
            'draft' => 'badge bg-warning'
        ];

        $class = $badges[$this->status] ?? 'badge bg-secondary';
        return "<span class=\"{$class}\">" . ucfirst($this->status) . "</span>";
    }

    public function getActiveBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }
}
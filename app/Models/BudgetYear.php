<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'total_budget',
        'total_allocated',
        'total_utilized',
        'total_remaining',
        'notes',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'total_budget' => 'decimal:2',
        'total_allocated' => 'decimal:2',
        'total_utilized' => 'decimal:2',
        'total_remaining' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function departmentBudgets()
    {
        return $this->hasMany(DepartmentBudget::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(BudgetApproval::class);
    }

    public function revisions()
    {
        return $this->hasMany(BudgetRevision::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now())
                     ->where('status', 'active');
    }

    // Methods
    public static function generateName()
    {
        $year = date('Y');
        $nextYear = $year + 1;
        return "{$year}/{$nextYear}";
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    public function canBeApproved()
    {
        return $this->status === 'draft';
    }

    public function updateTotals()
    {
        $this->total_allocated = $this->departmentBudgets()->sum('allocated_amount');
        $this->total_utilized = $this->departmentBudgets()->sum('utilized_amount');
        $this->total_remaining = $this->total_allocated - $this->total_utilized;
        $this->save();
    }

    public function getProgressAttribute()
    {
        if ($this->total_allocated == 0) return 0;
        return round(($this->total_utilized / $this->total_allocated) * 100, 2);
    }

    public function getStatusColorAttribute()
    {
        return [
            'draft' => 'secondary',
            'active' => 'success',
            'closed' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getFormattedTotalAttribute()
    {
        return 'TZS ' . number_format($this->total_budget, 2);
    }
}
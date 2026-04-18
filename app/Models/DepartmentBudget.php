<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentBudget extends Model
{
    use HasFactory;

    protected $table = 'department_budgets';

    protected $fillable = [
        'budget_year_id',
        'department_id',
        'budget_category_id',
        'allocated_amount',
        'utilized_amount',
        'remaining_amount',
        'percentage_utilized',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'percentage_utilized' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relationships
    public function budgetYear()
    {
        return $this->belongsTo(BudgetYear::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    public function items()
    {
        return $this->hasMany(BudgetItem::class, 'budget_category_id', 'budget_category_id')
                    ->where('budget_year_id', $this->budget_year_id)
                    ->where('department_id', $this->department_id);
    }

    // Methods
    public function updateUtilization($amount)
    {
        $this->utilized_amount += $amount;
        $this->remaining_amount = $this->allocated_amount - $this->utilized_amount;
        $this->percentage_utilized = $this->allocated_amount > 0 
            ? round(($this->utilized_amount / $this->allocated_amount) * 100, 2) 
            : 0;
        $this->save();
    }

    public function addAllocation($amount)
    {
        $this->allocated_amount += $amount;
        $this->remaining_amount = $this->allocated_amount - $this->utilized_amount;
        $this->percentage_utilized = $this->allocated_amount > 0 
            ? round(($this->utilized_amount / $this->allocated_amount) * 100, 2) 
            : 0;
        $this->save();
    }

    public function getStatusColorAttribute()
    {
        if ($this->percentage_utilized >= 100) return 'danger';
        if ($this->percentage_utilized >= 80) return 'warning';
        if ($this->percentage_utilized >= 50) return 'info';
        return 'success';
    }

    public function getFormattedAllocatedAttribute()
    {
        return 'TZS ' . number_format($this->allocated_amount, 2);
    }

    public function getFormattedUtilizedAttribute()
    {
        return 'TZS ' . number_format($this->utilized_amount, 2);
    }

    public function getFormattedRemainingAttribute()
    {
        return 'TZS ' . number_format($this->remaining_amount, 2);
    }
}
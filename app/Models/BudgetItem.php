<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory;

    protected $table = 'budget_items';

    protected $fillable = [
        'budget_year_id',
        'department_id',
        'budget_category_id',
        'item_code',
        'description',
        'unit_price',
        'quantity',
        'total_amount',
        'justification',
        'metadata'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'total_amount' => 'decimal:2',
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

    public function departmentBudget()
    {
        return $this->hasOne(DepartmentBudget::class, 'budget_year_id', 'budget_year_id')
                    ->where('department_id', $this->department_id)
                    ->where('budget_category_id', $this->budget_category_id);
    }

    // Scopes
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('budget_category_id', $categoryId);
    }

    // Methods
    public function calculateTotal()
    {
        $this->total_amount = $this->unit_price * $this->quantity;
        $this->save();
    }

    public function getFormattedUnitPriceAttribute()
    {
        return 'TZS ' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return 'TZS ' . number_format($this->total_amount, 2);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'is_active',
        'sort_order',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array'
    ];

    // Relationships
    public function departmentBudgets()
    {
        return $this->hasMany(DepartmentBudget::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRevenue($query)
    {
        return $query->where('type', 'revenue');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeCapital($query)
    {
        return $query->where('type', 'capital');
    }

    // Methods
    public static function generateCode($type)
    {
        $prefix = match($type) {
            'revenue' => 'REV',
            'expense' => 'EXP',
            'capital' => 'CAP',
            default => 'CAT'
        };
        
        $last = self::where('code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($last) {
            $number = intval(substr($last->code, 3)) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getTypeColorAttribute()
    {
        return [
            'revenue' => 'success',
            'expense' => 'danger',
            'capital' => 'info'
        ][$this->type] ?? 'secondary';
    }

    public function getTypeTextAttribute()
    {
        return ucwords($this->type);
    }
}
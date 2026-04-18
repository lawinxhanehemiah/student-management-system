<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetRevision extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'budget_revisions';

    protected $fillable = [
        'revision_number',
        'budget_year_id',
        'department_id',
        'type',
        'amount',
        'reason',
        'status',
        'old_values',
        'new_values',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
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

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public static function generateRevisionNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $last = self::where('revision_number', 'like', "BR/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($last) {
            $parts = explode('/', $last->revision_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "BR/{$year}/{$month}/{$sequence}";
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function getTypeTextAttribute()
    {
        return match($this->type) {
            'increase' => 'Budget Increase',
            'decrease' => 'Budget Decrease',
            'transfer' => 'Budget Transfer',
            default => ucwords($this->type)
        };
    }

    public function getTypeColorAttribute()
    {
        return [
            'increase' => 'success',
            'decrease' => 'danger',
            'transfer' => 'info'
        ][$this->type] ?? 'secondary';
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getFormattedAmountAttribute()
    {
        return 'TZS ' . number_format($this->amount, 2);
    }
}
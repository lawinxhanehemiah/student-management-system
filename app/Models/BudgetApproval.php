<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetApproval extends Model
{
    use HasFactory;

    protected $table = 'budget_approvals';

    protected $fillable = [
        'budget_year_id',
        'department_id',
        'level',
        'status',
        'comments',
        'approved_by',
        'approved_at',
        'metadata'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
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

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Methods
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

    public function getLevelTextAttribute()
    {
        return match($this->level) {
            'hod' => 'Head of Department',
            'finance' => 'Finance Manager',
            'director' => 'Director',
            default => ucwords($this->level)
        };
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ][$this->status] ?? 'secondary';
    }
}
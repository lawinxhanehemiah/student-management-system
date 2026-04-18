<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLevel extends Model
{
    protected $fillable = [
        'name', 'code', 'level_order', 'approver_type',
        'approver_value', 'min_amount', 'max_amount',
        'is_active', 'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2'
    ];

    public function requisitionApprovals()
    {
        return $this->hasMany(RequisitionApproval::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAmount($query, $amount)
    {
        return $query->where('min_amount', '<=', $amount)
            ->where(function($q) use ($amount) {
                $q->where('max_amount', '>=', $amount)
                  ->orWhereNull('max_amount');
            });
    }

    public function getApproverNameAttribute()
    {
        if ($this->approver_type === 'role') {
            return "Role: " . $this->approver_value;
        } elseif ($this->approver_type === 'user') {
            $user = User::find($this->approver_value);
            return $user ? $user->name : "User #" . $this->approver_value;
        } else {
            return "Department Head";
        }
    }
}
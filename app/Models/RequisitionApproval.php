<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionApproval extends Model
{
    protected $fillable = [
        'requisition_id', 'approval_level_id', 'approver_id',
        'status', 'comments', 'action_date'
    ];

    protected $casts = [
        'action_date' => 'datetime'
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function approvalLevel()
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
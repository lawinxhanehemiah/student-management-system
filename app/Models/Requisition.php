<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Requisition extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'requisition_number', 'title', 'description', 'requested_by',
        'department_id', 'request_date', 'required_date', 'priority',
        'status', 'estimated_total', 'justification', 'attachments',
        'metadata', 'created_by'
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'estimated_total' => 'decimal:2',
        'attachments' => 'array',
        'metadata' => 'array'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(RequisitionApproval::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tender()
    {
        return $this->hasOne(Tender::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function getCurrentApprovalLevelAttribute()
    {
        return $this->approvals()->where('status', 'pending')->first();
    }

    public function isFullyApproved()
    {
        return $this->status === 'approved';
    }

    public function getAuditIdentifier(): ?string
    {
        return $this->requisition_number;
    }
}
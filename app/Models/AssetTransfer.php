<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class AssetTransfer extends Model
{
    use Auditable;

    protected $fillable = [
        'asset_id', 'from_department_id', 'to_department_id',
        'from_user_id', 'to_user_id', 'from_location', 'to_location',
        'transfer_date', 'reason', 'status', 'metadata',
        'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
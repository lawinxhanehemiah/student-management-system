<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAdjustmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'created_by', 'approved_by', 'request_type',
        'amount', 'reason', 'metadata', 'status', 'approved_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
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

    public function isPending()
    {
        return $this->status === 'pending';
    }
}
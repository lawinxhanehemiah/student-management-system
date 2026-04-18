<?php
// app/Models/Refund.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'refund_number',           // <- Hii ndio ilikuwa inakosekana!
        'payment_id',
        'invoice_id',
        'student_id',
        'academic_year_id',
        'amount',
        'refund_method',
        'refund_reason',
        'description',
        'bank_name',
        'bank_account',
        'phone_number',
        'cheque_number',
        'transaction_reference',
        'status',
        'metadata',
        'requested_by',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
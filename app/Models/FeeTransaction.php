<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeTransaction extends Model
{
    use HasFactory;

    protected $table = 'fee_transactions';
    
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'control_number',
        'receipt_number',
        'transaction_type',
        'description',
        'debit',
        'credit',
        'running_balance',
        'reference_id',
        'reference_type',
        'transaction_date',
        'metadata'
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'transaction_date' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeInvoices($query)
    {
        return $query->where('transaction_type', 'INVOICE');
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'PAYMENT');
    }

    public function scopeByControlNumber($query, $controlNumber)
    {
        return $query->where('control_number', $controlNumber);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
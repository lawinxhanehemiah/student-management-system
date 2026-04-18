<?php
// app/Models/CreditNote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'credit_note_number',      // <- Hii ndio ilikuwa inakosekana!
        'invoice_id',
        'student_id',
        'academic_year_id',
        'amount',
        'remaining_amount',
        'reason',
        'description',
        'status',
        'issue_date',
        'expiry_date',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function applications()
    {
        return $this->hasMany(CreditNoteApplication::class);
    }
}
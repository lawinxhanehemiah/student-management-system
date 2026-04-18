<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionStatus extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'status', // pending, eligible, approved, rejected, promoted
        'academic_standing', // good, warning, probation, suspended
        'gpa',
        'cgpa',
        'conditions_met',
        'conditions_failed',
        'recommendation', // promote, repeat_semester, repeat_year, probation
        'reviewed_by',
        'approved_by',
        'approved_at',
        'notes'
    ];
    
    protected $casts = [
        'conditions_met' => 'array',
        'conditions_failed' => 'array',
        'approved_at' => 'datetime',
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2'
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
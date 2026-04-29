<?php
// app/Models/StudentResult.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'student_results';
    
    protected $fillable = [
        'student_id',
        'module_id',
        'academic_year_id',
        'semester',
        'raw_cw',
        'raw_exam',
        'weighted_score',
        'grade',
        'grade_point',
        'remark',
        'attempt_type',
        'attempt_no',
        'result_status',
        'workflow_status',
        'batch_id',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'calculation_snapshot'
    ];
    
    protected $casts = [
        'raw_cw' => 'decimal:2',
        'raw_exam' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'attempt_no' => 'integer',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'calculation_snapshot' => 'array'
    ];
    
    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
    
    public function batch()
    {
        return $this->belongsTo(ResultUploadBatch::class, 'batch_id');
    }
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('workflow_status', 'published');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('workflow_status', 'approved');
    }
    
    public function scopeDraft($query)
    {
        return $query->where('workflow_status', 'draft');
    }
    
    public function scopePass($query)
    {
        return $query->where('result_status', 'pass');
    }
    
    public function scopeFail($query)
    {
        return $query->where('result_status', 'fail');
    }
    
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
    
    public function scopeByModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }
    
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
    
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }
    
    // Helper methods
    public function isPass()
    {
        return $this->result_status === 'pass';
    }
    
    public function isFail()
    {
        return $this->result_status === 'fail';
    }
    
    public function isPublished()
    {
        return $this->workflow_status === 'published';
    }
    
    public function isApproved()
    {
        return $this->workflow_status === 'approved';
    }
    
    public function isDraft()
    {
        return $this->workflow_status === 'draft';
    }
}
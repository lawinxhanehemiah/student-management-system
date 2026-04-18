<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionLog extends Model
{
    protected $table = 'promotion_logs';
    
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'from_level',
        'to_level',
        'from_semester',
        'to_semester',
        'promotion_type',
        'gpa',
        'cgpa',
        'fee_cleared',
        'conditions_met',
        'conditions_failed',
        'notes',
        'promoted_by',
        'promoted_at'
    ];
    
    protected $casts = [
        'conditions_met' => 'array',
        'conditions_failed' => 'array',
        'fee_cleared' => 'boolean',
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'promoted_at' => 'datetime'
    ];
    
    /**
     * Get the student that was promoted
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    
    /**
     * Get the academic year of promotion
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    /**
     * Get the user who promoted the student
     */
    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
    
    /**
     * Scope a query to only include semester promotions
     */
    public function scopeSemesterPromotions($query)
    {
        return $query->where('promotion_type', 'semester');
    }
    
    /**
     * Scope a query to only include level promotions
     */
    public function scopeLevelPromotions($query)
    {
        return $query->where('promotion_type', 'level');
    }
    
    /**
     * Scope a query to only include promotions for a specific academic year
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
    
    /**
     * Scope a query to only include promotions for a specific student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
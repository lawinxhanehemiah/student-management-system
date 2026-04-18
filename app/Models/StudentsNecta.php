<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentsNecta extends Model
{
    protected $table = 'students_necta';
    
    protected $fillable = [
        'student_cno',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'gender',
        'division',
        'points',
        'exam_year',
        'school_id',
        'is_verified',
        'verified_at',
        'verified_by'
    ];
    
    protected $casts = [
        'is_verified' => 'boolean',
        'exam_year' => 'integer',
        'points' => 'integer',
        'verified_at' => 'datetime',
    ];
    
    /**
     * Get the grades for this student
     */
    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'student_id');
    }
    
    /**
     * Get the school for this student
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    
    /**
     * Get the verifier user
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    protected $table = 'student_grades';
    
    protected $fillable = [
        'student_id',
        'subject_id',
        'grade'
    ];
    
    /**
     * Get the student for this grade
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentsNecta::class, 'student_id');
    }
    
    /**
     * Get the subject for this grade
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(SubjectsNecta::class, 'subject_id');
    }
}
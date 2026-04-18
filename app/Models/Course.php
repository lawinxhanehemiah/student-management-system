<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'programme_id',
        'level',
        'semester',
        'credit_hours',
        'description',
        'status',
         'tutor_id'
    ];
    
    protected $casts = [
        'credit_hours' => 'integer',
        'level' => 'integer',
        'semester' => 'integer'
    ];
    
    /**
     * Get the programme that owns the course
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
    
    /**
     * Get the course registrations for this course
     */
    public function registrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }
    
    /**
     * Get the results for this course
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    
    /**
     * Scope a query to only include active courses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope a query to only include courses for a specific programme and level
     */
    public function scopeForProgrammeAndLevel($query, $programmeId, $level, $semester)
    {
        return $query->where('programme_id', $programmeId)
                     ->where('level', $level)
                     ->where('semester', $semester);
    }

    public function tutor()
{
    return $this->belongsTo(User::class, 'tutor_id');
}

public function students()
{
    return $this->belongsToMany(Student::class, 'course_student');
}

}
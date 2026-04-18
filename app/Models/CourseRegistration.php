<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseRegistration extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'academic_year_id',
        'semester',
        'registration_date',
        'status',
        'registered_by'
    ];
    
    protected $casts = [
        'registration_date' => 'datetime'
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    public function results()
    {
        return $this->hasOne(Result::class);
    }
}
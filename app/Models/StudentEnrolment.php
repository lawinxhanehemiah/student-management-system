<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrolment extends Model
{
    protected $fillable = [
        'student_id',
        'programme_id',
        'academic_year',
        'current_year',
        'current_semester',
        'enrollment_date',
        'status',
    ];

    protected $casts = [
        'current_year' => 'integer',
        'current_semester' => 'integer',
        'enrollment_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentScore extends Model
{
    protected $fillable = [
        'student_id', 'module_id', 'component_id', 'academic_year_id',
        'semester', 'score'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function component()
    {
        return $this->belongsTo(AssessmentComponent::class, 'component_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
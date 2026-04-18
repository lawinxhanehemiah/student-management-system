<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAcademicStatus extends Model
{
    use HasFactory;

    protected $table = 'student_academic_status';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'year_of_study',
        'semester',
        'status',
        'promoted_by',
        'promoted_at',
    ];

    protected $casts = [
        'promoted_at' => 'datetime',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function promoter() // HOD
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}

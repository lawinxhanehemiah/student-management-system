<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tutor_id',
        'student_id',
        'course_id',
        'date',
        'status',
        'remarks',
        'session_time',
        'semester',
        'academic_year'
    ];
    
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationship with tutor (user)
    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
    
    // Relationship with student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    // Relationship with course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    // Scope for current month
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }
    
    // Scope for today
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }
    
    // Accessor for status badge
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'present' => '<span class="badge bg-success">Present</span>',
            'absent' => '<span class="badge bg-danger">Absent</span>',
            'late' => '<span class="badge bg-warning">Late</span>',
            'excused' => '<span class="badge bg-info">Excused</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
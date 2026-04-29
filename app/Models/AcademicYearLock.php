<?php
// app/Models/AcademicYearLock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYearLock extends Model
{
    use HasFactory;
    
    protected $table = 'academic_year_locks';
    
    protected $fillable = [
        'academic_year_id',
        'locked_by',
        'locked_at',
        'unlocked_by',
        'unlocked_at',
        'unlock_reason',
        'validation_result'
    ];
    
    protected $casts = [
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'validation_result' => 'array'
    ];
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
    
    public function unlockedBy()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }
}
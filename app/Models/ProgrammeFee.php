<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgrammeFee extends Model
{
    use HasFactory;

    protected $table = 'programme_fees';

    protected $fillable = [
        'programme_id',
        'academic_year_id',
        'registration_fee',
        'semester_1_fee',
        'semester_2_fee',
        'level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'registration_fee' => 'decimal:2',
        'semester_1_fee' => 'decimal:2',
        'semester_2_fee' => 'decimal:2',
        'level' => 'integer'
    ];

    // Accessors
    public function getTotalYearFeeAttribute()
    {
        return $this->semester_1_fee + $this->semester_2_fee;
    }

    public function getTotalFeeAttribute()
    {
        return $this->registration_fee + $this->total_year_fee;
    }

    // Relationships
    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
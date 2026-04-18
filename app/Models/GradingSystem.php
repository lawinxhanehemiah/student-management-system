<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingSystem extends Model
{
    protected $fillable = [
        'name', 'min_score', 'max_score', 'grade', 'grade_point',
        'academic_year_id', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public static function getGrade($score, $academicYearId = null)
    {
        $query = self::where('is_active', true);
        if ($academicYearId) {
            $query->where(function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId)
                  ->orWhereNull('academic_year_id');
            });
        }
        return $query->where('min_score', '<=', $score)
                     ->where('max_score', '>=', $score)
                     ->first();
    }
}
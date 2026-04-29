<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingSystem extends Model
{
    protected $fillable = [
        'name',
        'program_category',
        'min_score',
        'max_score',
        'grade',
        'grade_point',
        'academic_year_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'grade_point' => 'decimal:2'
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    // Helper function kupata grading system sahihi
    public static function getGrading($programCode, $ntaLevel, $score)
    {
        $query = self::where('is_active', 1);
        
        if ($ntaLevel == 6) {
            $healthPrograms = ['CMT', 'PST', 'EHT', 'NTA', 'CLINICAL'];
            $category = in_array($programCode, $healthPrograms) ? 'health' : 'non_health';
            $query->where('program_category', $category);
            $query->where('name', 'Diploma NTA-Level 6');
        } elseif ($ntaLevel == 5) {
            $query->where('name', 'Diploma NTA-Level 5')
                  ->where('program_category', 'all');
        } else {
            $query->where('name', 'Certificate NTA-Level 4')
                  ->where('program_category', 'all');
        }
        
        return $query->where('min_score', '<=', $score)
                     ->where('max_score', '>=', $score)
                     ->first();
    }
}
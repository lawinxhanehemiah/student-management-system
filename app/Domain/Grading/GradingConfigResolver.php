<?php
// app/Domain/Grading/GradingConfigResolver.php - Pure data, no logic

namespace App\Domain\Grading;

use App\Models\Module;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\GradingSystem;
use App\Models\ProgrammeAssessmentRule;
use App\Models\ProgrammePassMark;

class GradingConfigResolver
{
    public function resolveGradingSystem(Student $student, Module $module, AcademicYear $academicYear): array
    {
        $grading = GradingSystem::where('academic_year_id', $academicYear->id)
            ->where('program_category', $student->programme->program_category)
            ->where('nta_level', $module->nta_level)
            ->where('is_active', true)
            ->orderBy('min_score', 'asc')
            ->get();
            
        if ($grading->isEmpty()) {
            throw new \Exception("No grading system found");
        }
        
        return $grading->map(function($g) {
            return [
                'min_score' => $g->min_score,
                'max_score' => $g->max_score,
                'grade' => $g->grade,
                'grade_point' => $g->grade_point
            ];
        })->toArray();
    }
    
    public function resolveWeights(Student $student, Module $module): array
    {
        // Try programme rule first
        $rule = ProgrammeAssessmentRule::where('programme_id', $student->programme_id)
            ->where(function($q) use ($module) {
                $q->where('nta_level', $module->nta_level)
                  ->orWhereNull('nta_level');
            })
            ->where('is_active', true)
            ->first();
            
        if ($rule) {
            return [
                'cw_weight' => (float) $rule->cw_weight,
                'exam_weight' => (float) $rule->exam_weight,
                'source' => 'programme_rule'
            ];
        }
        
        // Fallback to module defaults (must exist in strict mode)
        if ($module->cw_weight !== null && $module->exam_weight !== null) {
            return [
                'cw_weight' => (float) $module->cw_weight,
                'exam_weight' => (float) $module->exam_weight,
                'source' => 'module_default'
            ];
        }
        
        throw new \Exception("No weights configured for module {$module->code}");
    }
    
    public function resolvePassMark(Student $student, Module $module): float
    {
        $passMark = ProgrammePassMark::where('programme_id', $student->programme_id)
            ->where('nta_level', $module->nta_level)
            ->first();
            
        if (!$passMark) {
            throw new \Exception("No pass mark configured");
        }
        
        return (float) $passMark->pass_mark;
    }
}
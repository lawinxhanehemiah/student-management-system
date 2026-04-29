<?php
// app/Domain/Grading/GradingEngine.php

namespace App\Domain\Grading;

use App\Models\Module;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\GradingSystem;
use App\Models\ProgrammeAssessmentRule;
use App\Models\ProgrammePassMark;

class GradingEngine
{
    private $calculator;
    
    public function __construct()
    {
        $this->calculator = new GradingCalculator();
    }
    
    /**
     * Calculate grade - NO FALLBACKS, assumes config is validated
     * Call GradingConfigValidator::validate() before using this!
     */
    public function calculate(Student $student, Module $module, AcademicYear $academicYear, float $rawCW, float $rawExam): array
    {
        // Resolve everything - will throw if missing
        $gradingBands = $this->resolveGradingBands($student, $module, $academicYear);
        $weights = $this->resolveWeights($student, $module);
        $passMark = $this->resolvePassMark($student, $module);
        
        // Calculate
        $weightedScore = $this->calculator->calculateWeightedScore(
            $rawCW, $rawExam, 
            $weights['cw_weight'], $weights['exam_weight']
        );
        
        $gradeData = $this->calculator->getGradeFromScore($weightedScore, $gradingBands);
        $resultStatus = $this->calculator->getResultStatus($weightedScore, $passMark);
        $remark = $this->calculator->getRemark($weightedScore);
        
        return [
            'weighted_score' => $weightedScore,
            'grade' => $gradeData['grade'],
            'grade_point' => $gradeData['grade_point'],
            'result_status' => $resultStatus,
            'remark' => $remark,
            'weights_used' => $weights,
            'pass_mark_used' => $passMark,
            'grading_system_id' => $gradeData['grading_system_id'] ?? null
        ];
    }
    
    private function resolveGradingBands(Student $student, Module $module, AcademicYear $academicYear): array
    {
        $grading = GradingSystem::where('academic_year_id', $academicYear->id)
            ->where('program_category', $student->programme->program_category)
            ->where('nta_level', $module->nta_level)
            ->where('is_active', true)
            ->orderBy('min_score', 'asc')
            ->get();
            
        if ($grading->isEmpty()) {
            throw new \Exception(
                "No grading system found for: " .
                "Program Category: {$student->programme->program_category}, " .
                "NTA Level: {$module->nta_level}, " .
                "Academic Year: {$academicYear->name}"
            );
        }
        
        return $grading->map(function($g) {
            return [
                'min_score' => $g->min_score,
                'max_score' => $g->max_score,
                'grade' => $g->grade,
                'grade_point' => $g->grade_point,
                'grading_system_id' => $g->id
            ];
        })->toArray();
    }
    
    private function resolveWeights(Student $student, Module $module): array
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
        
        // Fallback to module defaults
        if ($module->cw_weight !== null && $module->exam_weight !== null) {
            return [
                'cw_weight' => (float) $module->cw_weight,
                'exam_weight' => (float) $module->exam_weight,
                'source' => 'module_default'
            ];
        }
        
        throw new \Exception("No weights configured for module {$module->code}");
    }
    
    private function resolvePassMark(Student $student, Module $module): float
    {
        $passMark = ProgrammePassMark::where('programme_id', $student->programme_id)
            ->where('nta_level', $module->nta_level)
            ->first();
            
        if (!$passMark) {
            throw new \Exception(
                "No pass mark configured for Programme: {$student->programme->name}, " .
                "NTA Level: {$module->nta_level}"
            );
        }
        
        return (float) $passMark->pass_mark;
    }
}
<?php
// app/Domain/Grading/GradingConfigValidator.php

namespace App\Domain\Grading;

use App\Models\AcademicYear;
use App\Models\Programme;
use App\Models\Module;
use App\Models\GradingSystem;
use Illuminate\Support\Facades\DB;

class GradingConfigValidator
{
    private $academicYear;
    private $errors = [];
    private $warnings = [];
    
    public function __construct(AcademicYear $academicYear)
    {
        $this->academicYear = $academicYear;
    }
    
    public function validate(): array
    {
        $this->errors = [];
        $this->warnings = [];
        
        $this->validateProgrammes();
        $this->validateModules();
        $this->validateGradingSystems();
        $this->validatePassMarks();
        
        return [
            'is_valid' => empty($this->errors),
            'is_ready_for_locking' => empty($this->errors) && empty($this->warnings),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'summary' => [
                'total_errors' => count($this->errors),
                'total_warnings' => count($this->warnings)
            ]
        ];
    }
    
    private function validateProgrammes(): void
    {
        $programmes = Programme::all();
        
        if ($programmes->isEmpty()) {
            $this->errors[] = 'No programmes found in the system';
            return;
        }
        
        foreach ($programmes as $programme) {
            if (empty($programme->program_category)) {
                $this->errors[] = "Programme '{$programme->name}' (ID: {$programme->id}) has NO program_category. Please set to 'health', 'non_health', or 'all'.";
            } elseif (!in_array($programme->program_category, ['health', 'non_health', 'all'])) {
                $this->errors[] = "Programme '{$programme->name}' has INVALID category: '{$programme->program_category}'. Must be 'health', 'non_health', or 'all'.";
            }
        }
    }
    
    private function validateModules(): void
    {
        $modules = Module::where('is_active', true)->get();
        
        if ($modules->isEmpty()) {
            $this->errors[] = 'No active modules found in the system';
            return;
        }
        
        foreach ($modules as $module) {
            // Check NTA level
            if (empty($module->nta_level)) {
                $this->errors[] = "Module '{$module->code}' has NO nta_level set";
            }
            
            // Check weights (if set, they must sum to 100)
            if ($module->cw_weight !== null && $module->exam_weight !== null) {
                $total = (float) $module->cw_weight + (float) $module->exam_weight;
                
                if (abs($total - 100) > 0.01) {
                    $this->errors[] = "Module '{$module->code}' has INVALID weights: CW={$module->cw_weight}, Exam={$module->exam_weight}, Total={$total}% (must be 100)";
                }
            }
            // If weights are NULL, that's okay - programme rules will be used
        }
    }
    
    private function validateGradingSystems(): void
    {
        $programmeCategories = Programme::distinct()->pluck('programme_category')->filter();
        $ntaLevels = Module::where('is_active', true)->distinct()->pluck('nta_level')->filter();
        
        if ($programmeCategories->isEmpty() || $ntaLevels->isEmpty()) {
            $this->warnings[] = 'No programme categories or NTA levels found to validate grading systems';
            return;
        }
        
        foreach ($programmeCategories as $category) {
            foreach ($ntaLevels as $level) {
                $exists = GradingSystem::where('academic_year_id', $this->academicYear->id)
                    ->where('program_category', $category)
                    ->where('nta_level', $level)
                    ->where('is_active', true)
                    ->exists();
                    
                if (!$exists) {
                    $this->errors[] = "MISSING grading system: Category='{$category}', NTA Level={$level}, Academic Year='{$this->academicYear->name}' (ID: {$this->academicYear->id})";
                }
            }
        }
    }
    
    private function validatePassMarks(): void
    {
        $programmes = Programme::all();
        $ntaLevels = Module::where('is_active', true)->distinct()->pluck('nta_level')->filter();
        
        foreach ($programmes as $programme) {
            foreach ($ntaLevels as $level) {
                $hasPassMark = DB::table('programme_pass_marks')
                    ->where('programme_id', $programme->id)
                    ->where('nta_level', $level)
                    ->exists();
                    
                if (!$hasPassMark) {
                    $this->errors[] = "MISSING pass mark for Programme '{$programme->name}', NTA Level {$level}";
                }
            }
        }
    }
}
<?php
// app/Console/Commands/ValidateGradingConfig.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Grading\GradingConfigValidator;
use App\Models\AcademicYear;

class ValidateGradingConfig extends Command
{
    protected $signature = 'grading:validate 
                            {--academic-year= : Academic year ID to validate}
                            {--fix : Attempt to auto-fix issues}
                            {--strict : Fail if any warning exists}';
    
    protected $description = 'Validate entire grading system configuration';
    
    public function handle()
    {
        $academicYearId = $this->option('academic-year') ?? AcademicYear::where('is_current', true)->first()?->id;
        
        if (!$academicYearId) {
            $this->error('No academic year specified and no current year set');
            return 1;
        }
        
        $academicYear = AcademicYear::find($academicYearId);
        $validator = new GradingConfigValidator($academicYear);
        $result = $validator->validate();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Valid', $result['summary']['total_errors'] === 0 ? 'Yes' : 'No'],
                ['❌ Errors', $result['summary']['total_errors']],
                ['⚠️ Warnings', $result['summary']['total_warnings']]
            ]
        );
        
        if (!empty($result['errors'])) {
            $this->newLine();
            $this->error('❌ ERRORS FOUND:');
            foreach ($result['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }
        
        if (!empty($result['warnings'])) {
            $this->newLine();
            $this->warn('⚠️ WARNINGS:');
            foreach ($result['warnings'] as $warning) {
                $this->line("  • {$warning}");
            }
        }
        
        if ($result['is_valid']) {
            $this->newLine();
            $this->info('✅ Grading system is fully configured and ready!');
            return 0;
        }
        
        if ($this->option('strict')) {
            return 1;
        }
        
        return $result['is_valid'] ? 0 : 1;
    }
}
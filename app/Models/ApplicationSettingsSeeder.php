<?php

namespace Database\Seeders;

use App\Models\ApplicationSetting;
use App\Models\Program;
use App\Models\ProgramAvailability;
use Illuminate\Database\Seeder;

class ApplicationSettingsSeeder extends Seeder
{
    public function run()
    {
        // Create application setting for current academic year
        $setting = ApplicationSetting::create([
            'academic_year' => '2024/2025',
            'intake' => 'Both',
            'status' => 'OPEN',
            'opening_date' => now()->subDays(30),
            'closing_date' => now()->addDays(60),
            
            // Eligibility
            'min_education_level' => 'CSEE',
            'min_division' => 'III',
            'min_subjects_pass' => 5,
            'min_grade' => 'D',
            
            // Fee settings
            'fee_mode' => 'FREE',
            'fee_amount' => 0,
            'currency' => 'TZS',
            
            // Documents required
            'required_documents' => json_encode([
                [
                    'name' => 'Birth Certificate',
                    'required' => true,
                    'file_types' => ['pdf', 'jpg', 'png'],
                    'max_size' => 2048,
                ],
                [
                    'name' => 'Academic Certificates',
                    'required' => true,
                    'file_types' => ['pdf', 'jpg', 'png'],
                    'max_size' => 2048,
                ],
                [
                    'name' => 'Passport Photo',
                    'required' => true,
                    'file_types' => ['jpg', 'png'],
                    'max_size' => 512,
                ],
            ]),
            
            // Steps enabled
            'enabled_steps' => json_encode(['personal', 'academic', 'programs', 'documents', 'review']),
            
            // Results entry
            'results_entry_mode' => 'GUIDED',
            'manual_verification' => true,
            
            // Course recommendation
            'recommendation_mode' => 'RECOMMEND_ONLY',
            
            // Messages
            'closed_message' => 'Applications for this intake are currently closed.',
            'eligibility_message' => 'Please ensure you meet the minimum requirements before applying.',
            'payment_message' => 'Application is free of charge for all candidates.',
            
            // Audit & Control
            'lock_submitted' => true,
            'allow_admin_override' => false,
            'log_changes' => true,
            
            // Versioning
            'version' => 1,
            'effective_from' => now(),
            'changed_by' => 1, // Super admin
            'is_active' => true,
        ]);

        // Make all programs available for this application cycle
        $programs = Program::where('is_active', true)->get();
        
        foreach ($programs as $program) {
            ProgramAvailability::create([
                'application_setting_id' => $setting->id,
                'program_id' => $program->id,
                'is_active' => true,
                'intake_allowed' => 'Both',
                'capacity' => rand(50, 200),
                'min_requirements' => json_encode([
                    'min_points' => rand(4, 8),
                    'required_subjects' => ['Mathematics', 'English'],
                ]),
            ]);
        }

        $this->command->info('Application settings seeded successfully!');
        $this->command->info("Created application setting for {$setting->academic_year}");
        $this->command->info("Made {$programs->count()} programs available");
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Insert academic and fiscal year settings
        $settings = [
            // Academic Years
            [
                'setting_key' => 'academic_years',
                'setting_value' => json_encode([
                    ['id' => 1, 'name' => '2024/2025', 'start_date' => '2024-09-01', 'end_date' => '2025-08-31', 'is_active' => false],
                    ['id' => 2, 'name' => '2025/2026', 'start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_active' => true],
                    ['id' => 3, 'name' => '2026/2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => false],
                ]),
                'setting_type' => 'json',
                'setting_group' => 'academic',
                'display_name' => 'Academic Years',
                'description' => 'List of academic years',
                'sort_order' => 1,
                'is_public' => 1,
            ],
            [
                'setting_key' => 'current_academic_year',
                'setting_value' => '2',
                'setting_type' => 'integer',
                'setting_group' => 'academic',
                'display_name' => 'Current Academic Year ID',
                'description' => 'ID of the currently active academic year',
                'sort_order' => 2,
                'is_public' => 1,
            ],
            
            // Fiscal Years
            [
                'setting_key' => 'fiscal_years',
                'setting_value' => json_encode([
                    ['id' => 1, 'name' => 'FY 2024', 'start_date' => '2024-01-01', 'end_date' => '2024-12-31', 'is_active' => false],
                    ['id' => 2, 'name' => 'FY 2025', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'is_active' => false],
                    ['id' => 3, 'name' => 'FY 2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_active' => true],
                ]),
                'setting_type' => 'json',
                'setting_group' => 'fiscal',
                'display_name' => 'Fiscal Years',
                'description' => 'List of fiscal years',
                'sort_order' => 1,
                'is_public' => 1,
            ],
            [
                'setting_key' => 'current_fiscal_year',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'setting_group' => 'fiscal',
                'display_name' => 'Current Fiscal Year ID',
                'description' => 'ID of the currently active fiscal year',
                'sort_order' => 2,
                'is_public' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            // Check if setting already exists
            $exists = DB::table('system_settings')->where('setting_key', $setting['setting_key'])->exists();
            
            if (!$exists) {
                DB::table('system_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down()
    {
        DB::table('system_settings')->whereIn('setting_key', [
            'academic_years',
            'current_academic_year',
            'fiscal_years',
            'current_fiscal_year',
        ])->delete();
    }
};
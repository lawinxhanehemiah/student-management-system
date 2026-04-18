<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eligibility_rules', function (Blueprint $table) {
            // Add category column
            $table->enum('category', ['health', 'non_health', 'general'])->default('general')->after('programme_id');
            
            // Add points operator (less than or equal / greater than or equal)
            $table->enum('points_operator', ['lte', 'gte'])->default('lte')->after('min_csee_points');
            
            // Add core subjects (all required)
            $table->json('core_subjects')->nullable()->after('required_subjects');
            
            // Add alternative subjects (at least one required)
            $table->json('alternative_subjects')->nullable()->after('core_subjects');
            
            // Add minimum count for alternative subjects
            $table->integer('min_alternative_count')->default(1)->after('alternative_subjects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eligibility_rules', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'points_operator',
                'core_subjects',
                'alternative_subjects',
                'min_alternative_count'
            ]);
        });
    }
};
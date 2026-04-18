<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eligibility_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('eligibility_rules', 'core_subjects')) {
                $table->json('core_subjects')->nullable()->after('required_subjects');
            }
            if (!Schema::hasColumn('eligibility_rules', 'alternative_subjects')) {
                $table->json('alternative_subjects')->nullable()->after('core_subjects');
            }
            if (!Schema::hasColumn('eligibility_rules', 'min_alternative_count')) {
                $table->integer('min_alternative_count')->default(1)->after('alternative_subjects');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eligibility_rules', function (Blueprint $table) {
            $table->dropColumn(['core_subjects', 'alternative_subjects', 'min_alternative_count']);
        });
    }
};
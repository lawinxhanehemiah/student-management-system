<?php
// database/migrations/xxxx_add_created_by_to_departments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_active')
                      ->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('departments', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')
                      ->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('departments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });
    }
};
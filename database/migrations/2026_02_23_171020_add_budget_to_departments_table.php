<?php
// database/migrations/xxxx_add_budget_to_departments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'budget')) {
                $table->decimal('budget', 15, 2)->default(0)->after('description');
            }
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('budget');
        });
    }
};
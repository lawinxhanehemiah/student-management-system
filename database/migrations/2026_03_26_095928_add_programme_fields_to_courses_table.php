<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgrammeFieldsToCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Add programme_id column
            $table->foreignId('programme_id')->nullable()->after('id')->constrained('programmes')->onDelete('cascade');
            
            // Add course code
            $table->string('code', 50)->nullable()->after('name');
            
            // Add level and semester
            $table->integer('level')->nullable()->after('programme_id');
            $table->integer('semester')->nullable()->after('level');
            
            // Add credit hours
            $table->integer('credit_hours')->default(3)->after('semester');
            
            // Add description
            $table->text('description')->nullable()->after('credit_hours');
            
            // Add is_active flag (since status already exists, we'll use status)
            // We'll keep using 'status' column which already exists
            
            // Add index for better performance
            $table->index(['programme_id', 'level', 'semester']);
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['programme_id']);
            $table->dropColumn(['programme_id', 'code', 'level', 'semester', 'credit_hours', 'description']);
            $table->dropIndex(['programme_id', 'level', 'semester']);
        });
    }
}
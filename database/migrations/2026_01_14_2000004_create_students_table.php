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
        Schema::create('students', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('application_id')->constrained()->cascadeOnDelete();

    $table->string('registration_number')->unique();
    $table->foreignId('programme_id');
    $table->foreignId('course_id');

    $table->string('study_mode');
    $table->string('intake');
    $table->string('status')->default('active');

    // Guardian
    $table->string('guardian_name')->nullable();
    $table->string('guardian_phone')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

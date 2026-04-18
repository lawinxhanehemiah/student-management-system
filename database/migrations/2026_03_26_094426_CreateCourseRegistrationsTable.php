<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('course_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained();
            $table->integer('semester')->default(1);
            $table->date('registration_date');
            $table->enum('status', ['registered', 'completed', 'dropped', 'failed'])->default('registered');
            $table->foreignId('registered_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->unique(['student_id', 'course_id', 'academic_year_id', 'semester'], 'unique_registration');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('course_registrations');
    }
}
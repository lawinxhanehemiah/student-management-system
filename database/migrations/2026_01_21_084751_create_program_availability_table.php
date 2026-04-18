<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_availability', function (Blueprint $table) {
            $table->id();

            // Foreign key pointing to 'programmes'
            $table->foreignId('program_id')->constrained('programmes')->cascadeOnDelete();

            // Foreign key to academic_years
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            $table->enum('intake', ['March', 'September'])->default('March');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_availability');
    }
};

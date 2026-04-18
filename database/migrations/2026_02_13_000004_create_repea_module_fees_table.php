<?php
// database/migrations/2024_01_01_000002_create_repeat_module_fees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('repeat_module_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->integer('level');
            $table->integer('semester');
            
            // FEE - ONLY TOTAL FEE (Hakuna fee_per_unit, registration_fee, tuition_fee, library_fee, computer_fee)
            $table->decimal('total_fee', 10, 2)->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['programme_id', 'academic_year_id', 'level', 'semester'], 'repeat_fee_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repeat_module_fees');
    }
};
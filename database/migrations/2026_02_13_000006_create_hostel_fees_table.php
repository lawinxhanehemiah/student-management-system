<?php
// database/migrations/2024_01_01_000003_create_hostel_fees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostel_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->integer('level'); // 1-6
            $table->integer('semester'); // 1 or 2
            
            // Hostel fee - ONLY TOTAL FEE kwa mujibu wa architecture yenu
            $table->decimal('total_fee', 10, 2)->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['academic_year_id', 'programme_id', 'level', 'semester'], 'hostel_fee_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostel_fees');
    }
};
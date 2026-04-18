<?php
// database/migrations/xxxx_xx_xx_create_curriculum_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('curriculum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->integer('year'); // 1..6
            $table->integer('semester'); // 1 or 2
            $table->decimal('credits', 5, 2)->nullable(); // override default_credits if needed
            $table->boolean('is_required')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['programme_id', 'module_id', 'year', 'semester'], 'unique_curriculum');
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculum');
    }
};
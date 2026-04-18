<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id(); // bigint unsigned
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('study_mode', ['Full Time', 'Part Time', 'Evening', 'Weekend'])->default('Full Time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};

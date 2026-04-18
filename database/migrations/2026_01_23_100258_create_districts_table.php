<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index('region_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
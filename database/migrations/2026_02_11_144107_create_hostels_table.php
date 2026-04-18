<?php
// database/migrations/xxxx_create_hostels_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['boys', 'girls', 'mixed'])->default('mixed');
            $table->integer('capacity')->default(0);
            $table->enum('gender', ['male', 'female', 'both'])->default('both');
            $table->decimal('fee_per_semester', 10, 2)->default(0);
            $table->decimal('fee_per_year', 10, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('warden_name')->nullable();
            $table->string('warden_phone')->nullable();
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostels');
    }
};
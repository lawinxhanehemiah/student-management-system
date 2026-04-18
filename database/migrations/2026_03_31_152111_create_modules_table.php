<?php
// database/migrations/xxxx_xx_xx_create_modules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('nta_level')->unsigned()->comment('4,5,6');
            $table->decimal('default_credits', 5, 2)->nullable();
            $table->enum('type', ['Core', 'Fundamental', 'Elective'])->default('Core');
            $table->decimal('pass_mark', 5, 2)->default(50.00);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('modules');
    }
};
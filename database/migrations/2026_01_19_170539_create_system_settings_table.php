<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_type')->default('text'); // text, textarea, boolean, number, email, select
            $table->string('setting_group')->default('general'); // general, academic, finance, etc
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // for select fields
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(false); // if can be accessed publicly
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('level_order');
            $table->enum('approver_type', ['role', 'user', 'department_head']);
            $table->string('approver_value'); // role name, user_id, or 'department_head'
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('level_order');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_levels');
    }
};
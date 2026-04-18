<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionLogsTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained();
            $table->integer('from_level');
            $table->integer('to_level');
            $table->integer('from_semester')->nullable();
            $table->integer('to_semester')->nullable();
            $table->string('promotion_type'); // 'semester', 'level', 'bulk'
            $table->decimal('gpa', 5, 2)->nullable();
            $table->boolean('fee_cleared')->default(false);
            $table->text('conditions_met')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('promoted_by')->constrained('users');
            $table->timestamp('promoted_at');
            $table->timestamps();
            
            $table->index(['student_id', 'academic_year_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_logs');
    }
}
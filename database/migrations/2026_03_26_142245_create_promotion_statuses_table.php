<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained();
            $table->enum('status', [
                'pending', 'eligible', 'approved', 'rejected', 'promoted'
            ])->default('pending');
            $table->enum('academic_standing', [
                'good', 'warning', 'probation', 'suspended'
            ])->default('good');
            $table->decimal('gpa', 5, 2)->nullable();
            $table->decimal('cgpa', 5, 2)->nullable();
            $table->json('conditions_met')->nullable();
            $table->json('conditions_failed')->nullable();
            $table->string('recommendation')->nullable(); // promote, repeat_semester, repeat_year, probation
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'academic_year_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_statuses');
    }
}
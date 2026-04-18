<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_year_id');
            $table->unsignedBigInteger('department_id');
            $table->enum('level', ['hod', 'finance', 'director'])->default('hod');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('budget_year_id');
            $table->index('department_id');
            $table->index('approved_by');
            $table->unique(['budget_year_id', 'department_id', 'level'], 'approval_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_approvals');
    }
};
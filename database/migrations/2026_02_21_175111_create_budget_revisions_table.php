<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('revision_number')->unique();
            $table->unsignedBigInteger('budget_year_id');
            $table->unsignedBigInteger('department_id');
            $table->enum('type', ['increase', 'decrease', 'transfer'])->default('increase');
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('budget_year_id');
            $table->index('department_id');
            $table->index('requested_by');
            $table->index('approved_by');
            $table->index('revision_number');
            $table->index(['budget_year_id', 'department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_revisions');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('department_id')->constrained();
            $table->date('request_date');
            $table->date('required_date');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->text('justification')->nullable();
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('requisition_number');
            $table->index('status');
            $table->index('priority');
            $table->index('request_date');
            $table->index('department_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisitions');
    }
};
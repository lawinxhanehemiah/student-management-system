<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requisition_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->onDelete('cascade');
            $table->foreignId('approval_level_id')->constrained();
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('action_date')->nullable();
            $table->timestamps();
            
            $table->unique(['requisition_id', 'approval_level_id'], 'requisition_level_unique');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisition_approvals');
    }
};
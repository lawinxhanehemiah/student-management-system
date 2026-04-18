<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->decimal('value', 15, 2)->nullable();
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_deliverables');
    }
};
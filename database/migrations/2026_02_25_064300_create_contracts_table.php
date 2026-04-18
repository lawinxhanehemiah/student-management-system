<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->foreignId('tender_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('requisition_id')->nullable()->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('contract_value', 15, 2);
            $table->enum('status', ['draft', 'active', 'completed', 'terminated', 'expired'])->default('draft');
            $table->json('terms')->nullable();
            $table->json('documents')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->foreignId('project_manager')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('contract_number');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
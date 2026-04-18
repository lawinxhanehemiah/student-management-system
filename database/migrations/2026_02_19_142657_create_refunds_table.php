// database/migrations/xxxx_xx_xx_create_refunds_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('payment_id')->nullable()->constrained();
            $table->foreignId('invoice_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->string('refund_method'); // bank_transfer, mpesa, cash, cheque
            $table->string('refund_reason');
            $table->text('description')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('status')->default('pending'); // pending, approved, processed, rejected
            $table->json('metadata')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['student_id', 'status']);
            $table->index('refund_number');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('refunds');
    }
};
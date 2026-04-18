<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('supplier_invoice_id')->nullable()->constrained();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'mpesa'])->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['supplier_id', 'status']);
            $table->index('voucher_number');
            $table->index('payment_date');
            $table->index('supplier_invoice_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
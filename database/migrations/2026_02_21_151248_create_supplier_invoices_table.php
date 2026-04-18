<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('purchase_order_id')->nullable()->constrained();
            $table->foreignId('goods_received_note_id')->nullable()->constrained();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('supplier_invoice_number');
            $table->enum('status', [
                'pending', 'verified', 'approved', 
                'partial_paid', 'paid', 'overdue', 'cancelled'
            ])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['supplier_id', 'status']);
            $table->index('invoice_number');
            $table->index('due_date');
            $table->index('supplier_invoice_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
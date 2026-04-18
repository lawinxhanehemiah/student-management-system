<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('reference')->nullable();
            $table->enum('status', [
                'draft', 'pending_approval', 'approved', 
                'ordered', 'partially_received', 'completed', 'cancelled'
            ])->default('draft');
            $table->enum('payment_terms', ['cash', 'credit_7', 'credit_15', 'credit_30', 'credit_60'])->default('cash');
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->json('items_summary')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['supplier_id', 'status']);
            $table->index('po_number');
            $table->index('order_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
};
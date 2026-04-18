<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('purchase_order_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->date('receipt_date');
            $table->string('delivery_note_number')->nullable();
            $table->string('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('draft');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['purchase_order_id', 'status']);
            $table->index('grn_number');
            $table->index('receipt_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('goods_received_notes');
    }
};
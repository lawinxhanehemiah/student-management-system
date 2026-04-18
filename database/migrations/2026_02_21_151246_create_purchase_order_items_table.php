<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('item_code')->nullable();
            $table->string('description');
            $table->string('unit')->default('pcs');
            $table->decimal('quantity', 12, 2);
            $table->decimal('received_quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('purchase_order_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
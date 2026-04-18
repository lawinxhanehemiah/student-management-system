<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_received_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained();
            $table->decimal('quantity_received', 12, 2);
            $table->decimal('quantity_accepted', 12, 2);
            $table->decimal('quantity_rejected', 12, 2)->default(0);
            $table->string('rejection_reason')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('goods_received_note_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grn_items');
    }
};
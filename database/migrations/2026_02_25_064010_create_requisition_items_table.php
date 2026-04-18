<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->default('pcs');
            $table->decimal('estimated_unit_price', 15, 2);
            $table->decimal('estimated_total', 15, 2);
            $table->string('catalog_number')->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();
            
            $table->index('requisition_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisition_items');
    }
};
<?php
// database/migrations/[timestamp]_create_invoice_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 10, 2);
            $table->string('type')->nullable(); // registration, tuition, other
            $table->string('category')->nullable(); // fee, fine, other
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->string('tax_type')->nullable();
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('programme_fee_id')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_waived')->default(false);
            $table->string('waiver_reason')->nullable();
            $table->unsignedBigInteger('waived_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('cascade');
                  
            $table->foreign('programme_fee_id')
                  ->references('id')
                  ->on('programme_fees')
                  ->onDelete('set null');
                  
            $table->foreign('waived_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('invoice_id');
            $table->index('type');
            $table->index('category');
            $table->index('is_optional');
            $table->index('is_waived');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
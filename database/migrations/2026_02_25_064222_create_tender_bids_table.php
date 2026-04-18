<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tender_bids', function (Blueprint $table) {
            $table->id();
            $table->string('bid_number')->unique();
            $table->foreignId('tender_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained();
            $table->decimal('bid_amount', 15, 2);
            $table->text('technical_proposal')->nullable();
            $table->json('documents')->nullable();
            $table->enum('status', ['submitted', 'shortlisted', 'accepted', 'rejected'])->default('submitted');
            $table->decimal('technical_score', 5, 2)->nullable();
            $table->decimal('financial_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->text('evaluation_comments')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            
            $table->index('bid_number');
            $table->index('status');
            $table->unique(['tender_id', 'supplier_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tender_bids');
    }
};
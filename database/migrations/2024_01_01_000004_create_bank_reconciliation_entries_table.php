<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_reconciliation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('matched')->default(false);
            $table->enum('adjustment_type', [
                'missing_in_bank', 
                'missing_in_system', 
                'wrong_amount',
                'duplicate',
                'other'
            ])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('matched');
            $table->index('adjustment_type');
            $table->unique(['bank_reconciliation_id', 'bank_transaction_id'], 'rec_txn_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_entries');
    }
};
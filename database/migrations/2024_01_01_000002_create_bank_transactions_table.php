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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');
            $table->enum('transaction_type', [
                'deposit', 
                'withdrawal', 
                'transfer', 
                'opening_balance',
                'fee',
                'interest',
                'reversal'
            ])->default('deposit');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            
            // Polymorphic relationship to source document
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->text('description');
            $table->enum('status', ['pending', 'completed', 'failed', 'reconciled'])->default('pending');
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('transaction_number');
            $table->index('transaction_date');
            $table->index('transaction_type');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
            $table->index('reconciled_at');
            
            // Composite index for date range queries
            $table->index(['bank_account_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
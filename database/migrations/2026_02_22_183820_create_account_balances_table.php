<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->date('balance_date');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['account_id', 'balance_date']);
            $table->index('balance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
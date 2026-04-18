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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('payment_gateway_id')
                  ->constrained('bank_accounts')->onDelete('set null');
            $table->string('cheque_number', 50)->nullable()->after('bank_account_id');
            $table->timestamp('deposited_at')->nullable()->after('cheque_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['bank_account_id', 'cheque_number', 'deposited_at']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->string('reference_type'); // payment, invoice, journal, bank
            $table->unsignedBigInteger('reference_id');
            $table->string('transaction_type'); // payment_received, invoice_created, journal_posted, etc.
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('TZS');
            $table->string('status')->nullable();
            $table->json('before_status')->nullable();
            $table->json('after_status')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('user_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
            
            $table->index(['reference_type', 'reference_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('transaction_date');
            $table->index('transaction_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_logs');
    }
};
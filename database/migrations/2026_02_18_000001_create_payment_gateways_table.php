<?php
// database/migrations/2024_01_01_000001_create_payment_gateways_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaysTable extends Migration
{
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // nmb, tpesa, aitel, etc
            $table->string('type'); // bank, mobile_money, card
            $table->json('credentials')->nullable(); // Encrypted credentials
            $table->json('config')->nullable(); // endpoints, timeouts, etc
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->morphs('payable'); // invoice, fee_structure, etc
            $table->foreignId('student_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('payment_gateway_id')->constrained();
            
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            
            $table->string('payment_method'); // cash, bank_transfer, mobile_money, card
            $table->string('transaction_type'); // full_payment, partial_payment, deposit
            
            // Payment references
            $table->string('control_number')->nullable();
            $table->string('reference_number')->nullable(); // Payment reference
            $table->string('transaction_id')->nullable(); // Bank transaction ID
            $table->string('receipt_number')->nullable();
            
            // Payment status
            $table->string('status'); // pending, completed, failed, refunded, partially_completed
            $table->json('status_history')->nullable();
            
            // Gateway specific data
            $table->json('gateway_request')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('gateway_metadata')->nullable();
            
            // Tracking
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('control_number');
            $table->index('transaction_id');
            $table->index('status');
            $table->index('paid_at');
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained();
            $table->string('attempt_number');
            
            $table->string('status'); // initiated, success, failed
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();
            
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
        });

        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->unique();
            $table->string('payment_type'); // bank, mobile
            $table->string('control_number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('msisdn')->nullable(); // Phone number
            $table->decimal('amount', 10, 2);
            $table->string('reference')->nullable();
            
            $table->json('raw_data');
            $table->string('status'); // received, processed, failed
            $table->text('processing_error')->nullable();
            
            $table->foreignId('payment_id')->nullable()->constrained();
            $table->foreignId('invoice_id')->nullable()->constrained();
            
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_notifications');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_gateways');
    }
}
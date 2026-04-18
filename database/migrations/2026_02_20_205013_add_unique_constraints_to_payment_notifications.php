<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_notifications', function (Blueprint $table) {
            $table->unique('transaction_id', 'payment_notifications_transaction_id_unique');
            $table->unique(['control_number', 'amount', 'transaction_id'], 'payment_notifications_unique_combo');
        });
    }

    public function down()
    {
        Schema::table('payment_notifications', function (Blueprint $table) {
            $table->dropUnique('payment_notifications_transaction_id_unique');
            $table->dropUnique('payment_notifications_unique_combo');
        });
    }
};
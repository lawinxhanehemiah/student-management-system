<?php
// database/migrations/xxxx_add_payment_status_to_supplier_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_invoices', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])
                    ->default('unpaid')
                    ->after('status');
            }
            
            if (!Schema::hasColumn('supplier_invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            }
            
            if (!Schema::hasColumn('supplier_invoices', 'balance')) {
                // 🔴 FIX: Remove the extra quote after 2
                $table->decimal('balance', 15, 2)->default(0)->after('paid_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount', 'balance']);
        });
    }
};
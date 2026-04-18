// database/migrations/add_aging_fields_to_invoices.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('aging_category')->nullable()->index();
            $table->integer('days_overdue')->default(0)->index();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->integer('reminder_count')->default(0);
            $table->string('collection_status')->default('current'); // current, follow_up, critical, written_off
            $table->decimal('write_off_amount', 12, 2)->default(0);
            $table->timestamp('written_off_at')->nullable();
            $table->text('write_off_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'aging_category',
                'days_overdue',
                'last_reminder_sent_at',
                'reminder_count',
                'collection_status',
                'write_off_amount',
                'written_off_at',
                'write_off_reason'
            ]);
        });
    }
};
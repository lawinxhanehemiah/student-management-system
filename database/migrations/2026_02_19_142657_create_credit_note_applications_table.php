// database/migrations/xxxx_xx_xx_create_credit_note_applications_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_note_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('target_invoice_id')->nullable()->constrained('invoices');
            $table->string('target_type'); // invoice, future_fees
            $table->decimal('amount_applied', 12, 2);
            $table->string('status')->default('applied');
            $table->json('metadata')->nullable();
            $table->foreignId('applied_by')->constrained('users');
            $table->timestamp('applied_at');
            $table->timestamps();
            
            $table->index(['credit_note_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_note_applications');
    }
};
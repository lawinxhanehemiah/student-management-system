<?php
// database/migrations/2026_02_13_000010_create_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('academic_year_id');
                $table->unsignedBigInteger('programme_fee_id')->nullable();
                $table->string('invoice_type')->default('tuition');
                $table->decimal('total_amount', 10, 2);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('balance', 10, 2);
                $table->date('issue_date');
                $table->date('due_date');
                $table->string('status')->default('pending');
                $table->string('payment_status')->default('unpaid');
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('academic_year_id')->references('id')->on('academic_years');
                $table->foreign('programme_fee_id')->references('id')->on('programme_fees');
                $table->foreign('created_by')->references('id')->on('users');
                
                // Indexes
                $table->index('invoice_number');
                $table->index('student_id');
                $table->index('academic_year_id');
                $table->index('status');
                $table->index('payment_status');
                $table->index('due_date');
            });
            
            echo "Table invoices created successfully.\n";
        } else {
            echo "Table invoices already exists.\n";
        }
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
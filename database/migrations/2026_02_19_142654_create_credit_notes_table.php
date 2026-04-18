// database/migrations/xxxx_xx_xx_create_credit_notes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, partially_used, fully_used, void
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['student_id', 'status']);
            $table->index('credit_note_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_notes');
    }
};
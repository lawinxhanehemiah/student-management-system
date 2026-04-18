<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->date('entry_date');
            $table->string('reference_type')->nullable(); // invoice, payment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description');
            $table->enum('type', ['manual', 'system', 'recurring'])->default('manual');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_balanced')->default(false);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('entry_date');
            $table->index('journal_number');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('tender_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('requisition_id')->nullable()->constrained();
            $table->enum('type', ['open', 'closed', 'restricted', 'direct'])->default('open');
            $table->enum('status', ['draft', 'published', 'evaluating', 'awarded', 'cancelled'])->default('draft');
            $table->date('published_date')->nullable();
            $table->date('closing_date');
            $table->date('evaluation_date')->nullable();
            $table->decimal('estimated_value', 15, 2);
            $table->json('documents')->nullable();
            $table->json('eligibility_criteria')->nullable();
            $table->json('evaluation_criteria')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tender_number');
            $table->index('status');
            $table->index('closing_date');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenders');
    }
};
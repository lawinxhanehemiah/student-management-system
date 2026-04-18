<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year')->nullable();
            $table->enum('intake', ['March', 'September', 'Both', 'Rolling'])->default('Both');
            $table->enum('status', ['OPEN', 'CLOSED', 'SUSPENDED'])->default('CLOSED');
            $table->date('opening_date')->nullable();
            $table->date('closing_date')->nullable();
            
            // Eligibility
            $table->string('min_education_level')->nullable();
            $table->string('min_division')->nullable();
            $table->integer('min_subjects_pass')->nullable();
            $table->string('min_grade')->nullable();
            
            // Fee settings
            $table->enum('fee_mode', ['FREE', 'PAID', 'CONDITIONAL'])->default('FREE');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->string('currency')->default('TZS');
            
            // Documents
            $table->json('required_documents')->nullable();
            
            // Steps control
            $table->json('enabled_steps')->nullable();
            
            // Results entry
            $table->enum('results_entry_mode', ['MANUAL', 'GUIDED', 'IMPORTED'])->default('GUIDED');
            $table->boolean('manual_verification')->default(true);
            
            // Course recommendation
            $table->enum('recommendation_mode', ['OFF', 'RECOMMEND_ONLY', 'BLOCK_UNQUALIFIED'])->default('RECOMMEND_ONLY');
            
            // Messages
            $table->text('closed_message')->nullable();
            $table->text('eligibility_message')->nullable();
            $table->text('payment_message')->nullable();
            
            // Audit & Control
            $table->boolean('lock_submitted')->default(true);
            $table->boolean('allow_admin_override')->default(false);
            $table->boolean('log_changes')->default(true);
            
            // Versioning
            $table->integer('version')->default(1);
            $table->date('effective_from');
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_settings');
    }
};

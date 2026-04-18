<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Admission Calendar Events
        Schema::create('admission_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->enum('event_type', ['application_deadline', 'admission_letter', 'registration', 'orientation', 'exam', 'holiday', 'other']);
            $table->string('color')->default('#007bff');
            $table->boolean('is_public')->default(true);
            $table->integer('reminder_days')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('event_date');
        });
        
        // Admission Settings
        Schema::create('admission_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category');
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('updated_by')->references('id')->on('users');
            $table->index(['category', 'key']);
        });
        
        // Workflow Stages
        Schema::create('admission_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->string('stage_name');
            $table->string('stage_code')->unique();
            $table->integer('stage_order');
            $table->string('responsible_role');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('days_to_complete')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Selection Criteria
        Schema::create('admission_selection_criteria', function (Blueprint $table) {
            $table->id();
            $table->integer('csee_weight')->default(60);
            $table->integer('acsee_weight')->default(30);
            $table->integer('division_bonus')->default(10);
            $table->boolean('auto_select_enabled')->default(true);
            $table->integer('min_ranking_score')->default(50);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('updated_by')->references('id')->on('users');
        });
        
        // Notification Templates
        Schema::create('admission_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
        });
        
        // Help Articles
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('category');
            $table->string('tags')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('category');
        });
        
        // FAQs
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('category');
            $table->integer('order_position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('category');
        });
        
        // Support Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('subject');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('category', ['technical', 'bug', 'feature_request', 'account', 'payment', 'other']);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('resolution')->nullable;
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->index('ticket_number');
            $table->index('status');
        });
        
        // Backups
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->bigInteger('size')->default(0);
            $table->string('type');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('admission_notification_templates');
        Schema::dropIfExists('admission_selection_criteria');
        Schema::dropIfExists('admission_workflow_stages');
        Schema::dropIfExists('admission_settings');
        Schema::dropIfExists('admission_calendar_events');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Workflow Rules table
        Schema::create('admission_workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_event');
            $table->string('action');
            $table->text('conditions')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Workflow Stages table (if not exists)
        if (!Schema::hasTable('admission_workflow_stages')) {
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
        }
        
        // Notification Templates table (if not exists)
        if (!Schema::hasTable('admission_notification_templates')) {
            Schema::create('admission_notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
                $table->string('subject');
                $table->text('body');
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
            });
        }
        
        // Selection Criteria table (if not exists)
        if (!Schema::hasTable('admission_selection_criteria')) {
            Schema::create('admission_selection_criteria', function (Blueprint $table) {
                $table->id();
                $table->integer('csee_weight')->default(60);
                $table->integer('acsee_weight')->default(30);
                $table->integer('division_bonus')->default(10);
                $table->boolean('auto_select_enabled')->default(true);
                $table->integer('min_ranking_score')->default(50);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('admission_workflow_rules');
        Schema::dropIfExists('admission_workflow_stages');
        Schema::dropIfExists('admission_notification_templates');
        Schema::dropIfExists('admission_selection_criteria');
    }
};
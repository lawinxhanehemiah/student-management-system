<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->string('provider')->nullable();
            $table->string('message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('application_id')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('phone_number');
            $table->index('status');
            $table->index('created_at');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_logs');
    }
};
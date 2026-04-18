<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('action'); // approved, rejected, waitlisted, letter_sent, etc.
            $table->text('description')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('application_id')
                  ->references('id')
                  ->on('applications')
                  ->onDelete('cascade');
                  
            $table->foreign('performed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_logs');
    }
};
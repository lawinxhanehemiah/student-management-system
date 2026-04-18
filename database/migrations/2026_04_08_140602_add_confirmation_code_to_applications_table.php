<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('confirmation_code', 20)->nullable()->after('application_number');
            $table->string('sms_sent_at', 50)->nullable()->after('admission_letter_sent_at');
        });
    }

    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['confirmation_code', 'sms_sent_at']);
        });
    }
};
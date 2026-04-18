<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('applications', function (Blueprint $table) {
        $table->boolean('application_fee_paid')->default(false)->after('status');
    });
}

public function down()
{
    Schema::table('applications', function (Blueprint $table) {
        $table->dropColumn('application_fee_paid');
    });
}


    
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromotionStatusToStudentsTable extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('promotion_status', [
                'pending', 
                'eligible', 
                'approved', 
                'rejected', 
                'promoted',
                'probation',
                'repeat_semester',
                'repeat_year'
            ])->default('pending')->after('status');
            $table->enum('academic_standing', [
                'good', 
                'warning', 
                'probation', 
                'suspended'
            ])->default('good')->after('promotion_status');
            $table->timestamp('last_promotion_check')->nullable()->after('academic_standing');
            $table->timestamp('promoted_at')->nullable()->after('last_promotion_check');
            $table->integer('promotion_attempts')->default(0)->after('promoted_at');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'promotion_status',
                'academic_standing',
                'last_promotion_check',
                'promoted_at',
                'promotion_attempts'
            ]);
        });
    }
}
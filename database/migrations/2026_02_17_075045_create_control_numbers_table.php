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
    Schema::create('control_numbers', function (Blueprint $table) {
        $table->id();
        $table->decimal('paid_amount', 10, 2)->nullable();
        $table->string('status')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
        $table->softDeletes(); // 对应查询中的 deleted_at
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_numbers');
    }
};

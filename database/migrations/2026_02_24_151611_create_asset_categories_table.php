<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'none'])
                  ->default('straight_line');
            $table->integer('default_useful_life_years')->nullable();
            $table->decimal('default_salvage_value_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_categories');
    }
};
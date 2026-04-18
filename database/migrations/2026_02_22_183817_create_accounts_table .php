<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->enum('account_type', [
                'asset', 'liability', 'equity', 
                'revenue', 'expense'
            ])->default('asset');
            $table->enum('category', [
                'current_asset', 'fixed_asset', 'current_liability', 
                'long_term_liability', 'owners_equity', 'operating_revenue',
                'other_revenue', 'operating_expense', 'administrative_expense',
                'selling_expense', 'other_expense'
            ])->nullable();
            $table->string('parent_code')->nullable();
            $table->integer('level')->default(1);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('currency')->default('TZS');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['account_type', 'is_active']);
            $table->index('parent_code');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
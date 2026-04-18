<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('is_balanced');
            }
            if (!Schema::hasColumn('journal_entries', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('journal_entries', 'locked_by')) {
                $table->foreignId('locked_by')
                      ->nullable()
                      ->after('locked_at')
                      ->constrained('users')
                      ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });
    }
};
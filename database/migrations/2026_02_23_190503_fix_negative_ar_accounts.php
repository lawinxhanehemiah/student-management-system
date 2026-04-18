<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // This is a DATA FIX, not a structural change
        // We need to investigate and fix the root cause
        
        DB::transaction(function () {
            // Option 1: Create offsetting invoices
            // Option 2: Adjust through journal entries
            // Option 3: Reclassify to unearned revenue
            
            $this->createAdjustingEntries();
        });
    }

    private function createAdjustingEntries()
    {
        // Get negative AR accounts
        $negativeAR = DB::table('chart_of_accounts')
            ->where('account_code', 'LIKE', '1-02-%')
            ->where('current_balance', '<', 0)
            ->get();

        foreach ($negativeAR as $account) {
            // Create adjusting journal entry
            // Debit AR, Credit Unearned Revenue
            // This requires proper accounting review
        }
    }

    public function down()
    {
        // Cannot revert data fixes
    }
};
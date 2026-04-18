<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Recalculate all account balances based on journal entries
        DB::transaction(function () {
            $accounts = DB::table('chart_of_accounts')->get();
            
            foreach ($accounts as $account) {
                // Get totals from journal entry lines
                $totals = DB::table('journal_entry_lines')
                    ->where('account_id', $account->id)
                    ->select(
                        DB::raw('SUM(debit) as total_debit'),
                        DB::raw('SUM(credit) as total_credit')
                    )
                    ->first();
                
                $debit = $totals->total_debit ?? 0;
                $credit = $totals->total_credit ?? 0;
                
                // Calculate correct balance based on account type
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $correctBalance = $account->opening_balance + $debit - $credit;
                } else {
                    $correctBalance = $account->opening_balance + $credit - $debit;
                }
                
                // Update if different
                if (abs($correctBalance - $account->current_balance) > 0.01) {
                    DB::table('chart_of_accounts')
                        ->where('id', $account->id)
                        ->update([
                            'current_balance' => $correctBalance,
                            'updated_at' => now()
                        ]);
                }
            }
        });
    }

    public function down()
    {
        // Cannot revert - this is a data fix
    }
};
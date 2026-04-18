<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class ReconcileAccountBalances extends Command
{
    protected $signature = 'accounts:reconcile 
                            {--fix : Actually update the balances}
                            {--account= : Reconcile specific account code}';
    
    protected $description = 'Reconcile account balances with journal entries';

    public function handle()
    {
        $this->info('====================================');
        $this->info('ACCOUNT BALANCE RECONCILIATION');
        $this->info('====================================');

        // Get accounts to reconcile
        $query = ChartOfAccount::query();
        
        if ($this->option('account')) {
            $query->where('account_code', $this->option('account'));
        }
        
        $accounts = $query->get();
        
        $this->info("Found {$accounts->count()} accounts to check");
        $this->newLine();

        $stats = [
            'correct' => 0,
            'incorrect' => 0,
            'fixed' => 0,
            'errors' => []
        ];

        foreach ($accounts as $account) {
            $this->info("Checking: {$account->account_code} - {$account->account_name}");
            
            // Calculate correct balance from journal entries
            $debit = JournalEntryLine::where('account_id', $account->id)->sum('debit');
            $credit = JournalEntryLine::where('account_id', $account->id)->sum('credit');
            
            if (in_array($account->account_type, ['asset', 'expense'])) {
                $correctBalance = $account->opening_balance + $debit - $credit;
            } else {
                $correctBalance = $account->opening_balance + $credit - $debit;
            }

            $currentBalance = $account->current_balance;
            $diff = abs($correctBalance - $currentBalance);

            if ($diff < 0.01) {
                $this->line("  ✅ CORRECT: " . number_format($currentBalance, 2));
                $stats['correct']++;
            } else {
                $this->line("  ❌ INCORRECT:");
                $this->line("     Current:  " . number_format($currentBalance, 2));
                $this->line("     Correct:  " . number_format($correctBalance, 2));
                $this->line("     Diff:     " . number_format($diff, 2));
                
                $stats['incorrect']++;

                // Fix if requested
                if ($this->option('fix')) {
                    try {
                        $account->current_balance = $correctBalance;
                        $account->saveQuietly();
                        $this->line("     ✅ FIXED");
                        $stats['fixed']++;
                    } catch (\Exception $e) {
                        $this->error("     ❌ ERROR: " . $e->getMessage());
                        $stats['errors'][] = "{$account->account_code}: {$e->getMessage()}";
                    }
                }
            }
            
            $this->newLine();
        }

        // Summary
        $this->info('====================================');
        $this->info('SUMMARY');
        $this->info('====================================');
        $this->line("Total Accounts:    {$accounts->count()}");
        $this->line("Correct:           {$stats['correct']}");
        $this->line("Incorrect:         {$stats['incorrect']}");
        
        if ($this->option('fix')) {
            $this->line("Fixed:             {$stats['fixed']}");
        }
        
        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->error('Errors:');
            foreach ($stats['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        $this->newLine();
        
        if (!$this->option('fix') && $stats['incorrect'] > 0) {
            $this->warn('Run with --fix to correct the balances');
        }

        return 0;
    }
}
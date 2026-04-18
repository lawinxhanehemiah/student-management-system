<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;

class ValidateAccountingEquation extends Command
{
    protected $signature = 'accounting:validate 
                            {--fix : Try to identify issues}
                            {--as-of= : Date to validate}';
    
    protected $description = 'Validate accounting equation: Assets = Liabilities + Equity';

    public function handle()
    {
        $this->info('====================================');
        $this->info('ACCOUNTING EQUATION VALIDATION');
        $this->info('====================================');

        $asOf = $this->option('as-of') ?? now()->format('Y-m-d');
        $this->info("As of: {$asOf}");

        // Get all accounts with balances
        $accounts = ChartOfAccount::all();

        $assets = 0;
        $liabilities = 0;
        $equity = 0;

        foreach ($accounts as $account) {
            $balance = $account->current_balance;
            
            switch ($account->account_type) {
                case 'asset':
                    $assets += $balance;
                    break;
                case 'liability':
                    $liabilities += $balance;
                    break;
                case 'equity':
                    $equity += $balance;
                    break;
                // Revenue/Expense are temporary accounts - closed to equity
                case 'revenue':
                    $equity += $balance;
                    break;
                case 'expense':
                    $equity -= $balance;
                    break;
            }
        }

        $totalLiabilitiesEquity = $liabilities + $equity;
        $difference = $assets - $totalLiabilitiesEquity;

        $this->newLine();
        $this->line("Total Assets:        " . number_format($assets, 2));
        $this->line("Total Liabilities:   " . number_format($liabilities, 2));
        $this->line("Total Equity:        " . number_format($equity, 2));
        $this->line("Liabilities + Equity: " . number_format($totalLiabilitiesEquity, 2));
        $this->newLine();
        
        if (abs($difference) < 0.01) {
            $this->info("✅ ACCOUNTING EQUATION IS BALANCED!");
            $this->info("Assets = Liabilities + Equity");
            return 0;
        } else {
            $this->error("❌ ACCOUNTING EQUATION IS NOT BALANCED!");
            $this->error("Difference: " . number_format($difference, 2));
            
            if ($this->option('fix')) {
                $this->warn("This requires investigation of AR/AP and revenue recognition logic.");
            }
            
            return 1;
        }
    }
}
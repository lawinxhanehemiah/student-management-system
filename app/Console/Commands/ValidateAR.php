<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\ChartOfAccount;

class ValidateAR extends Command
{
    protected $signature = 'accounting:validate-ar';
    
    protected $description = 'Validate Accounts Receivable against Invoices';

    public function handle()
    {
        $this->info('====================================');
        $this->info('ACCOUNTS RECEIVABLE VALIDATION');
        $this->info('====================================');

        // Get AR account
        $arAccount = ChartOfAccount::where('account_code', 'LIKE', '1-02-%')->get();
        
        foreach ($arAccount as $account) {
            $this->validateARAccount($account);
        }

        return 0;
    }

    private function validateARAccount($account)
    {
        $this->newLine();
        $this->line("Account: {$account->account_code} - {$account->account_name}");
        $this->line("System Balance: " . number_format($account->current_balance, 2));

        // Get all unpaid invoices
        $invoiceBalance = 0;
        $invoiceCount = 0;
        
        // Adjust based on your invoice type mapping
        $type = str_contains($account->account_name, 'Tuition') ? 'tuition' : 'hostel';
        
        $invoices = Invoice::where('invoice_type', $type)
            ->where('balance', '>', 0)
            ->get();
            
        foreach ($invoices as $invoice) {
            $invoiceBalance += $invoice->balance;
            $invoiceCount++;
        }

        $this->line("Unpaid Invoices:     " . number_format($invoiceBalance, 2));
        $this->line("Invoice Count:       " . $invoiceCount);
        
        $diff = $account->current_balance - $invoiceBalance;
        
        if (abs($diff) < 0.01) {
            $this->info("✅ AR matches invoices!");
        } elseif ($diff > 0) {
            $this->warn("⚠️ AR is HIGHER than invoices by " . number_format($diff, 2));
            $this->warn("   Possible: Over-posting or missing invoices");
        } else {
            $this->error("❌ AR is LOWER than invoices by " . number_format(abs($diff), 2));
            $this->error("   This explains negative AR!");
            $this->error("   Cause: Payments posted before invoices");
        }
    }
}
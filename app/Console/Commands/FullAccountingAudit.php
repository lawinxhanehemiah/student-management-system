<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FullAccountingAudit extends Command
{
    protected $signature = 'accounting:audit';
    
    protected $description = 'Run full accounting audit suite';

    public function handle()
    {
        $this->info('====================================');
        $this->info('FULL ACCOUNTING AUDIT');
        $this->info('====================================');

        $tests = [
            'Trial Balance' => 'accounting:trial-balance',
            'Accounting Equation' => 'accounting:validate',
            'AR Validation' => 'accounting:validate-ar',
            'AP Validation' => 'accounting:validate-ap',
            'Revenue Recognition' => 'accounting:validate-revenue',
        ];

        $results = [];
        $failed = 0;

        foreach ($tests as $name => $command) {
            $this->newLine();
            $this->line("Running: {$name}");
            
            $exitCode = $this->call($command);
            
            $results[$name] = $exitCode === 0 ? 'PASS' : 'FAIL';
            if ($exitCode !== 0) $failed++;
        }

        $this->newLine();
        $this->info('====================================');
        $this->info('AUDIT SUMMARY');
        $this->info('====================================');
        
        foreach ($results as $name => $result) {
            $mark = $result === 'PASS' ? '✅' : '❌';
            $this->line("{$mark} {$name}: {$result}");
        }

        $this->newLine();
        if ($failed === 0) {
            $this->info('✅ ALL TESTS PASSED - SYSTEM IS AUDIT-READY!');
        } else {
            $this->warn("⚠️ {$failed} test(s) failed - Review before audit");
        }

        return $failed;
    }
}
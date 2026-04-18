<?php
// app/Console/Commands/UpdateAgingCategories.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccountsReceivableService;

class UpdateAgingCategories extends Command
{
    protected $signature = 'ar:update-aging';
    protected $description = 'Update aging categories for all invoices';

    public function handle(AccountsReceivableService $arService)
    {
        $this->info('Updating aging categories...');
        
        $arService->updateAgingCategories();
        
        $this->info('Aging categories updated successfully!');
    }
}
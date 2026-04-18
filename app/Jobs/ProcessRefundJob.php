<?php
// app/Jobs/ProcessRefundJob.php

namespace App\Jobs;

use App\Models\Refund;
use App\Services\MPesaPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Refund $refund;

    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    public function handle(MPesaPaymentService $mpesaService)
    {
        try {
            if ($this->refund->refund_method === 'mpesa') {
                // Process M-Pesa refund
                $result = $mpesaService->processRefund($this->refund);
                
                if ($result['success']) {
                    $this->refund->update([
                        'status' => 'processed',
                        'transaction_reference' => $result['reference'],
                        'processed_at' => now()
                    ]);
                } else {
                    $this->fail(new \Exception($result['message']));
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Refund processing job failed', [
                'refund_id' => $this->refund->id,
                'error' => $e->getMessage()
            ]);
            
            $this->fail($e);
        }
    }
}
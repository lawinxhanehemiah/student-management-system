<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * NMB Mobile Webhook
     */
    public function nmbWebhook(Request $request)
    {
        Log::info('NMB Webhook Received:', $request->all());
        
        // Parse NMB response
        $data = [
            'control_number' => $request->input('BillNumber'),
            'transaction_id' => $request->input('TransactionID'),
            'amount' => $request->input('Amount'),
            'payment_method' => 'nmb',
            'mobile_number' => $request->input('MSISDN'),
            'reference_number' => $request->input('Reference'),
            'status' => $request->input('Status') == 'successful' ? 'success' : 'failed',
            'timestamp' => $request->input('Timestamp'),
        ];

        // Forward to PaymentController
        return app(\App\Http\Controllers\Applicant\PaymentController::class)
            ->paymentCallback(new Request($data));
    }

    /**
     * Vodacom M-Pesa Webhook
     */
    public function vodaWebhook(Request $request)
    {
        Log::info('Vodacom Webhook Received:', $request->all());
        
        // Parse M-Pesa response
        $result = json_decode($request->input('Result'), true);
        
        $data = [
            'control_number' => $result['BillRefNumber'] ?? null,
            'transaction_id' => $result['TransID'] ?? null,
            'amount' => $result['TransAmount'] ?? 0,
            'payment_method' => 'voda',
            'mobile_number' => $result['MSISDN'] ?? null,
            'reference_number' => $result['TransID'] ?? null,
            'status' => ($result['ResultCode'] ?? 1) == 0 ? 'success' : 'failed',
            'timestamp' => $result['TransTime'] ?? now(),
        ];

        return app(\App\Http\Controllers\Applicant\PaymentController::class)
            ->paymentCallback(new Request($data));
    }

    /**
     * Airtel Money Webhook
     */
    public function airtelWebhook(Request $request)
    {
        Log::info('Airtel Webhook Received:', $request->all());
        
        $data = [
            'control_number' => $request->input('reference'),
            'transaction_id' => $request->input('transaction_id'),
            'amount' => $request->input('amount'),
            'payment_method' => 'airtel',
            'mobile_number' => $request->input('msisdn'),
            'reference_number' => $request->input('reference'),
            'status' => $request->input('status') == 'SUCCESS' ? 'success' : 'failed',
            'timestamp' => $request->input('timestamp'),
        ];

        return app(\App\Http\Controllers\Applicant\PaymentController::class)
            ->paymentCallback(new Request($data));
    }
}
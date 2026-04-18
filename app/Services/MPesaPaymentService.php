<?php
// app/Services/MPesaPaymentService.php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class MPesaPaymentService
{
    protected $gateway;
    protected $config;
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $shortcode;
    protected $passkey;
    protected $callbackUrl;

    public function __construct()
    {
        // Get gateway from database - using your existing table
        $this->gateway = PaymentGateway::where('code', 'mpesa')
            ->where('is_active', 1)
            ->first();
        
        if (!$this->gateway) {
            Log::error('M-Pesa gateway not found or inactive');
            throw new Exception('M-Pesa gateway not configured');
        }

        // Load config from database or env
        $this->consumerKey = env('MPESA_CONSUMER_KEY');
        $this->consumerSecret = env('MPESA_CONSUMER_SECRET');
        $this->shortcode = env('MPESA_SHORTCODE', '174379');
        $this->passkey = env('MPESA_PASSKEY');
        
        // Set environment
        $this->sandbox = env('MPESA_ENVIRONMENT', 'sandbox') === 'sandbox';
        
        // Set base URL
        $this->baseUrl = $this->sandbox 
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';
        
        // Get callback URL from gateway config or use default
        $gatewayConfig = json_decode($this->gateway->config ?? '{}', true);
        $this->callbackUrl = $gatewayConfig['callback_url'] ?? route('api.mpesa.callback');
        
        Log::info('M-Pesa Service Initialized', [
            'gateway_id' => $this->gateway->id,
            'environment' => $this->sandbox ? 'sandbox' : 'production',
            'shortcode' => $this->shortcode,
            'callback_url' => $this->callbackUrl
        ]);
    }

    /**
     * Get OAuth token
     */
    protected function getAccessToken($forceNew = false)
    {
        $cacheKey = 'mpesa_access_token_' . $this->gateway->id;
        
        if (!$forceNew && Cache::has($cacheKey)) {
            $token = Cache::get($cacheKey);
            Log::info('Using cached M-Pesa token');
            return $token;
        }

        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        try {
            Log::info('Requesting new M-Pesa token');
            
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->withOptions([
                    'verify' => false,
                    'timeout' => 30
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new Exception('HTTP Error: ' . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                throw new Exception('Access token not found in response');
            }

            $token = $data['access_token'];
            
            // Cache token for 50 minutes (M-Pesa tokens expire after 1 hour)
            Cache::put($cacheKey, $token, now()->addMinutes(50));
            
            Log::info('New M-Pesa token obtained', [
                'token_preview' => substr($token, 0, 20) . '...'
            ]);
            
            return $token;

        } catch (Exception $e) {
            Log::error('M-Pesa Token Error', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            throw $e;
        }
    }

    /**
     * Generate password for STK Push
     */
    protected function generatePassword()
    {
        $timestamp = date('YmdHis');
        $data = $this->shortcode . $this->passkey . $timestamp;
        
        return [
            'password' => base64_encode($data),
            'timestamp' => $timestamp
        ];
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle different formats
        if (substr($phone, 0, 1) == '0') {
            // Format: 0712345678 -> 254712345678
            $phone = '254' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) == '7') {
            // Format: 712345678 -> 254712345678
            $phone = '254' . $phone;
        } elseif (substr($phone, 0, 4) == '2547') {
            // Already in correct format
            // Do nothing
        } elseif (substr($phone, 0, 3) == '254') {
            // Format: 25412345678 (invalid, should be 2547...)
            throw new Exception('Invalid phone number. Must start with 2547 or 07');
        } else {
            throw new Exception('Invalid phone number format. Use 07XX or 2547XX');
        }
        
        // Validate length
        if (strlen($phone) != 12) {
            throw new Exception('Invalid phone number length. Expected 12 digits');
        }
        
        return $phone;
    }

    /**
     * Initiate STK Push
     */
    public function initiateSTKPush(Invoice $invoice, string $phoneNumber, ?float $amount = null)
    {
        DB::beginTransaction();
        
        try {
            // Format phone
            $phone = $this->formatPhoneNumber($phoneNumber);
            
            // Determine amount
            $paymentAmount = $amount ?? $invoice->balance;
            
            // Validate amount
            if ($paymentAmount <= 0) {
                throw new Exception('Amount must be greater than 0');
            }
            
            if ($paymentAmount > $invoice->balance) {
                throw new Exception('Amount exceeds invoice balance of ' . number_format($invoice->balance, 2));
            }
            
            // Get token
            $token = $this->getAccessToken();
            
            // Generate password
            $auth = $this->generatePassword();
            
            // Prepare STK Push request
            $stkUrl = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
            
            $accountRef = substr($invoice->invoice_number, 0, 12);
            
            $body = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $auth['password'],
                'Timestamp' => $auth['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) round($paymentAmount),
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => $accountRef,
                'TransactionDesc' => 'School Fees Payment'
            ];
            
            Log::info('Sending STK Push', [
                'invoice' => $invoice->invoice_number,
                'phone' => $phone,
                'amount' => $paymentAmount,
                'reference' => $accountRef
            ]);
            
            // Send request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])
            ->withOptions(['verify' => false, 'timeout' => 30])
            ->post($stkUrl, $body);
            
            $result = $response->json();
            
            Log::info('STK Push Response', $result);
            
            // Check response
            if (!$response->successful() || ($result['ResponseCode'] ?? '1') != '0') {
                throw new Exception($result['errorMessage'] ?? $result['ResponseDescription'] ?? 'STK Push failed');
            }
            
            // Create payment record
            $payment = Payment::create([
                'payment_number' => 'MP' . time() . rand(100, 999),
                'payable_type' => Invoice::class,
                'payable_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'academic_year_id' => $invoice->academic_year_id,
                'payment_gateway_id' => $this->gateway->id,
                'amount' => $paymentAmount,
                'paid_amount' => 0,
                'balance' => $paymentAmount,
                'payment_method' => 'mpesa',
                'transaction_id' => $result['CheckoutRequestID'],
                'status' => 'pending',
                'metadata' => [
                    'phone' => $phone,
                    'checkout_id' => $result['CheckoutRequestID'],
                    'merchant_request_id' => $result['MerchantRequestID'] ?? null,
                    'request_data' => $body,
                    'response_data' => $result
                ]
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'STK Push sent. Please check your phone and enter PIN.',
                'checkout_id' => $result['CheckoutRequestID'],
                'payment' => $payment
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('STK Push initiation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process M-Pesa callback
     */
    public function processCallback(array $data)
    {
        DB::beginTransaction();
        
        try {
            Log::info('Processing M-Pesa callback', $data);
            
            // Extract callback data
            $callbackData = $data['Body']['stkCallback'] ?? null;
            
            if (!$callbackData) {
                throw new Exception('Invalid callback data structure');
            }
            
            $checkoutId = $callbackData['CheckoutRequestID'];
            $resultCode = $callbackData['ResultCode'];
            $resultDesc = $callbackData['ResultDesc'];
            
            // Find payment
            $payment = Payment::where('transaction_id', $checkoutId)->first();
            
            if (!$payment) {
                throw new Exception('Payment not found for checkout ID: ' . $checkoutId);
            }
            
            // Update payment metadata with callback
            $metadata = $payment->metadata ?? [];
            $metadata['callback_data'] = $callbackData;
            
            // Handle successful payment
            if ($resultCode == 0) {
                $items = $callbackData['CallbackMetadata']['Item'] ?? [];
                
                $amount = 0;
                $receipt = '';
                $phone = '';
                
                foreach ($items as $item) {
                    if ($item['Name'] == 'Amount') $amount = $item['Value'];
                    if ($item['Name'] == 'MpesaReceiptNumber') $receipt = $item['Value'];
                    if ($item['Name'] == 'PhoneNumber') $phone = $item['Value'];
                }
                
                // Get invoice
                $invoice = $payment->payable;
                
                // Add payment to invoice
                $invoice->addPayment(
                    $amount,
                    'mpesa',
                    $receipt,
                    'M-Pesa payment via ' . $phone,
                    [
                        'checkout_id' => $checkoutId,
                        'receipt' => $receipt,
                        'phone' => $phone
                    ]
                );
                
                // Update payment record
                $payment->update([
                    'status' => 'completed',
                    'paid_amount' => $amount,
                    'balance' => 0,
                    'receipt_number' => $receipt,
                    'paid_at' => now(),
                    'metadata' => $metadata
                ]);
                
                Log::info('Payment completed', [
                    'checkout_id' => $checkoutId,
                    'receipt' => $receipt,
                    'amount' => $amount
                ]);
                
            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'metadata' => $metadata
                ]);
                
                Log::warning('Payment failed', [
                    'checkout_id' => $checkoutId,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc
                ]);
            }
            
            DB::commit();
            
            return [
                'success' => $resultCode == 0,
                'message' => $resultDesc,
                'checkout_id' => $checkoutId
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Callback processing failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Query transaction status
     */
    public function queryStatus($checkoutId)
    {
        try {
            $token = $this->getAccessToken();
            $auth = $this->generatePassword();
            
            $queryUrl = $this->baseUrl . '/mpesa/stkpushquery/v1/query';
            
            $body = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $auth['password'],
                'Timestamp' => $auth['timestamp'],
                'CheckoutRequestID' => $checkoutId
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])
            ->withOptions(['verify' => false])
            ->post($queryUrl, $body);
            
            return $response->json();
            
        } catch (Exception $e) {
            Log::error('Query status failed', [
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Debug method to test connection
     */
    public function debugConnection()
    {
        $results = [];
        
        // Test 1: Check gateway
        $results['gateway'] = [
            'exists' => $this->gateway ? true : false,
            'active' => $this->gateway?->is_active ?? false,
            'id' => $this->gateway?->id ?? null
        ];
        
        // Test 2: Get token
        try {
            Cache::forget('mpesa_access_token_' . $this->gateway?->id);
            $token = $this->getAccessToken(true);
            $results['token'] = [
                'success' => true,
                'preview' => substr($token, 0, 20) . '...',
                'length' => strlen($token)
            ];
        } catch (Exception $e) {
            $results['token'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 3: Generate password
        try {
            $auth = $this->generatePassword();
            $results['password'] = [
                'success' => true,
                'timestamp' => $auth['timestamp'],
                'password_preview' => substr($auth['password'], 0, 20) . '...'
            ];
        } catch (Exception $e) {
            $results['password'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 4: Format phone
        try {
            $phone = $this->formatPhoneNumber('0712345678');
            $results['phone'] = [
                'success' => true,
                'formatted' => $phone
            ];
        } catch (Exception $e) {
            $results['phone'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return $results;
    }
}
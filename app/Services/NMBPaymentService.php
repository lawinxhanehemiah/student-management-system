<?php
// app/Services/NMBPaymentService.php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\PaymentNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Exception;
use Random\RandomException;


class NMBPaymentService
{
    protected PaymentGateway $gateway;
    protected array $config;
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected bool $sandbox;
    protected string $username;
    protected string $password;
    protected int $timeout;
    protected int $retryAttempts;
    protected bool $verifySsl;

    public function __construct()
    {
        // Get gateway from database (just metadata, no secrets)
        $this->gateway = PaymentGateway::byCode('nmb')->active()->firstOrFail();
        
        // Get actual credentials from config (not env directly)
        $this->config = Config::get('payment.gateways.nmb', []);
        
        $this->baseUrl = $this->config['base_url'] ?? '';
        $this->consumerKey = $this->config['consumer_key'] ?? '';
        $this->consumerSecret = $this->config['consumer_secret'] ?? '';
        $this->sandbox = $this->config['sandbox'] ?? true;
        $this->username = $this->config['username'] ?? ($this->sandbox ? 'sandbox' : '');
        $this->password = $this->config['password'] ?? ($this->sandbox ? 'sandbox' : '');
        $this->timeout = $this->config['timeout'] ?? 30;
        $this->retryAttempts = $this->config['retry_attempts'] ?? 3;
        $this->verifySsl = Config::get('payment.require_ssl', true);
        
        // Validate configuration on construct
        $this->validateConfiguration();
    }

    /**
     * Check if in sandbox mode
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Validate gateway configuration
     *
     * @throws Exception
     */
    protected function validateConfiguration(): void
    {
        $required = ['base_url', 'consumer_key', 'consumer_secret'];
        
        if (!$this->sandbox) {
            $required = array_merge($required, ['username', 'password']);
        }
        
        $missing = [];
        foreach ($required as $field) {
            if (empty($this->{$field}) && empty($this->config[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            Log::error('NMB Gateway configuration incomplete', [
                'missing_fields' => $missing,
                'sandbox' => $this->sandbox
            ]);
            
            throw new Exception(
                'NMB Gateway configuration incomplete. Missing: ' . implode(', ', $missing) . 
                '. Check your .env and config/payment.php'
            );
        }
    }

    /**
     * Check if gateway is properly configured
     */
    protected function isConfigured(): bool
    {
        try {
            $this->validateConfiguration();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get HTTP client with default configuration
     */
    protected function getHttpClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->timeout)
            ->retry($this->retryAttempts, 1000, function ($exception) {
                // Retry only on connection errors or 5xx responses
                return $exception instanceof RequestException || 
                       ($exception->response && $exception->response->serverError());
            })
            ->withOptions([
                'verify' => $this->verifySsl,
                'http_errors' => true, // Throw on 4xx/5xx
            ])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'STMC-Payment-System/1.0',
                'X-Request-ID' => $this->generateRequestId(),
            ]);
    }

    /**
     * Generate unique request ID for tracking
     */
    protected function generateRequestId(): string
    {
        return uniqid('nmb_', true) . '_' . random_int(1000, 9999);
    }

    /**
     * Get access token using direct login
     *
     * @throws Exception
     */
    protected function getAccessToken(): string
    {
        if (!$this->isConfigured()) {
            throw new Exception('NMB Gateway not properly configured. Check your .env file.');
        }

        $cacheKey = 'nmb_access_token_' . md5($this->consumerKey);
        
        return Cache::remember($cacheKey, 3500, function () {
            try {
                $requestId = $this->generateRequestId();
                
                Log::info('NMB Direct Login initiated', [
                    'request_id' => $requestId,
                    'sandbox' => $this->sandbox
                ]);

                $response = $this->getHttpClient()
                    ->post($this->baseUrl . '/my/logins/direct', [
                        'username' => $this->username,
                        'password' => $this->password,
                        'consumer_key' => $this->consumerKey
                    ]);

                if (!$response->successful()) {
                    throw new Exception(
                        'NMB Direct Login failed: ' . $response->body(),
                        $response->status()
                    );
                }

                $data = $response->json();
                $token = $data['token'] ?? null;

                if (!$token) {
                    throw new Exception('NMB Direct Login response missing token');
                }

                Log::info('NMB Direct Login successful', [
                    'request_id' => $requestId,
                    'expires_in' => $data['expires_in'] ?? 3600
                ]);

                return $token;

            } catch (RequestException $e) {
                Log::error('NMB Direct Login HTTP error', [
                    'error' => $e->getMessage(),
                    'status' => $e->response?->status(),
                    'body' => $e->response?->body()
                ]);
                
                throw new Exception('Failed to authenticate with NMB: ' . $e->getMessage());
                
            } catch (Exception $e) {
                Log::error('NMB Direct Login exception', [
                    'error' => $e->getMessage()
                ]);
                
                throw new Exception('Failed to authenticate with NMB: ' . $e->getMessage());
            }
        });
    }

    /**
     * Generate cryptographically secure control number
     *
     * @throws RandomException
     */
    protected function generateControlNumber(Invoice $invoice): string
    {
        $maxAttempts = Config::get('payment.settings.control_number_max_attempts', 10);
        $attempt = 0;
        
        do {
            $year = date('y');
            $month = date('m');
            $day = date('d');
            
            // Use random_int for cryptographically secure randomness
            $studentIdPadded = str_pad((string)$invoice->student_id, 4, '0', STR_PAD_LEFT);
            $random = random_int(1000, 9999);
            
            $controlNumber = $year . $month . $day . $studentIdPadded . substr((string)$random, 0, 2);
            
            // Check uniqueness
            $exists = Invoice::where('control_number', $controlNumber)->exists();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                // Fallback to timestamp-based to avoid infinite loop
                $controlNumber = $year . $month . $day . $studentIdPadded . substr((string)(time() % 10000), 0, 2);
                break;
            }
            
        } while ($exists);
        
        return $controlNumber;
    }

    /**
     * Generate and register control number with NMB
     *
     * @throws Exception
     */
    public function generateAndRegisterControlNumber(Invoice $invoice): array
    {
        DB::beginTransaction();
        
        try {
            // Check if invoice already has valid control number
            if ($invoice->control_number && 
                $invoice->control_number_status === 'generated' && 
                $invoice->control_number_expiry?->isFuture()) {
                
                DB::commit();
                
                return [
                    'success' => true,
                    'control_number' => $invoice->control_number,
                    'expiry_date' => $invoice->control_number_expiry->format('Y-m-d H:i:s'),
                    'message' => 'Existing valid control number found'
                ];
            }

            // Generate control number
            $controlNumber = $this->generateControlNumber($invoice);
            $expiryDays = Config::get('payment.settings.control_number_expiry_days', 30);
            $expiryDate = now()->addDays($expiryDays);

            // In sandbox mode, skip actual API call
            if ($this->sandbox) {
                return $this->registerControlNumberInSandbox($invoice, $controlNumber, $expiryDate);
            }

            // PRODUCTION: Call actual NMB API
            $token = $this->getAccessToken();
            $requestId = $this->generateRequestId();

            Log::info('Registering control number with NMB', [
                'request_id' => $requestId,
                'invoice_id' => $invoice->id,
                'control_number' => $controlNumber
            ]);

            $response = $this->getHttpClient()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Request-ID' => $requestId
                ])
                ->post($this->baseUrl . '/v1/control-numbers', [
                    'control_number' => $controlNumber,
                    'amount' => $invoice->balance,
                    'currency' => Config::get('payment.currency', 'TZS'),
                    'reference' => $invoice->invoice_number,
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'customer_name' => $invoice->student->user->full_name ?? 'Student',
                    'customer_phone' => $invoice->student->phone ?? null,
                    'customer_email' => $invoice->student->user->email ?? null,
                    'description' => $invoice->description ?? 'School fees payment',
                    'callback_url' => route('webhooks.nmb.payment'),
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'student_id' => $invoice->student_id,
                        'academic_year' => $invoice->academicYear?->name
                    ]
                ]);

            if (!$response->successful()) {
                throw new Exception(
                    'NMB API Error: ' . $response->body(),
                    $response->status()
                );
            }

            $apiResponse = $response->json();

            // Update invoice
            $invoice->update([
                'control_number' => $controlNumber,
                'control_number_status' => 'generated',
                'control_number_expiry' => $expiryDate,
                'metadata' => array_merge($invoice->metadata ?? [], [
                    'nmb_registration' => [
                        'request_id' => $requestId,
                        'registered_at' => now()->toIso8601String(),
                        'api_reference' => $apiResponse['reference'] ?? null,
                        'api_transaction_id' => $apiResponse['transaction_id'] ?? null
                    ]
                ])
            ]);

            DB::commit();

            Log::info('Control number registered successfully', [
                'request_id' => $requestId,
                'invoice_id' => $invoice->id,
                'control_number' => $controlNumber
            ]);

            return [
                'success' => true,
                'control_number' => $controlNumber,
                'expiry_date' => $expiryDate->format('Y-m-d H:i:s'),
                'api_reference' => $apiResponse['reference'] ?? null,
                'message' => 'Control number generated and registered successfully'
            ];

        } catch (RandomException $e) {
            DB::rollBack();
            Log::error('Failed to generate secure random number', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            throw new Exception('System error: Failed to generate secure control number');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Control number generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Failed to generate control number: ' . $e->getMessage());
        }
    }

    /**
     * Register control number in sandbox mode
     */
    protected function registerControlNumberInSandbox(Invoice $invoice, string $controlNumber, \DateTime $expiryDate): array
    {
        $requestId = $this->generateRequestId();

        $invoice->update([
            'control_number' => $controlNumber,
            'control_number_status' => 'generated',
            'control_number_expiry' => $expiryDate,
            'metadata' => array_merge($invoice->metadata ?? [], [
                'nmb_registration' => [
                    'request_id' => $requestId,
                    'registered_at' => now()->toIso8601String(),
                    'sandbox' => true
                ]
            ])
        ]);

        DB::commit();

        Log::info('Control number generated in sandbox', [
            'request_id' => $requestId,
            'invoice_id' => $invoice->id,
            'control_number' => $controlNumber
        ]);

        return [
            'success' => true,
            'control_number' => $controlNumber,
            'expiry_date' => $expiryDate->format('Y-m-d H:i:s'),
            'message' => 'Control number generated successfully (SANDBOX MODE)',
            'sandbox' => true
        ];
    }

   /**
 * Process payment notification (webhook) - WITH IDEMPOTENCY
 *
 * @throws Exception
 */
public function processPaymentNotification(array $data): array
{
    // Get idempotency key from data
    $idempotencyKey = $data['idempotency_key'] ?? $data['transaction_id'] ?? null;
    
    if (!$idempotencyKey) {
        // Generate from data to prevent duplicates
        $idempotencyKey = md5($data['control_number'] . $data['amount'] . $data['transaction_id']);
    }
    
    // Check if already processed using cache
    $cacheKey = 'nmb_webhook_' . $idempotencyKey;
    
    if (Cache::has($cacheKey)) {
        Log::info('Duplicate NMB webhook detected (cache)', [
            'idempotency_key' => $idempotencyKey,
            'control_number' => $data['control_number']
        ]);
        
        return [
            'success' => true,
            'message' => 'Payment already processed',
            'duplicate' => true
        ];
    }
    
    // Store in cache immediately (expires after 24 hours)
    Cache::put($cacheKey, true, now()->addHours(24));
    
    DB::beginTransaction();
    
    try {
        // Validate required fields
        $required = ['control_number', 'amount', 'transaction_id'];
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }

        // Validate amount
        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            throw new Exception('Invalid payment amount: ' . $amount);
        }

        // Check if already processed in database (extra safety)
        $existingNotification = PaymentNotification::where('transaction_id', $data['transaction_id'])
            ->where('status', 'processed')
            ->first();
            
        if ($existingNotification) {
            Log::info('Duplicate NMB webhook detected (database)', [
                'transaction_id' => $data['transaction_id'],
                'existing_id' => $existingNotification->id
            ]);
            
            DB::rollBack();
            Cache::put($cacheKey, true, now()->addHours(24));
            
            return [
                'success' => true,
                'message' => 'Payment already processed',
                'duplicate' => true
            ];
        }

        // Create notification record
        $notification = PaymentNotification::create([
            'notification_id' => $data['notification_id'] ?? uniqid('NMB_', true),
            'payment_type' => $data['payment_type'] ?? 'bank',
            'control_number' => $data['control_number'],
            'transaction_id' => $data['transaction_id'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'msisdn' => $data['msisdn'] ?? $data['phone_number'] ?? null,
            'amount' => $amount,
            'currency' => $data['currency'] ?? Config::get('payment.currency', 'TZS'),
            'reference' => $data['reference'] ?? null,
            'raw_data' => $data,
            'status' => 'received',
            'received_at' => now()
        ]);

        // Find invoice by control number
        $invoice = Invoice::where('control_number', $data['control_number'])
            ->where('control_number_status', 'generated')
            ->where(function ($query) {
                $query->whereNull('control_number_expiry')
                    ->orWhere('control_number_expiry', '>', now());
            })
            ->lockForUpdate()
            ->first();

        if (!$invoice) {
            $notification->update([
                'status' => 'failed',
                'processing_error' => 'Invoice not found or control number expired'
            ]);
            
            DB::commit();
            
            return [
                'success' => false,
                'message' => 'Invoice not found or control number expired'
            ];
        }

        // Validate payment amount doesn't exceed invoice balance
        if ($amount > $invoice->balance) {
            $notification->update([
                'status' => 'failed',
                'processing_error' => 'Payment amount exceeds invoice balance'
            ]);
            
            DB::commit();
            
            return [
                'success' => false,
                'message' => 'Payment amount exceeds invoice balance'
            ];
        }

        // Process payment
        $paymentMethod = match ($data['payment_type'] ?? 'bank') {
            'mobile' => 'mobile_money',
            'atm' => 'bank_transfer',
            'branch' => 'cash',
            default => 'bank_transfer'
        };

        // Add payment to invoice
        $invoice->addPayment(
            $amount,
            $paymentMethod,
            $data['transaction_id'],
            "Auto-reconciled from NMB. Ref: {$data['transaction_reference']}",
            [
                'notification_id' => $notification->id,
                'transaction_reference' => $data['transaction_reference'],
                'payment_channel' => $data['payment_type'] ?? 'bank',
                'reconciled_at' => now()->toIso8601String()
            ]
        );

        // Update notification
        $notification->update([
            'status' => 'processed',
            'processed_at' => now(),
            'invoice_id' => $invoice->id,
            'payment_id' => $invoice->payments()->latest()->first()?->id
        ]);

        DB::commit();

        Log::info('NMB payment processed successfully', [
            'control_number' => $data['control_number'],
            'amount' => $amount,
            'transaction_id' => $data['transaction_id'],
            'invoice_id' => $invoice->id,
            'notification_id' => $notification->id,
            'remaining_balance' => $invoice->fresh()->balance
        ]);

        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'notification_id' => $notification->id,
            'remaining_balance' => $invoice->balance
        ];

    } catch (Exception $e) {
        DB::rollBack();
        Cache::forget($cacheKey); // Remove cache if failed
        
        Log::error('Payment notification processing failed', [
            'data' => $data,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return [
            'success' => false,
            'message' => 'Failed to process payment: ' . $e->getMessage()
        ];
    }
}
    /**
     * Check payment status
     *
     * @throws Exception
     */
    public function checkPaymentStatus(string $controlNumber): array
    {
        try {
            // Check in our database first
            $invoice = Invoice::where('control_number', $controlNumber)->first();
            
            if ($invoice && $invoice->balance < $invoice->total_amount) {
                return [
                    'success' => true,
                    'paid' => true,
                    'amount_paid' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'payment_status' => $invoice->payment_status,
                    'source' => 'database'
                ];
            }

            // In sandbox, simulate status check
            if ($this->sandbox) {
                return [
                    'success' => true,
                    'paid' => false,
                    'amount_paid' => 0,
                    'balance' => $invoice?->balance ?? 0,
                    'message' => 'No payment found (SANDBOX)',
                    'source' => 'sandbox'
                ];
            }

            // PRODUCTION: Call NMB API
            $token = $this->getAccessToken();
            $requestId = $this->generateRequestId();

            $response = $this->getHttpClient()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Request-ID' => $requestId
                ])
                ->get($this->baseUrl . '/v1/payments/status', [
                    'control_number' => $controlNumber
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to check payment status: ' . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'paid' => $data['paid'] ?? false,
                'amount_paid' => $data['amount_paid'] ?? 0,
                'payment_date' => $data['payment_date'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'source' => 'api'
            ];

        } catch (Exception $e) {
            Log::error('Payment status check failed', [
                'control_number' => $controlNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to check payment status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify webhook signature - SANDBOX FRIENDLY
     *
     * @throws Exception
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        // SKIP VERIFICATION IN SANDBOX MODE
        if ($this->sandbox) {
            Log::info('Sandbox mode: Skipping webhook signature verification');
            return true;
        }
        
        $signatureHeader = Config::get('payment.settings.webhook_signature_header', 'X-NMB-Signature');
        $signature = $request->header($signatureHeader);
        
        if (!$signature) {
            Log::warning('Webhook signature missing', [
                'headers' => $request->headers->all()
            ]);
            
            throw new Exception('Missing webhook signature');
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $this->consumerSecret);
        
        $isValid = hash_equals($expectedSignature, $signature);
        
        if (!$isValid) {
            Log::error('Invalid webhook signature', [
                'expected' => $expectedSignature,
                'received' => $signature
            ]);
            
            throw new Exception('Invalid webhook signature');
        }

        return true;
    }

    /**
     * Initiate mobile money payment
     *
     * @throws Exception
     */
    public function initiateMobilePayment(Invoice $invoice, string $phoneNumber, ?float $amount = null): array
    {
        DB::beginTransaction();
        
        try {
            $paymentAmount = $amount ?? $invoice->balance;
            
            if ($paymentAmount > $invoice->balance) {
                throw new Exception('Payment amount exceeds invoice balance');
            }

            if ($paymentAmount < Config::get('payment.settings.min_payment_amount', 1000)) {
                throw new Exception('Payment amount below minimum allowed');
            }

            // Ensure control number exists
            if (!$invoice->control_number) {
                $controlNumberResult = $this->generateAndRegisterControlNumber($invoice);
                $invoice->refresh();
            }

            // Create payment record
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'payable_type' => Invoice::class,
                'payable_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'academic_year_id' => $invoice->academic_year_id,
                'payment_gateway_id' => $this->gateway->id,
                'amount' => $paymentAmount,
                'paid_amount' => 0,
                'balance' => $paymentAmount,
                'payment_method' => 'mobile_money',
                'transaction_type' => $paymentAmount < $invoice->balance ? 'partial_payment' : 'full_payment',
                'control_number' => $invoice->control_number,
                'status' => 'pending',
                'metadata' => [
                    'phone_number' => $phoneNumber,
                    'initiated_at' => now()->toIso8601String()
                ],
                'created_by' => auth()->id()
            ]);

            // In sandbox mode, simulate success
            if ($this->sandbox) {
                $reference = 'MP_' . time() . '_' . $payment->id;
                
                $payment->update([
                    'reference_number' => $reference,
                    'gateway_response' => [
                        'success' => true,
                        'message' => 'Payment request sent to customer (SANDBOX)',
                        'reference' => $reference
                    ],
                    'status' => 'pending'
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Mobile payment initiated. Please check your phone to complete payment. (SANDBOX)',
                    'payment' => $payment,
                    'reference' => $reference,
                    'sandbox' => true
                ];
            }

            // PRODUCTION: Call NMB mobile money API
            $token = $this->getAccessToken();
            $requestId = $this->generateRequestId();

            $response = $this->getHttpClient()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Request-ID' => $requestId
                ])
                ->post($this->baseUrl . '/v1/mobile/payments', [
                    'phone_number' => $phoneNumber,
                    'amount' => $paymentAmount,
                    'currency' => Config::get('payment.currency', 'TZS'),
                    'control_number' => $invoice->control_number,
                    'reference' => $payment->payment_number,
                    'description' => "Payment for invoice {$invoice->invoice_number}",
                    'callback_url' => route('webhooks.nmb.payment')
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to initiate mobile payment: ' . $response->body());
            }

            $apiResponse = $response->json();

            $payment->update([
                'reference_number' => $apiResponse['reference'] ?? null,
                'transaction_id' => $apiResponse['transaction_id'] ?? null,
                'gateway_response' => $apiResponse,
                'gateway_request' => [
                    'phone' => $phoneNumber,
                    'amount' => $paymentAmount,
                    'request_id' => $requestId
                ]
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Mobile payment initiated successfully',
                'payment' => $payment,
                'reference' => $apiResponse['reference'] ?? null,
                'transaction_id' => $apiResponse['transaction_id'] ?? null
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Mobile payment initiation failed', [
                'invoice_id' => $invoice->id,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            throw new Exception('Failed to initiate mobile payment: ' . $e->getMessage());
        }
    }
}
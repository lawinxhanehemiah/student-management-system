<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Services\NMBPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;
use App\Models\ProgrammeFee;
use App\Models\Programme;
use Carbon\Carbon;






class PaymentController extends Controller
{
    protected NMBPaymentService $nmbService;

    /**
     * Constructor - NO MIDDLEWARE HERE for Laravel 11
     * Middleware inawekwa kwenye routes
     */
    public function __construct(NMBPaymentService $nmbService)
    {
        $this->nmbService = $nmbService;
        // DO NOT USE $this->middleware() in Laravel 11
    }

    /**
     * Display payment page for invoice
     */
    public function showPaymentPage(string $invoiceNumber): View
    {
        try {
            $invoice = Invoice::with([
                'student.user',
                'student.programme',
                'academicYear',
                'items'
            ])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

            // Get active and configured gateways
            $gateways = PaymentGateway::active()
                ->orderBy('sort_order')
                ->get()
                ->filter(function ($gateway) {
                    return $this->isGatewayConfigured($gateway->code);
                });

            // Get recent payments for this invoice
            $recentPayments = Payment::where('payable_type', Invoice::class)
                ->where('payable_id', $invoice->id)
                ->with('gateway')
                ->latest()
                ->limit(5)
                ->get();

            return view('payments.page', [
                'invoice' => $invoice,
                'gateways' => $gateways,
                'recentPayments' => $recentPayments,
                'paymentProgress' => $invoice->payment_progress,
                'minAmount' => config('payment.settings.min_payment_amount', 1000),
                'currency' => config('payment.currency', 'TZS'),
                'currencySymbol' => config('payment.currency_symbol', 'TSh')
            ]);

        } catch (NotFoundHttpException $e) {
            abort(404, 'Invoice not found');
        } catch (Exception $e) {
            Log::error('Failed to load payment page', [
                'invoice' => $invoiceNumber ?? null,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Failed to load payment page');
        }
    }

    /**
     * Public payment page (no login required)
     */
    public function publicPaymentPage(Request $request): View
    {
        try {
            $request->validate([
                'invoice' => 'required|string',
                'token' => 'required|string'
            ]);

            // Verify token
            $invoice = Invoice::with(['student.user', 'academicYear', 'items'])
                ->where('invoice_number', $request->invoice)
                ->firstOrFail();

            $expectedToken = hash('sha256', $invoice->id . $invoice->student_id . config('app.key'));
            
            if (!hash_equals($expectedToken, $request->token)) {
                abort(403, 'Invalid payment link');
            }

            $gateways = PaymentGateway::active()->get();

            return view('payments.public', [
                'invoice' => $invoice,
                'gateways' => $gateways,
                'token' => $request->token
            ]);

        } catch (ValidationException $e) {
            abort(422, 'Invalid payment parameters');
        } catch (NotFoundHttpException $e) {
            abort(404, 'Invoice not found');
        }
    }

    /**
     * Check if gateway is configured
     */
    protected function isGatewayConfigured(string $code): bool
    {
        $config = config("payment.gateways.{$code}", []);
        
        if ($code === 'nmb') {
            return !empty($config['consumer_key']) && !empty($config['consumer_secret']);
        }
        
        return !empty($config);
    }

    /**
     * Generate control number for invoice
     */
    public function generateControlNumber(Request $request, string $invoiceNumber): JsonResponse
    {
        try {
            $request->validate([
                'force_regenerate' => 'boolean'
            ]);

            $invoice = Invoice::where('invoice_number', $invoiceNumber)
                ->where('payment_status', '!=', 'paid')
                ->firstOrFail();

            // Check authorization
            if (!$this->authorizePaymentAction($invoice)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to perform this action'
                ], 403);
            }

            // Check if invoice already has valid control number
            if (!$request->boolean('force_regenerate') && 
                $invoice->control_number && 
                $invoice->control_number_status === 'generated' &&
                $invoice->control_number_expiry?->isFuture()) {
                
                return response()->json([
                    'success' => true,
                    'control_number' => $invoice->control_number,
                    'expiry_date' => $invoice->control_number_expiry->format('Y-m-d H:i:s'),
                    'message' => 'Existing control number is still valid'
                ]);
            }

            // Generate new control number
            $result = $this->nmbService->generateAndRegisterControlNumber($invoice);

            if ($result['success']) {
                if (class_exists('Activity')) {
                    activity()
                        ->performedOn($invoice)
                        ->causedBy(Auth::user())
                        ->log('Generated control number: ' . $result['control_number']);
                }

                return response()->json([
                    'success' => true,
                    'control_number' => $result['control_number'],
                    'expiry_date' => $result['expiry_date'],
                    'message' => $result['message']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to generate control number'
            ], 422);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Control number generation error', [
                'invoice' => $invoiceNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate control number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate mobile payment
     */
    public function initiateMobilePayment(Request $request, string $invoiceNumber): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => [
                    'required',
                    'string',
                    'regex:/^(0|255)\d{9}$/',
                    'min:10',
                    'max:13'
                ],
                'amount' => [
                    'nullable',
                    'numeric',
                    'min:' . config('payment.settings.min_payment_amount', 1000)
                ],
                'gateway_code' => 'required|string|in:nmb,mpesa,tigo,airtel,halopesa'
            ], [
                'phone_number.regex' => 'Invalid phone number format. Use 0XXXXXXXXX or 255XXXXXXXXX',
                'amount.min' => 'Minimum payment amount is ' . config('payment.settings.min_payment_amount', 1000)
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $invoice = Invoice::where('invoice_number', $invoiceNumber)
                ->where('payment_status', '!=', 'paid')
                ->firstOrFail();

            // Authorize action
            if (!$this->authorizePaymentAction($invoice)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to perform this action'
                ], 403);
            }

            $amount = $request->amount ?? $invoice->balance;

            // Validate amount doesn't exceed balance
            if ($amount > $invoice->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed invoice balance of ' . 
                                number_format($invoice->balance, 2)
                ], 422);
            }

            // Route to appropriate gateway service
            $result = match ($request->gateway_code) {
                'nmb' => $this->nmbService->initiateMobilePayment($invoice, $request->phone_number, $amount),
                default => throw new Exception('Gateway not implemented: ' . $request->gateway_code)
            };

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'reference' => $result['reference'] ?? null,
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'payment_id' => $result['payment']->id ?? null,
                    'redirect_url' => $result['redirect_url'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate payment'
            ], 422);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Mobile payment initiation error', [
                'invoice' => $invoiceNumber,
                'phone' => $request->phone_number ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process manual payment (cash, bank slip)
     */
    public function processManualPayment(Request $request, string $invoiceNumber): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => [
                    'required',
                    'numeric',
                    'min:' . config('payment.settings.min_payment_amount', 1000)
                ],
                'payment_method' => 'required|in:cash,bank_transfer,cheque',
                'reference_number' => 'nullable|string|max:255',
                'receipt_number' => 'nullable|string|max:255',
                'payment_date' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string|max:500',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ensure user is authorized for manual payments
            if (!Auth::user()?->can('process-manual-payments')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $invoice = Invoice::where('invoice_number', $invoiceNumber)
                ->where('payment_status', '!=', 'paid')
                ->firstOrFail();

            // Validate amount
            if ($request->amount > $invoice->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed invoice balance'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Create payment record
                $payment = Payment::create([
                    'payment_number' => Payment::generatePaymentNumber(),
                    'payable_type' => Invoice::class,
                    'payable_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                    'academic_year_id' => $invoice->academic_year_id,
                    'payment_gateway_id' => null,
                    'amount' => $request->amount,
                    'paid_amount' => 0,
                    'balance' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'transaction_type' => $request->amount < $invoice->balance ? 'partial_payment' : 'full_payment',
                    'control_number' => $invoice->control_number,
                    'reference_number' => $request->reference_number,
                    'receipt_number' => $request->receipt_number,
                    'status' => 'pending_verification',
                    'metadata' => [
                        'payment_date' => $request->payment_date,
                        'notes' => $request->notes,
                        'submitted_by' => Auth::id(),
                        'submitted_at' => now()->toIso8601String()
                    ],
                    'created_by' => Auth::id()
                ]);

                // Handle attachments if any
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('payments/' . $payment->id, 'public');
                        
                        $payment->update([
                            'metadata' => array_merge($payment->metadata ?? [], [
                                'attachments' => array_merge(
                                    $payment->metadata['attachments'] ?? [],
                                    [$path]
                                )
                            ])
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully. Waiting for verification.',
                    'payment' => [
                        'id' => $payment->id,
                        'number' => $payment->payment_number,
                        'amount' => $payment->amount,
                        'status' => $payment->status
                    ]
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Manual payment processing error', [
                'invoice' => $invoiceNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify manual payment
     */
    public function verifyManualPayment(Request $request, int $paymentId): JsonResponse
    {
        try {
            if (!Auth::user()?->can('verify-payments')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approve,reject',
                'rejection_reason' => 'required_if:action,reject|string|max:500',
                'receipt_number' => 'required_if:action,approve|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $payment = Payment::with('payable')
                ->where('status', 'pending_verification')
                ->findOrFail($paymentId);

            DB::beginTransaction();

            try {
                if ($request->action === 'approve') {
                    // Mark payment as completed
                    $payment->payable->addPayment(
                        amount: $payment->amount,
                        method: $payment->payment_method,
                        reference: $request->receipt_number,
                        notes: 'Payment verified by ' . Auth::user()->name
                    );

                    $payment->update([
                        'status' => 'completed',
                        'receipt_number' => $request->receipt_number,
                        'paid_at' => now(),
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'verified_by' => Auth::id(),
                            'verified_at' => now()->toIso8601String()
                        ])
                    ]);

                    $message = 'Payment approved successfully';
                } else {
                    // Reject payment
                    $payment->update([
                        'status' => 'rejected',
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'rejected_by' => Auth::id(),
                            'rejected_at' => now()->toIso8601String(),
                            'rejection_reason' => $request->rejection_reason
                        ])
                    ]);

                    $message = 'Payment rejected';
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'payment' => $payment
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Payment verification error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment'
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request, string $controlNumber): JsonResponse
    {
        try {
            $request->validate([
                'gateway' => 'sometimes|string|in:nmb,crdb'
            ]);

            $gateway = $request->get('gateway', 'nmb');

            $result = match ($gateway) {
                'nmb' => $this->nmbService->checkPaymentStatus($controlNumber),
                default => ['success' => false, 'message' => 'Gateway not supported']
            };

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Payment status check error', [
                'control_number' => $controlNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status'
            ], 500);
        }
    }

    /**
     * Handle NMB webhook
     */
    public function handleNMBWebhook(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature
            $this->nmbService->verifyWebhookSignature($request);

            Log::info('NMB webhook received', [
                'headers' => $request->headers->all(),
                'payload' => $request->all()
            ]);

            $result = $this->nmbService->processPaymentNotification($request->all());

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment processed successfully',
                    'reference' => $result['notification_id'] ?? null
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 400);

        } catch (Exception $e) {
            Log::error('NMB webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history
     */
    public function paymentHistory(Request $request): View
    {
        try {
            $query = Payment::with([
                'payable',
                'student.user',
                'gateway',
                'createdBy'
            ])->latest();

            // Apply filters
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('min_amount')) {
                $query->where('amount', '>=', $request->min_amount);
            }

            if ($request->filled('max_amount')) {
                $query->where('amount', '<=', $request->max_amount);
            }

            // Paginate
            $payments = $query->paginate(15)->withQueryString();

            // Calculate totals
            $totals = [
                'total_amount' => Payment::sum('amount'),
                'total_paid' => Payment::where('status', 'completed')->sum('amount'),
                'total_pending' => Payment::whereIn('status', ['pending', 'pending_verification'])->sum('amount'),
                'total_failed' => Payment::where('status', 'failed')->sum('amount'),
                'count' => [
                    'completed' => Payment::where('status', 'completed')->count(),
                    'pending' => Payment::whereIn('status', ['pending', 'pending_verification'])->count(),
                    'failed' => Payment::where('status', 'failed')->count()
                ]
            ];

            // Get filter options
            $students = Student::with('user')
                ->whereHas('payments')
                ->limit(100)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->user->first_name . ' ' . $s->user->last_name,
                    'reg_no' => $s->registration_number
                ]);

            return view('payments.history', [
                'payments' => $payments,
                'totals' => $totals,
                'students' => $students,
                'filters' => $request->all(),
                'statuses' => Payment::distinct('status')->pluck('status'),
                'methods' => Payment::distinct('payment_method')->pluck('payment_method')
            ]);

        } catch (Exception $e) {
            Log::error('Failed to load payment history', [
                'error' => $e->getMessage()
            ]);

            abort(500, 'Failed to load payment history');
        }
    }

    /**
     * Get payment details
     */
    public function showPayment(int $paymentId): View
    {
        try {
            $payment = Payment::with([
                'payable',
                'student.user',
                'student.programme',
                'academicYear',
                'gateway',
                'attempts',
                'notifications',
                'createdBy',
                'updatedBy'
            ])->findOrFail($paymentId);

            // Load invoice items if payable is invoice
            if ($payment->payable_type === Invoice::class) {
                $payment->payable->load('items');
            }

            return view('finance.payments.show', [
                'payment' => $payment,
                'canVerify' => Auth::user()?->can('verify-payments')
            ]);

        } catch (NotFoundHttpException $e) {
            abort(404, 'Payment not found');
        }
    }

    /**
     * Download payment receipt
     */
    /**
 * Download payment receipt
 */
public function downloadReceipt(int $paymentId)
{
    try {
        $payment = Payment::with([
            'payable',
            'student.user',
            'student.programme',
            'academicYear'
        ])
        ->where('status', 'completed')
        ->findOrFail($paymentId);

        // Prepare school data
        $school = [
            'name' => config('app.name', 'St. Maximilian Kolbe College'),
            'address' => config('app.address', 'P.O. Box 123, Dar es Salaam'),
            'phone' => config('app.phone', '+255 123 456 789'),
            'email' => config('app.email', 'info@college.ac.tz'),
            'logo' => public_path('images/logo.png')
        ];

        // Check if logo exists, otherwise use default
        if (!file_exists($school['logo'])) {
            $school['logo'] = null;
        }

        // Generate PDF receipt
        $pdf = \PDF::loadView('payments.receipt', [
            'payment' => $payment,
            'school' => $school
        ]);

        // Clean the filename - remove any slashes
        $cleanPaymentNumber = str_replace(['/', '\\'], '-', $payment->payment_number);
        $filename = 'receipt-' . $cleanPaymentNumber . '.pdf';

        // Optional: Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($filename);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Payment not found', ['payment_id' => $paymentId]);
        abort(404, 'Receipt not found');
    } catch (\Exception $e) {
        Log::error('Receipt download failed', [
            'payment_id' => $paymentId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        abort(500, 'Failed to generate receipt: ' . $e->getMessage());
    }
} 
    /**
     * Get payment statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            if (!Auth::user()?->can('view-payment-statistics')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $period = $request->get('period', 'month');
            
            $dateRange = match ($period) {
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'week' => [now()->startOfWeek(), now()->endOfWeek()],
                'month' => [now()->startOfMonth(), now()->endOfMonth()],
                'year' => [now()->startOfYear(), now()->endOfYear()],
                default => [now()->subDays(30), now()]
            };

            $stats = [
                'summary' => [
                    'total_amount' => Payment::sum('amount'),
                    'total_paid' => Payment::where('status', 'completed')->sum('amount'),
                    'total_pending' => Payment::whereIn('status', ['pending', 'pending_verification'])->sum('amount'),
                    'total_count' => Payment::count(),
                    'success_rate' => $this->calculateSuccessRate()
                ],
                'period' => [
                    'amount' => Payment::whereBetween('created_at', $dateRange)->sum('amount'),
                    'count' => Payment::whereBetween('created_at', $dateRange)->count(),
                    'completed' => Payment::where('status', 'completed')
                        ->whereBetween('created_at', $dateRange)
                        ->count()
                ],
                'by_method' => Payment::where('status', 'completed')
                    ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                    ->groupBy('payment_method')
                    ->get(),
                'by_gateway' => Payment::where('status', 'completed')
                    ->with('gateway')
                    ->select('payment_gateway_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                    ->groupBy('payment_gateway_id')
                    ->get(),
                'daily_trend' => Payment::whereBetween('created_at', [now()->subDays(7), now()])
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(amount) as total')
                    )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get payment statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Calculate success rate
     */
    protected function calculateSuccessRate(): float
    {
        $total = Payment::count();
        if ($total === 0) return 0;
        
        $successful = Payment::where('status', 'completed')->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Authorize payment action
     */
    protected function authorizePaymentAction(Invoice $invoice): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin/Finance can always perform actions
        if ($user->can('manage-payments')) {
            return true;
        }

        // Student can only pay their own invoices
        if (method_exists($user, 'isStudent') && $user->isStudent() && $invoice->student_id === $user->student?->id) {
            return true;
        }

        return false;
    }

    /**
     * Export payments report
     */
    public function exportReport(Request $request)
    {
        try {
            if (!Auth::user()?->can('export-payments')) {
                return back()->with('error', 'Unauthorized');
            }

            $validator = Validator::make($request->all(), [
                'format' => 'required|in:csv,excel,pdf',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'status' => 'nullable|string',
                'payment_method' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $query = Payment::with(['student.user', 'payable', 'gateway'])
                ->whereBetween('created_at', [$request->date_from, $request->date_to]);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            $payments = $query->get();

            // Generate export based on format
            return match ($request->format) {
                'csv' => $this->exportCsv($payments),
                'excel' => $this->exportExcel($payments),
                'pdf' => $this->exportPdf($payments),
                default => back()->with('error', 'Invalid format')
            };

        } catch (Exception $e) {
            Log::error('Payment export failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to export payments');
        }
    }

    /**
     * Export as CSV
     */
    protected function exportCsv($payments)
    {
        $filename = 'payments-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Payment Number',
                'Student Name',
                'Registration No',
                'Amount',
                'Method',
                'Status',
                'Reference',
                'Date',
                'Description'
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_number,
                    $payment->student?->user?->full_name ?? 'N/A',
                    $payment->student?->registration_number ?? 'N/A',
                    $payment->amount,
                    $payment->payment_method,
                    $payment->status,
                    $payment->reference_number ?? $payment->transaction_id ?? 'N/A',
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->payable?->description ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export as Excel (using Laravel Excel package)
     */
    protected function exportExcel($payments)
    {
        // Implement using Laravel Excel package
        // return \Maatwebsite\Excel\Facades\Excel::download(new PaymentsExport($payments), 'payments.xlsx');
        throw new Exception('Excel export not implemented');
    }

    /**
     * Export as PDF
     */
    protected function exportPdf($payments)
    {
        $pdf = \PDF::loadView('payments.export-pdf', [
            'payments' => $payments,
            'generated_at' => now()
        ]);

        return $pdf->download('payments-' . now()->format('Y-m-d') . '.pdf');
    }

    public function initiateMPesaPayment(Request $request, string $invoiceNumber)
{
    try {
        $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'nullable|numeric|min:1'
        ]);

        $invoice = Invoice::where('invoice_number', $invoiceNumber)
            ->where('payment_status', '!=', 'paid')
            ->firstOrFail();

        $mpesaService = app(\App\Services\MPesaPaymentService::class);
        $result = $mpesaService->initiateSTKPush($invoice, $request->phone_number, $request->amount);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'checkout_id' => $result['checkout_id'] ?? null
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function handleMPesaCallback(Request $request)
{
    try {
        $mpesaService = app(\App\Services\MPesaPaymentService::class);
        $result = $mpesaService->processCallback($request->all());

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);

    } catch (Exception $e) {
        Log::error('M-Pesa callback error', ['error' => $e->getMessage()]);
        return response()->json([
            'ResultCode' => 1,
            'ResultDesc' => 'Internal Server Error'
        ], 500);
    }
}

/**
 * Initiate M-Pesa STK Push payment using Invoice ID
 */
/**
 * Initiate M-Pesa STK Push payment using Invoice ID
 */
public function initiateMPesaPaymentById(Request $request, int $id)
{
    try {
        $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'nullable|numeric|min:1'
        ]);

        // Tafuta invoice na relationships zake
        $invoice = Invoice::with(['student', 'academicYear'])->find($id);
        
        // Check if invoice exists
        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found with ID: ' . $id
            ], 404);
        }

        // Check if student exists
        if (!$invoice->student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found for this invoice. Please check student relationship.'
            ], 422);
        }

        // Check if academic year exists
        if (!$invoice->academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Academic year not found for this invoice.'
            ], 422);
        }

        if ($invoice->payment_status == 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 422);
        }

        $amount = $request->amount ?? $invoice->balance;

        if ($amount > $invoice->balance) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount cannot exceed invoice balance of ' . 
                            number_format($invoice->balance, 2)
            ], 422);
        }

        // Log invoice details for debugging
        Log::info('M-Pesa payment initiation - Invoice details', [
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'academic_year_id' => $invoice->academic_year_id,
            'balance' => $invoice->balance,
            'amount' => $amount,
            'phone' => $request->phone_number
        ]);

        // Initialize M-Pesa service
        $mpesaService = app(\App\Services\MPesaPaymentService::class);
        
        $result = $mpesaService->initiateSTKPush($invoice, $request->phone_number, $amount);

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Payment initiated successfully',
            'checkout_id' => $result['checkout_id'] ?? null,
            'payment_id' => $result['payment']->id ?? null
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('M-Pesa payment initiation failed', [
            'invoice_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Display all payments with filters for level, semester and academic year
 */
public function allPayments(Request $request)
{
    try {
        $query = Payment::with([
                'student.user', 
                'student.programme',
                'payable',
                'gateway',
                'academicYear'  // 🔴 Load academic year relationship
            ])
            ->where('status', 'completed'); // Only show completed payments

        // 🔴 NEW: Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('current_level', $request->level);
            });
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('current_semester', $request->semester);
            });
        }

        // Filter by programme
        if ($request->filled('programme_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('programme_id', $request->programme_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Get payments with pagination
        $payments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Calculate statistics for filtered results
        $statistics = [
            'total_amount' => $query->sum('amount'),
            'total_count' => $query->count(),
            'average_amount' => $query->avg('amount'),
        ];

        // Get filter options
        $levels = [1, 2, 3, 4];
        $semesters = [1, 2];
        $programmes = \App\Models\Programme::where('is_active', true)->get();
        $paymentMethods = Payment::distinct('payment_method')
            ->where('payment_method', '!=', null)
            ->pluck('payment_method');
        
        // 🔴 NEW: Get all academic years for filter dropdown
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();

        return view('finance.payments.all-payments', compact(
            'payments',
            'statistics',
            'levels',
            'semesters',
            'programmes',
            'paymentMethods',
            'academicYears'  // 🔴 Send to view
        ));

    } catch (Exception $e) {
        Log::error('Failed to load all payments', [
            'error' => $e->getMessage()
        ]);

        abort(500, 'Failed to load payments');
    }
}
/**
 * Export all payments to CSV
 */
public function exportAllPayments(Request $request)
{
    try {
        if (!Auth::user()?->can('export-payments')) {
            return back()->with('error', 'Unauthorized');
        }

        $query = Payment::with(['student.user', 'student.programme', 'payable'])
            ->where('status', 'completed');

        // Apply filters
        if ($request->filled('level')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('current_level', $request->level);
            });
        }

        if ($request->filled('semester')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('current_semester', $request->semester);
            });
        }

        if ($request->filled('programme_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('programme_id', $request->programme_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'payments-export-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Payment #',
                'Date',
                'Student Name',
                'Registration #',
                'Programme',
                'Level',
                'Semester',
                'Amount',
                'Payment Method',
                'Reference',
                'Control #',
                'Invoice #'
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_number,
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->student?->user?->first_name . ' ' . $payment->student?->user?->last_name,
                    $payment->student?->registration_number ?? 'N/A',
                    $payment->student?->programme?->name ?? 'N/A',
                    'Year ' . ($payment->student?->current_level ?? 'N/A'),
                    'Semester ' . ($payment->student?->current_semester ?? 'N/A'),
                    $payment->amount,
                    $payment->payment_method,
                    $payment->transaction_reference ?? $payment->receipt_number ?? 'N/A',
                    $payment->control_number ?? 'N/A',
                    $payment->payable?->invoice_number ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    } catch (Exception $e) {
        Log::error('Payment export failed', [
            'error' => $e->getMessage()
        ]);

        return back()->with('error', 'Failed to export payments: ' . $e->getMessage());
    }
}

/**
 * Get payment statistics by level and semester (AJAX)
 */
public function paymentStatistics(Request $request)
{
    try {
        // Statistics by level
        $byLevel = [];
        for ($level = 1; $level <= 4; $level++) {
            $query = Payment::where('status', 'completed')
                ->whereHas('student', function($q) use ($level) {
                    $q->where('current_level', $level);
                });

            $byLevel[$level] = [
                'count' => $query->count(),
                'amount' => $query->sum('amount')
            ];
        }

        // Statistics by semester
        $bySemester = [];
        for ($semester = 1; $semester <= 2; $semester++) {
            $query = Payment::where('status', 'completed')
                ->whereHas('student', function($q) use ($semester) {
                    $q->where('current_semester', $semester);
                });

            $bySemester[$semester] = [
                'count' => $query->count(),
                'amount' => $query->sum('amount')
            ];
        }

        // Statistics by programme
        $programmes = \App\Models\Programme::where('is_active', true)->get();
        $byProgramme = [];

        foreach ($programmes as $programme) {
            $payments = Payment::where('status', 'completed')
                ->whereHas('student', function($q) use ($programme) {
                    $q->where('programme_id', $programme->id);
                })->get();

            $byProgramme[] = [
                'programme' => $programme->name,
                'student_count' => $programme->students()->count(),
                'payment_count' => $payments->count(),
                'total_amount' => $payments->sum('amount')
            ];
        }

        // Monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Payment::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            $count = Payment::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'amount' => $amount,
                'count' => $count
            ];
        }

        return response()->json([
            'success' => true,
            'by_level' => $byLevel,
            'by_semester' => $bySemester,
            'by_programme' => $byProgramme,
            'monthly_trend' => $monthlyTrend,
            'total_payments' => Payment::where('status', 'completed')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount')
        ]);

    } catch (Exception $e) {
        Log::error('Failed to get payment statistics', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to get statistics: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Payments Management Dashboard - Summary by level and semester
 */
public function paymentsManagementDashboard()
{
    $currentAcademicYear = AcademicYear::where('is_active', true)->first();
    $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
    
    $levels = [1, 2, 3, 4];
    $semesters = [1, 2];
    $feeTypes = ['tuition', 'hostel', 'supplementary', 'special'];
    
    $summary = [];
    
    foreach ($levels as $level) {
        foreach ($semesters as $semester) {
            // Get all students in this level and semester
            $students = Student::where('current_level', $level)
                ->where('current_semester', $semester)
                ->where('status', 'active')
                ->get();
            
            $totalStudents = $students->count();
            $studentsWithData = [];
            
            foreach ($students as $student) {
                // Calculate required fee for this level/semester
                $requiredFee = $this->getRequiredFeeForLevelSemester($student->programme_id, $level, $semester, $academicYearId);
                
                // Calculate total paid
                $totalPaid = Payment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                // Get latest invoice balance
                $latestInvoice = Invoice::where('student_id', $student->id)
                    ->where('academic_year_id', $academicYearId)
                    ->latest()
                    ->first();
                
                $balance = $latestInvoice ? $latestInvoice->balance : $requiredFee - $totalPaid;
                
                $studentsWithData[] = [
                    'student' => $student,
                    'required' => $requiredFee,
                    'paid' => $totalPaid,
                    'balance' => $balance,
                    'eligible' => $balance <= 0 // Fully paid
                ];
            }
            
            $eligibleCount = collect($studentsWithData)->where('eligible', true)->count();
            $notEligibleCount = $totalStudents - $eligibleCount;
            
            $summary[$level][$semester] = [
                'total_students' => $totalStudents,
                'eligible_count' => $eligibleCount,
                'not_eligible_count' => $notEligibleCount,
                'eligible_percentage' => $totalStudents > 0 ? round(($eligibleCount / $totalStudents) * 100, 2) : 0,
                'students' => $studentsWithData
            ];
        }
    }
    
    // Summary by fee type
    $feeTypeSummary = [];
    foreach ($feeTypes as $type) {
        $feeTypeSummary[$type] = [
            'total_paid' => Payment::where('payment_method', 'like', "%{$type}%")
                ->where('status', 'completed')
                ->sum('amount'),
            'count' => Payment::where('payment_method', 'like', "%{$type}%")
                ->where('status', 'completed')
                ->count()
        ];
    }
    
    return view('finance.payments-management.dashboard', compact(
        'summary', 
        'levels', 
        'semesters', 
        'feeTypeSummary',
        'currentAcademicYear'
    ));
}

/**
 * Get required fee for a specific programme, level and semester
 */
private function getRequiredFeeForLevelSemester($programmeId, $level, $semester, $academicYearId)
{
    $feeStructure = ProgrammeFee::where('programme_id', $programmeId)
        ->where('academic_year_id', $academicYearId)
        ->where('level', $level)
        ->first();
    
    if (!$feeStructure) {
        return 0;
    }
    
    if ($semester == 1) {
        return $feeStructure->registration_fee + $feeStructure->semester_1_fee;
    } else {
        return $feeStructure->semester_2_fee;
    }
}

/**
 * Display students by level and semester with payment status
 */
public function studentsByLevelAndSemester($level, $semester)
{
    $currentAcademicYear = AcademicYear::where('is_active', true)->first();
    $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
    
    $students = Student::with(['user', 'programme'])
        ->where('current_level', $level)
        ->where('current_semester', $semester)
        ->where('status', 'active')
        ->get();
    
    $studentsData = [];
    
    foreach ($students as $student) {
        $requiredFee = $this->getRequiredFeeForLevelSemester($student->programme_id, $level, $semester, $academicYearId);
        
        $totalPaid = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->sum('amount');
        
        $latestInvoice = Invoice::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->latest()
            ->first();
        
        $balance = $latestInvoice ? $latestInvoice->balance : $requiredFee - $totalPaid;
        
        // Get recent payments
        $recentPayments = Payment::with('payable')
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();
        
        $studentsData[] = [
            'id' => $student->id,
            'name' => $student->user->first_name . ' ' . $student->user->last_name,
            'reg_no' => $student->registration_number,
            'programme' => $student->programme->name ?? 'N/A',
            'required' => $requiredFee,
            'paid' => $totalPaid,
            'balance' => $balance,
            'eligible' => $balance <= 0,
            'payment_percentage' => $requiredFee > 0 ? round(($totalPaid / $requiredFee) * 100, 2) : 0,
            'recent_payments' => $recentPayments
        ];
    }
    
    // Sort: eligible first, then by balance
    $studentsData = collect($studentsData)->sortByDesc('eligible')->values();
    
    return view('finance.payments-management.students-list', compact(
        'studentsData', 
        'level', 
        'semester',
        'currentAcademicYear'
    ));
}

/**
 * Exam eligibility - Quick view of who can sit for exams
 */
public function examEligibility(Request $request)
{
    $level = $request->get('level', 0);
    $semester = $request->get('semester', 0);
    
    $currentAcademicYear = AcademicYear::where('is_active', true)->first();
    $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
    
    $query = Student::with(['user', 'programme'])
        ->where('status', 'active');
    
    if ($level > 0) {
        $query->where('current_level', $level);
    }
    
    if ($semester > 0) {
        $query->where('current_semester', $semester);
    }
    
    $students = $query->get();
    
    $eligible = [];
    $notEligible = [];
    
    foreach ($students as $student) {
        $requiredFee = $this->getRequiredFeeForLevelSemester(
            $student->programme_id, 
            $student->current_level, 
            $student->current_semester, 
            $academicYearId
        );
        
        $totalPaid = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->sum('amount');
        
        $balance = $requiredFee - $totalPaid;
        
        $studentData = [
            'id' => $student->id,
            'name' => $student->user->first_name . ' ' . $student->user->last_name,
            'reg_no' => $student->registration_number,
            'programme' => $student->programme->name ?? 'N/A',
            'level' => $student->current_level,
            'semester' => $student->current_semester,
            'required' => $requiredFee,
            'paid' => $totalPaid,
            'balance' => $balance
        ];
        
        if ($balance <= 0) {
            $eligible[] = $studentData;
        } else {
            $notEligible[] = $studentData;
        }
    }
    
    return view('finance.payments-management.exam-eligibility', compact(
        'eligible',
        'notEligible',
        'level',
        'semester',
        'currentAcademicYear'
    ));
}

/**
 * Full student statement - Like the example you provided
 */
public function fullStudentStatement($studentId)
{
    $student = Student::with(['user', 'programme'])->findOrFail($studentId);
    
    $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
    $selectedYear = request()->get('academic_year_id');
    
    // Get all transactions (invoices and payments)
    $invoices = Invoice::where('student_id', $studentId)
        ->with(['academicYear'])
        ->when($selectedYear, function($query) use ($selectedYear) {
            return $query->where('academic_year_id', $selectedYear);
        })
        ->orderBy('issue_date')
        ->get();
    
    $payments = Payment::where('student_id', $studentId)
        ->where('status', 'completed')
        ->with(['academicYear', 'payable'])
        ->when($selectedYear, function($query) use ($selectedYear) {
            return $query->where('academic_year_id', $selectedYear);
        })
        ->orderBy('created_at')
        ->get();
    
    // Combine and sort transactions
    $transactions = [];
    
    // Add invoices as DEBIT transactions
    foreach ($invoices as $invoice) {
        $transactions[] = [
            'date' => $invoice->issue_date,
            'academic_year' => $invoice->academicYear->name ?? 'N/A',
            'control_number' => $invoice->control_number ?? 'INVOICE',
            'receipt' => 'INVOICE',
            'fee_type' => ucwords(str_replace('_', ' ', $invoice->invoice_type)),
            'debit' => $invoice->total_amount,
            'installment' => $invoice->total_amount, // First installment
            'credit' => 0,
            'balance' => $invoice->balance,
            'type' => 'invoice',
            'description' => $invoice->description ?? $invoice->invoice_type
        ];
    }
    
    // Add payments as CREDIT transactions
    foreach ($payments as $payment) {
        $transactions[] = [
            'date' => $payment->created_at,
            'academic_year' => $payment->academicYear->name ?? 'N/A',
            'control_number' => $payment->control_number ?? 'N/A',
            'receipt' => 'PAYMENT',
            'fee_type' => $payment->payable->invoice_type ?? 'Payment',
            'debit' => 0,
            'installment' => 0,
            'credit' => $payment->amount,
            'balance' => $payment->balance ?? 0,
            'type' => 'payment',
            'description' => 'Payment received'
        ];
    }
    
    // Sort by date
    $transactions = collect($transactions)->sortBy('date')->values()->toArray();
    
    // Calculate running balance
    $runningBalance = 0;
    foreach ($transactions as &$transaction) {
        if ($transaction['type'] == 'invoice') {
            $runningBalance += $transaction['debit'];
        } else {
            $runningBalance -= $transaction['credit'];
        }
        $transaction['running_balance'] = $runningBalance;
    }
    
    $totalDebit = collect($transactions)->sum('debit');
    $totalCredit = collect($transactions)->sum('credit');
    $closingBalance = $totalDebit - $totalCredit;
    
    return view('finance.payments-management.full-statement', compact(
        'student',
        'transactions',
        'totalDebit',
        'totalCredit',
        'closingBalance',
        'academicYears',
        'selectedYear'
    ));
}

/**
 * Print student statement
 */
public function printStudentStatement($studentId)
{
    $student = Student::with(['user', 'programme'])->findOrFail($studentId);
    
    $selectedYear = request()->get('academic_year_id');
    
    // Similar to fullStudentStatement but for print view
    $invoices = Invoice::where('student_id', $studentId)
        ->with(['academicYear'])
        ->when($selectedYear, function($query) use ($selectedYear) {
            return $query->where('academic_year_id', $selectedYear);
        })
        ->orderBy('issue_date')
        ->get();
    
    $payments = Payment::where('student_id', $studentId)
        ->where('status', 'completed')
        ->with(['academicYear', 'payable'])
        ->when($selectedYear, function($query) use ($selectedYear) {
            return $query->where('academic_year_id', $selectedYear);
        })
        ->orderBy('created_at')
        ->get();
    
    $transactions = [];
    
    foreach ($invoices as $invoice) {
        $transactions[] = [
            'date' => $invoice->issue_date,
            'academic_year' => $invoice->academicYear->name ?? 'N/A',
            'control_number' => $invoice->control_number ?? 'INVOICE',
            'receipt' => 'INVOICE',
            'fee_type' => ucwords(str_replace('_', ' ', $invoice->invoice_type)),
            'debit' => $invoice->total_amount,
            'credit' => 0,
            'description' => $invoice->description ?? $invoice->invoice_type
        ];
    }
    
    foreach ($payments as $payment) {
        $transactions[] = [
            'date' => $payment->created_at,
            'academic_year' => $payment->academicYear->name ?? 'N/A',
            'control_number' => $payment->control_number ?? 'N/A',
            'receipt' => 'PAYMENT',
            'fee_type' => $payment->payable->invoice_type ?? 'Payment',
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Payment received'
        ];
    }
    
    $transactions = collect($transactions)->sortBy('date')->values();
    
    $totalDebit = $transactions->sum('debit');
    $totalCredit = $transactions->sum('credit');
    $closingBalance = $totalDebit - $totalCredit;
    
    return view('finance.payments-management.print-statement', compact(
        'student',
        'transactions',
        'totalDebit',
        'totalCredit',
        'closingBalance'
    ));
}

/**
 * Export eligible students to CSV
 */
public function exportEligibleStudents(Request $request)
{
    $level = $request->get('level');
    $semester = $request->get('semester');
    
    $currentAcademicYear = AcademicYear::where('is_active', true)->first();
    $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
    
    $query = Student::with(['user', 'programme'])
        ->where('status', 'active');
    
    if ($level) {
        $query->where('current_level', $level);
    }
    
    if ($semester) {
        $query->where('current_semester', $semester);
    }
    
    $students = $query->get();
    
    $eligible = [];
    
    foreach ($students as $student) {
        $requiredFee = $this->getRequiredFeeForLevelSemester(
            $student->programme_id, 
            $student->current_level, 
            $student->current_semester, 
            $academicYearId
        );
        
        $totalPaid = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->sum('amount');
        
        $balance = $requiredFee - $totalPaid;
        
        if ($balance <= 0) {
            $eligible[] = [
                'name' => $student->user->first_name . ' ' . $student->user->last_name,
                'reg_no' => $student->registration_number,
                'programme' => $student->programme->name ?? 'N/A',
                'level' => $student->current_level,
                'semester' => $student->current_semester,
                'required' => $requiredFee,
                'paid' => $totalPaid
            ];
        }
    }
    
    // Generate CSV
    $filename = 'eligible-students-' . now()->format('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    $callback = function() use ($eligible) {
        $file = fopen('php://output', 'w');
        
        fputcsv($file, [
            'Name',
            'Registration No',
            'Programme',
            'Level',
            'Semester',
            'Required Amount',
            'Paid Amount'
        ]);
        
        foreach ($eligible as $student) {
            fputcsv($file, [
                $student['name'],
                $student['reg_no'],
                $student['programme'],
                'Year ' . $student['level'],
                'Semester ' . $student['semester'],
                $student['required'],
                $student['paid']
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}

/**
 * Payments by fee type - FIXED VERSION
 */
public function paymentsByFeeType($type, Request $request)
{
    // Map fee type to invoice types
    $invoiceTypes = match($type) {
        'tuition' => ['tuition', 'registration'],
        'hostel' => ['hostel'],
        'supplementary' => ['supplementary'],
        'repeat' => ['repeat_module'],
        'special' => ['special'],
        default => [$type]
    };
    
    $query = Payment::with([
            'student.user', 
            'student.programme', 
            'payable',
            'academicYear'
        ])
        ->where('status', 'completed')
        ->whereHas('payable', function($query) use ($invoiceTypes) {
            $query->whereIn('invoice_type', $invoiceTypes);
        });
    
    // 🔴 NEW: Filter by academic year
    if ($request->filled('academic_year_id')) {
        $query->where('academic_year_id', $request->academic_year_id);
    }
    
    // Filter by level
    if ($request->filled('level')) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('current_level', $request->level);
        });
    }
    
    // Filter by semester
    if ($request->filled('semester')) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('current_semester', $request->semester);
        });
    }
    
    // Filter by programme
    if ($request->filled('programme_id')) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('programme_id', $request->programme_id);
        });
    }
    
    // Filter by payment method
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }
    
    // Filter by date range
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }
    
    // Filter by amount range
    if ($request->filled('min_amount')) {
        $query->where('amount', '>=', $request->min_amount);
    }
    if ($request->filled('max_amount')) {
        $query->where('amount', '<=', $request->max_amount);
    }
    
    // Search by text
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->whereHas('student.user', function($u) use ($search) {
                $u->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%");
            })->orWhereHas('student', function($s) use ($search) {
                $s->where('registration_number', 'LIKE', "%{$search}%");
            })->orWhere('transaction_reference', 'LIKE', "%{$search}%");
        });
    }
    
    // Get payments with pagination
    $payments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
    
    // Calculate total amount for filtered results
    $totalAmount = $query->sum('amount');
    $totalCount = $payments->total();
    
    // Get filter options
    $levels = [1, 2, 3, 4];
    $semesters = [1, 2];
    $programmes = \App\Models\Programme::where('is_active', true)->get();
    $paymentMethods = Payment::distinct('payment_method')
        ->where('payment_method', '!=', null)
        ->pluck('payment_method');
    
    //  NEW: Get academic years
    $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
    
    return view('finance.payments-management.fee-type-payments', compact(
        'payments',
        'type',
        'totalAmount',
        'totalCount',
        'levels',
        'semesters',
        'programmes',
        'paymentMethods',
        'academicYears'  // Send to view
    ));
}



/**
 * Student Payment Info Search Page
 */
public function studentPaymentInfoSearch()
{
    return view('finance.student-payment-info.search');
}

/**
 * Student Payment Info Page - Shows ALL invoices and their payments
 */
public function studentPaymentInfo($studentId, Request $request)
{
    // Get student with relationships
    $student = Student::with(['user', 'programme'])->findOrFail($studentId);
    
    // Get selected academic year
    $selectedYearId = $request->get('academic_year_id');
    $selectedYear = null;
    
    if ($selectedYearId) {
        $selectedYear = AcademicYear::find($selectedYearId);
    } else {
        // Find the most recent year that has data for this student
        $latestInvoice = Invoice::where('student_id', $studentId)
            ->orderBy('academic_year_id', 'desc')
            ->first();
            
        $latestPayment = Payment::where('student_id', $studentId)
            ->where('status', 'completed')
            ->orderBy('academic_year_id', 'desc')
            ->first();
        
        $latestYearId = null;
        
        if ($latestInvoice && $latestPayment) {
            $latestYearId = max($latestInvoice->academic_year_id, $latestPayment->academic_year_id);
        } elseif ($latestInvoice) {
            $latestYearId = $latestInvoice->academic_year_id;
        } elseif ($latestPayment) {
            $latestYearId = $latestPayment->academic_year_id;
        }
        
        if ($latestYearId) {
            $selectedYear = AcademicYear::find($latestYearId);
            $selectedYearId = $selectedYear->id;
        } else {
            // Fallback to active year or first academic year
            $selectedYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
            $selectedYearId = $selectedYear?->id;
        }
    }
    
    // Get ALL invoices for this student in the selected year
    $invoices = Invoice::where('student_id', $studentId)
        ->where('academic_year_id', $selectedYearId)
        ->with(['academicYear'])
        ->orderBy('issue_date')
        ->get();
    
    // Get ALL payments for this student in the selected year
    $payments = Payment::where('student_id', $studentId)
        ->where('academic_year_id', $selectedYearId)
        ->where('status', 'completed')
        ->with(['academicYear', 'payable'])
        ->orderBy('created_at')
        ->get();
    
    // Create a combined collection of all transactions
    $allTransactions = collect();
    
    // Add invoices
    foreach ($invoices as $invoice) {
        $allTransactions->push([
            'date' => $invoice->issue_date,
            'timestamp' => $invoice->issue_date->timestamp,
            'type' => 'invoice',
            'data' => $invoice
        ]);
    }
    
    // Add payments
    foreach ($payments as $payment) {
        $allTransactions->push([
            'date' => $payment->created_at,
            'timestamp' => $payment->created_at->timestamp,
            'type' => 'payment',
            'data' => $payment
        ]);
    }
    
    // Sort by date
    $allTransactions = $allTransactions->sortBy('timestamp')->values();
    
    // Build transactions with running balance
    $transactions = [];
    $runningBalance = 0;
    
    foreach ($allTransactions as $item) {
        if ($item['type'] == 'invoice') {
            $invoice = $item['data'];
            $runningBalance += $invoice->total_amount;
            
            $transactions[] = [
                'date' => $invoice->issue_date,
                'academic_year' => $invoice->academicYear->name ?? 'N/A',
                'control_number' => $invoice->control_number ?? 'INVOICE',
                'receipt' => 'INVOICE',
                'fee_type' => $this->formatFeeType($invoice, $student),
                'debit' => $invoice->total_amount,
                'installment' => $invoice->total_amount,
                'credit' => 0,
                'balance' => $runningBalance,
            ];
        } else {
            $payment = $item['data'];
            $runningBalance -= $payment->amount;
            
            $transactions[] = [
                'date' => $payment->created_at,
                'academic_year' => $payment->academicYear->name ?? 'N/A',
                'control_number' => $payment->control_number ?? 'N/A',
                'receipt' => 'PAYMENT',
                'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
                'debit' => 0,
                'installment' => 0,
                'credit' => $payment->amount,
                'balance' => $runningBalance,
            ];
        }
    }
    
    // Calculate totals
    $totalDebit = collect($transactions)->sum('debit');
    $totalCredit = collect($transactions)->sum('credit');
    $closingBalance = $totalDebit - $totalCredit;
    
    // Get last login
    $lastLogin = $student->user->last_login_at 
        ? $student->user->last_login_at->format('d M, Y H:i:s') 
        : 'Never';
    
    // Get academic years with data for this student
    $yearsWithInvoices = Invoice::where('student_id', $studentId)
        ->select('academic_year_id')
        ->distinct()
        ->pluck('academic_year_id')
        ->toArray();
    
    $yearsWithPayments = Payment::where('student_id', $studentId)
        ->where('status', 'completed')
        ->select('academic_year_id')
        ->distinct()
        ->pluck('academic_year_id')
        ->toArray();
    
    $yearIds = array_unique(array_merge($yearsWithInvoices, $yearsWithPayments));
    $academicYears = AcademicYear::whereIn('id', $yearIds)
        ->orderBy('start_date', 'desc')
        ->get();
    
    return view('finance.student-payment-info.show', compact(
        'student',
        'transactions',
        'totalDebit',
        'totalCredit',
        'closingBalance',
        'academicYears',
        'selectedYear',
        'selectedYearId',
        'lastLogin'
    ));
}
/**
 * Process the search form submission (POST)
 */
public function studentPaymentInfoSearchPost(Request $request)
{
    $request->validate([
        'registration_number' => 'required|string'
    ]);

    $student = Student::where('registration_number', $request->registration_number)->first();

    if (!$student) {
        return redirect()->back()
            ->with('error', 'Student not found with registration number: ' . $request->registration_number)
            ->withInput();
    }

    // Redirect to payment info page
    return redirect()->route('finance.student-payment-info.show', $student->id);
}
/**
 * Format fee type like "THREE YEAR-ORDINARY DIPLOMA THIRD YEAR"
 */
private function formatFeeType($invoice, $student)
{
    $programme = $student->programme;
    $programmeName = $programme->name ?? 'PROGRAMME';
    
    // Extract programme name
    $programmeBase = preg_replace('/\s*\([^)]*\)/', '', $programmeName);
    $programmeBase = strtoupper(trim($programmeBase));
    
    // Get year in words
    $level = $student->current_level;
    $yearWord = match((int)$level) {
        1 => 'FIRST YEAR',
        2 => 'SECOND YEAR',
        3 => 'THIRD YEAR',
        4 => 'FOURTH YEAR',
        default => "YEAR {$level}"
    };
    
    // Get actual fee type from invoice
    $feeType = match($invoice->invoice_type) {
        'tuition' => 'TUITION FEE',
        'registration' => 'REGISTRATION FEE',
        'hostel' => 'HOSTEL FEE',
        'repeat_module' => 'REPEAT MODULE FEE',
        'supplementary' => 'SUPPLEMENTARY FEE',
        default => strtoupper(str_replace('_', ' ', $invoice->invoice_type))
    };
    
    return "{$programmeBase} {$yearWord} - {$feeType}";
}



/**
 * View All Transactions (showing hidden control numbers)
 */
public function studentAllTransactions($studentId, Request $request)
{
    $student = Student::with(['user', 'programme'])->findOrFail($studentId);
    
    $selectedYearId = $request->get('academic_year_id');
    
    // Get all invoices (including hidden ones)
    $invoices = Invoice::where('student_id', $studentId)
        ->with(['academicYear'])
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->where('academic_year_id', $selectedYearId);
        })
        ->orderBy('issue_date')
        ->get();
    
    // Get all payments
    $payments = Payment::where('student_id', $studentId)
        ->where('status', 'completed')
        ->with(['academicYear', 'payable'])
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->where('academic_year_id', $selectedYearId);
        })
        ->orderBy('created_at')
        ->get();
    
    // Combine all transactions (no hidden)
    $allTransactions = [];
    $runningBalance = 0;
    
    foreach ($invoices as $invoice) {
        $runningBalance += $invoice->total_amount;
        $allTransactions[] = [
            'date' => $invoice->issue_date,
            'academic_year' => $invoice->academicYear->name ?? 'N/A',
            'control_number' => $invoice->control_number,
            'receipt' => 'INVOICE',
            'fee_type' => $this->formatFeeTypeFull($invoice, $student),
            'debit' => $invoice->total_amount,
            'installment' => $invoice->total_amount,
            'credit' => 0,
            'balance' => $runningBalance
        ];
    }
    
    foreach ($payments as $payment) {
        $runningBalance -= $payment->amount;
        $allTransactions[] = [
            'date' => $payment->created_at,
            'academic_year' => $payment->academicYear->name ?? 'N/A',
            'control_number' => $payment->control_number,
            'receipt' => 'PAYMENT',
            'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
            'debit' => 0,
            'installment' => 0,
            'credit' => $payment->amount,
            'balance' => $runningBalance
        ];
    }
    
    usort($allTransactions, function($a, $b) {
        return $a['date']->timestamp <=> $b['date']->timestamp;
    });
    
    $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
    
    return view('finance.student-payment-info.all-transactions', compact(
        'student',
        'allTransactions',
        'academicYears',
        'selectedYearId'
    ));
}

/**
 * Format fee type like "THREE YEAR-ORDINARY DIPLOMA THIRD YEAR"
 */
private function formatFeeTypeFull($invoice, $student)
{
    $programme = $student->programme;
    $programmeName = $programme->name ?? 'PROGRAMME';
    
    // Extract programme base (remove brackets)
    $programmeBase = preg_replace('/\s*\([^)]*\)/', '', $programmeName);
    $programmeBase = strtoupper(trim($programmeBase));
    
    // Get year in words based on invoice type or student level
    $level = $student->current_level;
    if ($invoice->invoice_type == 'repeat_module' || $invoice->invoice_type == 'supplementary') {
        // For repeat/supplementary, use the level from invoice metadata if available
        $metadata = json_decode($invoice->metadata, true);
        $level = $metadata['student_level'] ?? $student->current_level;
    }
    
    $yearWord = match((int)$level) {
        1 => 'FIRST YEAR',
        2 => 'SECOND YEAR',
        3 => 'THIRD YEAR',
        4 => 'FOURTH YEAR',
        default => "YEAR {$level}"
    };
    
    // Get fee type
    $feeType = match($invoice->invoice_type) {
        'tuition' => 'TUITION FEE',
        'registration' => 'REGISTRATION FEE',
        'hostel' => 'HOSTEL FEE',
        'repeat_module' => 'REPEAT MODULE FEE',
        'supplementary' => 'SUPPLEMENTARY FEE',
        default => strtoupper(str_replace('_', ' ', $invoice->invoice_type))
    };
    
    return "{$programmeBase} {$yearWord} - {$feeType}";
}



/* Format fee type from payment
 */
private function formatFeeTypeFromPayment($payment, $student)
{
    if (!$payment->payable) {
        return 'PAYMENT';
    }
    return $this->formatFeeType($payment->payable, $student);
}

/**
 * Check if transaction should be hidden (for transcript invoice etc)
 */
private function isHiddenTransaction($invoice)
{
    // Hide transcript invoices or special cases
    return strpos($invoice->description ?? '', 'transcript') !== false;
}

/**
 * Print Student Payment Info
 */
public function printStudentPaymentInfo($studentId, Request $request)
{
    $student = Student::with(['user', 'programme'])->findOrFail($studentId);
    
    $selectedYearId = $request->get('academic_year_id');
    
    // Get transactions (similar logic as above)
    $invoices = Invoice::where('student_id', $studentId)
        ->with(['academicYear'])
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->where('academic_year_id', $selectedYearId);
        })
        ->orderBy('issue_date')
        ->get();
    
    $payments = Payment::where('student_id', $studentId)
        ->where('status', 'completed')
        ->with(['academicYear', 'payable'])
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->where('academic_year_id', $selectedYearId);
        })
        ->orderBy('created_at')
        ->get();
    
    $transactions = [];
    $runningBalance = 0;
    
    foreach ($invoices as $invoice) {
        $runningBalance += $invoice->total_amount;
        $transactions[] = [
            'date' => $invoice->issue_date,
            'academic_year' => $invoice->academicYear->name ?? 'N/A',
            'control_number' => $invoice->control_number ?? 'INVOICE',
            'receipt' => 'INVOICE',
            'fee_type' => $this->formatFeeTypeFull($invoice, $student),
            'debit' => $invoice->total_amount,
            'credit' => 0,
            'balance' => $runningBalance
        ];
    }
    
    foreach ($payments as $payment) {
        $runningBalance -= $payment->amount;
        $transactions[] = [
            'date' => $payment->created_at,
            'academic_year' => $payment->academicYear->name ?? 'N/A',
            'control_number' => $payment->control_number ?? 'N/A',
            'receipt' => 'PAYMENT',
            'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
            'debit' => 0,
            'credit' => $payment->amount,
            'balance' => $runningBalance
        ];
    }
    
    usort($transactions, function($a, $b) {
        return $a['date']->timestamp <=> $b['date']->timestamp;
    });
    
    $totalDebit = collect($transactions)->sum('debit');
    $totalCredit = collect($transactions)->sum('credit');
    $closingBalance = $totalDebit - $totalCredit;
    
    return view('finance.student-payment-info.print', compact(
        'student',
        'transactions',
        'totalDebit',
        'totalCredit',
        'closingBalance'
    ));
}

/**
 * Export to PDF
 */
public function exportStudentPaymentInfo($studentId, Request $request)
{
    $student = Student::with(['user', 'programme'])->findOrFail($studentId);
    
    $selectedYearId = $request->get('academic_year_id');
    
    // Same data as print
    $invoices = Invoice::where('student_id', $studentId)
        ->with(['academicYear'])
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->where('academic_year_id', $selectedYearId);
        })
        ->orderBy('issue_date')
        ->get();
    
    $payments = Payment::where('student_id', $studentId)
        ->where('status', 'completed')
        ->with(['academicYear', 'payable'])
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->where('academic_year_id', $selectedYearId);
        })
        ->orderBy('created_at')
        ->get();
    
    $transactions = [];
    $runningBalance = 0;
    
    foreach ($invoices as $invoice) {
        $runningBalance += $invoice->total_amount;
        $transactions[] = [
            'date' => $invoice->issue_date,
            'academic_year' => $invoice->academicYear->name ?? 'N/A',
            'control_number' => $invoice->control_number ?? 'INVOICE',
            'receipt' => 'INVOICE',
            'fee_type' => $this->formatFeeTypeFull($invoice, $student),
            'debit' => $invoice->total_amount,
            'credit' => 0,
            'balance' => $runningBalance
        ];
    }
    
    foreach ($payments as $payment) {
        $runningBalance -= $payment->amount;
        $transactions[] = [
            'date' => $payment->created_at,
            'academic_year' => $payment->academicYear->name ?? 'N/A',
            'control_number' => $payment->control_number ?? 'N/A',
            'receipt' => 'PAYMENT',
            'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
            'debit' => 0,
            'credit' => $payment->amount,
            'balance' => $runningBalance
        ];
    }
    
    usort($transactions, function($a, $b) {
        return $a['date']->timestamp <=> $b['date']->timestamp;
    });
    
    $totalDebit = collect($transactions)->sum('debit');
    $totalCredit = collect($transactions)->sum('credit');
    $closingBalance = $totalDebit - $totalCredit;
    
    $pdf = \PDF::loadView('finance.student-payment-info.pdf', compact(
        'student',
        'transactions',
        'totalDebit',
        'totalCredit',
        'closingBalance'
    ));
    
    $filename = 'payment-info-' . $student->registration_number . '.pdf';
    
    return $pdf->download($filename);
}

/**
 * Payment Filter - Filter students by total tuition payment amount
 */
public function paymentFilter(Request $request)
{
    // Get filter values
    $level = $request->get('level');
    $semester = $request->get('semester');
    $programmeId = $request->get('programme_id');
    $academicYearId = $request->get('academic_year_id');
    $minAmount = $request->get('min_amount', 0);
    
    // Get academic years for filter dropdown
    $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
    
    // Build query for students
    $query = Student::with(['user', 'programme'])
        ->where('status', 'active');
    
    if ($level) {
        $query->where('current_level', $level);
    }
    
    if ($semester) {
        $query->where('current_semester', $semester);
    }
    
    if ($programmeId) {
        $query->where('programme_id', $programmeId);
    }
    
    $students = $query->get();
    
    // Get all tuition invoice IDs for filtering payments
    $tuitionInvoiceIds = Invoice::where('invoice_type', 'tuition')
        ->pluck('id')
        ->toArray();
    
    // Calculate total tuition payments for each student
    $results = [];
    foreach ($students as $student) {
        // Build payment query for tuition only
        $paymentQuery = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereIn('payable_id', $tuitionInvoiceIds)
            ->where('payable_type', Invoice::class);
        
        // Filter by academic year if selected
        if ($academicYearId) {
            $paymentQuery->where('academic_year_id', $academicYearId);
        }
        
        $totalPaid = $paymentQuery->sum('amount');
        
        // Only include if meets minimum amount
        if ($totalPaid >= $minAmount) {
            // Get the academic year name for display
            $yearName = null;
            if ($academicYearId) {
                $year = AcademicYear::find($academicYearId);
                $yearName = $year ? $year->name : null;
            }
            
            $results[] = [
                'id' => $student->id,
                'name' => $student->user->first_name . ' ' . $student->user->last_name,
                'reg_no' => $student->registration_number,
                'programme' => $student->programme->name ?? 'N/A',
                'programme_id' => $student->programme_id,
                'level' => $student->current_level,
                'semester' => $student->current_semester,
                'academic_year' => $yearName,
                'total_paid' => $totalPaid,
            ];
        }
    }
    
    // Sort by total_paid descending
    usort($results, fn($a, $b) => $b['total_paid'] <=> $a['total_paid']);
    
    // Paginate results
    $perPage = 20;
    $currentPage = $request->get('page', 1);
    $offset = ($currentPage - 1) * $perPage;
    $items = array_slice($results, $offset, $perPage);
    
    $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
        $items,
        count($results),
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );
    
    // Get filter options
    $levels = [1, 2, 3, 4];
    $semesters = [1, 2];
    $programmes = Programme::where('is_active', true)->get();
    
    // Calculate totals
    $totalStudents = count($results);
    $totalAmount = collect($results)->sum('total_paid');
    $averageAmount = $totalStudents > 0 ? round($totalAmount / $totalStudents) : 0;
    
    return view('finance.payment-filter.index', compact(
        'paginatedData',
        'levels',
        'semesters',
        'programmes',
        'academicYears',
        'totalStudents',
        'totalAmount',
        'averageAmount',
        'minAmount',
        'level',
        'semester',
        'programmeId',
        'academicYearId'
    ));
}
/**
 * Export payment filter results
 */
public function exportPaymentFilter(Request $request)
{
    $level = $request->get('level');
    $semester = $request->get('semester');
    $programmeId = $request->get('programme_id');
    $academicYearId = $request->get('academic_year_id');
    $minAmount = $request->get('min_amount', 0);
    
    $query = Student::with(['user', 'programme'])->where('status', 'active');
    
    if ($level) $query->where('current_level', $level);
    if ($semester) $query->where('current_semester', $semester);
    if ($programmeId) $query->where('programme_id', $programmeId);
    
    $students = $query->get();
    
    // Get tuition invoice IDs
    $tuitionInvoiceIds = Invoice::where('invoice_type', 'tuition')
        ->pluck('id')
        ->toArray();
    
    $data = [];
    foreach ($students as $student) {
        $paymentQuery = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereIn('payable_id', $tuitionInvoiceIds)
            ->where('payable_type', Invoice::class);
        
        if ($academicYearId) {
            $paymentQuery->where('academic_year_id', $academicYearId);
        }
        
        $totalPaid = $paymentQuery->sum('amount');
        
        if ($totalPaid >= $minAmount) {
            $data[] = [
                'name' => $student->user->first_name . ' ' . $student->user->last_name,
                'reg_no' => $student->registration_number,
                'programme' => $student->programme->name ?? 'N/A',
                'level' => 'Year ' . $student->current_level,
                'semester' => 'Sem ' . $student->current_semester,
                'total_paid' => $totalPaid,
            ];
        }
    }
    
    // Sort by amount descending
    usort($data, fn($a, $b) => $b['total_paid'] <=> $a['total_paid']);
    
    // Generate CSV
    $filename = 'tuition-payment-filter-' . date('Ymd') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    $callback = function() use ($data) {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Student Name', 'Reg No', 'Programme', 'Level', 'Semester', 'Total Tuition Paid (TZS)']);
        
        foreach ($data as $row) {
            fputcsv($file, [
                $row['name'],
                $row['reg_no'],
                $row['programme'],
                $row['level'],
                $row['semester'],
                $row['total_paid']
            ]);
        }
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}

/**
 * Student Payment Summary by Fee Type - Shows TOTAL payment per student
 * Works for: tuition, hostel, supplementary, repeat, special
 */
public function studentPaymentSummaryByType($type, Request $request)
{
    // Map fee type to invoice types (HUJUMUI ZOTE)
    $invoiceTypes = match($type) {
        'tuition' => ['tuition', 'registration'],
        'hostel' => ['hostel'],
        'supplementary' => ['supplementary'],
        'repeat' => ['repeat_module'],
        'special' => ['special'],
        default => [$type]
    };
    
    // Get filter values
    $level = $request->get('level');
    $semester = $request->get('semester');
    $programmeId = $request->get('programme_id');
    $academicYearId = $request->get('academic_year_id');
    $minAmount = $request->get('min_amount', 0);
    $search = $request->get('search');
    
    // Build student query
    $studentQuery = Student::with(['user', 'programme'])
        ->where('status', 'active');
    
    if ($level) {
        $studentQuery->where('current_level', $level);
    }
    
    if ($semester) {
        $studentQuery->where('current_semester', $semester);
    }
    
    if ($programmeId) {
        $studentQuery->where('programme_id', $programmeId);
    }
    
    if ($search) {
        $studentQuery->where(function($q) use ($search) {
            $q->whereHas('user', function($u) use ($search) {
                $u->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%");
            })->orWhere('registration_number', 'LIKE', "%{$search}%");
        });
    }
    
    $students = $studentQuery->get();
    
    // Get all invoice IDs of this type (e.g., all hostel invoices)
    $invoiceIds = Invoice::whereIn('invoice_type', $invoiceTypes)
        ->pluck('id')
        ->toArray();
    
    // Calculate totals per student
    $results = [];
    foreach ($students as $student) {
        $paymentQuery = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereIn('payable_id', $invoiceIds)
            ->where('payable_type', Invoice::class);
        
        if ($academicYearId) {
            $paymentQuery->where('academic_year_id', $academicYearId);
        }
        
        $totalPaid = $paymentQuery->sum('amount');
        $paymentCount = $paymentQuery->count();
        
        // Get last payment date
        $lastPayment = $paymentQuery->orderBy('created_at', 'desc')->first();
        
        // Get required fee for this fee type (if applicable)
        $requiredFee = $this->getRequiredFeeForFeeType($student, $type, $academicYearId);
        
        // Only include if meets minimum amount
        if ($totalPaid >= $minAmount) {
            $results[] = [
                'id' => $student->id,
                'name' => $student->user->first_name . ' ' . $student->user->last_name,
                'reg_no' => $student->registration_number,
                'programme' => $student->programme->name ?? 'N/A',
                'programme_id' => $student->programme_id,
                'level' => $student->current_level,
                'semester' => $student->current_semester,
                'total_paid' => $totalPaid,
                'payment_count' => $paymentCount,
                'last_payment' => $lastPayment ? $lastPayment->created_at->format('d/m/Y') : 'N/A',
                'required_fee' => $requiredFee,
                'balance' => $requiredFee - $totalPaid,
                'status' => $requiredFee > 0 ? ($totalPaid >= $requiredFee ? 'FULLY PAID' : 'PARTIAL') : 'N/A',
                'percentage' => $requiredFee > 0 ? round(($totalPaid / $requiredFee) * 100, 1) : 0,
            ];
        }
    }
    
    // Sort by total_paid descending
    usort($results, fn($a, $b) => $b['total_paid'] <=> $a['total_paid']);
    
    // Paginate results
    $perPage = 20;
    $currentPage = $request->get('page', 1);
    $offset = ($currentPage - 1) * $perPage;
    $items = array_slice($results, $offset, $perPage);
    
    $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
        $items,
        count($results),
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );
    
    // Get filter options
    $levels = [1, 2, 3, 4];
    $semesters = [1, 2];
    $programmes = Programme::where('is_active', true)->get();
    $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
    
    // Calculate totals
    $totalStudents = count($results);
    $totalAmount = collect($results)->sum('total_paid');
    $averageAmount = $totalStudents > 0 ? round($totalAmount / $totalStudents) : 0;
    $fullyPaidCount = collect($results)->filter(fn($s) => $s['status'] == 'FULLY PAID')->count();
    $partialCount = $totalStudents - $fullyPaidCount;
    
    return view('finance.payments-management.fee-type-student-summary', compact(
        'type',
        'paginatedData',
        'levels',
        'semesters',
        'programmes',
        'academicYears',
        'totalStudents',
        'totalAmount',
        'averageAmount',
        'fullyPaidCount',
        'partialCount',
        'minAmount',
        'level',
        'semester',
        'programmeId',
        'academicYearId',
        'search'
    ));
}

/**
 * Get required fee for specific fee type
 */
private function getRequiredFeeForFeeType($student, $type, $academicYearId)
{
    if (!$academicYearId) {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $academicYear?->id;
    }
    
    // Map fee type to database fields
    return match($type) {
        'hostel' => $this->getHostelFee($student, $academicYearId),
        'supplementary' => $this->getSupplementaryFee($student, $academicYearId),
        'repeat' => $this->getRepeatFee($student, $academicYearId),
        'special' => 0, // Special fees usually don't have fixed amount
        default => $this->getTuitionFee($student, $academicYearId)
    };
}

private function getHostelFee($student, $academicYearId)
{
    // Get from hostel_fees table (adjust according to your DB structure)
    $fee = DB::table('hostel_fees')
        ->where('programme_id', $student->programme_id)
        ->where('academic_year_id', $academicYearId)
        ->where('level', $student->current_level)
        ->first();
    
    return $fee ? $fee->amount : 0;
}

private function getSupplementaryFee($student, $academicYearId)
{
    $fee = DB::table('supplementary_fees')
        ->where('programme_id', $student->programme_id)
        ->where('academic_year_id', $academicYearId)
        ->where('level', $student->current_level)
        ->first();
    
    return $fee ? $fee->amount : 0;
}

private function getRepeatFee($student, $academicYearId)
{
    $fee = DB::table('repeat_fees')
        ->where('programme_id', $student->programme_id)
        ->where('academic_year_id', $academicYearId)
        ->where('level', $student->current_level)
        ->first();
    
    return $fee ? $fee->amount : 0;
}

private function getTuitionFee($student, $academicYearId)
{
    $fee = ProgrammeFee::where('programme_id', $student->programme_id)
        ->where('academic_year_id', $academicYearId)
        ->where('level', $student->current_level)
        ->first();
    
    if ($fee) {
        return $fee->registration_fee + $fee->semester_1_fee + $fee->semester_2_fee;
    }
    
    return 0;
}

}
<?php
// app/Http/Controllers/Finance/RefundController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Display refunds
     */
    public function index(Request $request)
    {
        $query = Refund::with(['student.user', 'invoice', 'payment', 'requestedBy']);

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('refund_method')) {
            $query->where('refund_method', $request->refund_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $refunds = $query->latest()->paginate(15);

        $summary = [
            'total_requested' => Refund::sum('amount'),
            'total_approved' => Refund::where('status', 'approved')->sum('amount'),
            'total_processed' => Refund::where('status', 'processed')->sum('amount'),
            'pending_count' => Refund::where('status', 'pending')->count()
        ];

        return view('finance.accounts-receivable.refunds.index', compact('refunds', 'summary'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $payments = Payment::with(['student.user', 'payable'])
            ->where('status', 'completed')
            ->whereDoesntHave('refunds')
            ->latest()
            ->limit(100)
            ->get();

        return view('finance.accounts-receivable.refunds.create', compact('payments'));
    }

    /**
     * Store refund request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|in:bank_transfer,mpesa,cash,cheque',
            'refund_reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'bank_name' => 'required_if:refund_method,bank_transfer|nullable|string|max:255',
            'bank_account' => 'required_if:refund_method,bank_transfer|nullable|string|max:255',
            'phone_number' => 'required_if:refund_method,mpesa|nullable|string|max:15',
            'cheque_number' => 'required_if:refund_method,cheque|nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::with('payable')->findOrFail($request->payment_id);

            // Calculate refundable amount
            $refundedAmount = Refund::where('payment_id', $payment->id)
                ->whereIn('status', ['approved', 'processed'])
                ->sum('amount');
            
            $availableForRefund = $payment->amount - $refundedAmount;

            if ($request->amount > $availableForRefund) {
                throw new \Exception("Refund amount cannot exceed available amount of " . number_format($availableForRefund, 2));
            }

            // Generate refund number
            $refundNumber = $this->generateRefundNumber();

            // Create refund
            $refund = Refund::create([
                'refund_number' => $refundNumber,
                'payment_id' => $payment->id,
                'invoice_id' => $payment->payable_id,
                'student_id' => $payment->student_id,
                'academic_year_id' => $payment->academic_year_id,
                'amount' => $request->amount,
                'refund_method' => $request->refund_method,
                'refund_reason' => $request->refund_reason,
                'description' => $request->description,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'phone_number' => $request->phone_number,
                'cheque_number' => $request->cheque_number,
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'metadata' => json_encode([
                    'payment_number' => $payment->payment_number,
                    'invoice_number' => $payment->payable->invoice_number,
                    'requested_at' => now()->toIso8601String()
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund requested successfully',
                'refund' => $refund
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund request failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve refund
     */
    public function approve(Request $request, $refundId)
    {
        try {
            if (!Auth::user()->can('approve-refunds')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $refund = Refund::where('status', 'pending')->findOrFail($refundId);

            DB::beginTransaction();

            $refund->status = 'approved';
            $refund->approved_by = Auth::id();
            $refund->approved_at = now();
            $refund->metadata = array_merge($refund->metadata ?? [], [
                'approval_notes' => $request->notes,
                'approved_at' => now()->toIso8601String()
            ]);
            $refund->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund approval failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function process(Request $request, $refundId)
    {
        try {
            if (!Auth::user()->can('process-refunds')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'transaction_reference' => 'required|string|max:255',
                'processed_notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $refund = Refund::where('status', 'approved')->findOrFail($refundId);

            DB::beginTransaction();

            // Process based on refund method
            switch ($refund->refund_method) {
                case 'mpesa':
                    // Initiate M-Pesa refund
                    $this->processMpesaRefund($refund);
                    break;
                    
                case 'bank_transfer':
                    // Process bank transfer
                    $this->processBankTransfer($refund);
                    break;
                    
                case 'cash':
                    // Process cash refund
                    $this->processCashRefund($refund);
                    break;
                    
                case 'cheque':
                    // Process cheque refund
                    $this->processChequeRefund($refund);
                    break;
            }

            $refund->status = 'processed';
            $refund->processed_by = Auth::id();
            $refund->processed_at = now();
            $refund->transaction_reference = $request->transaction_reference;
            $refund->metadata = array_merge($refund->metadata ?? [], [
                'processed_notes' => $request->processed_notes,
                'processed_at' => now()->toIso8601String()
            ]);
            $refund->save();

            // Update invoice balance if needed
            if ($refund->invoice) {
                $refund->invoice->balance += $refund->amount;
                $refund->invoice->paid_amount -= $refund->amount;
                $refund->invoice->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund processing failed', [
                'refund_id' => $refundId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject refund
     */
    public function reject(Request $request, $refundId)
    {
        try {
            if (!Auth::user()->can('reject-refunds')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $refund = Refund::where('status', 'pending')->findOrFail($refundId);

            DB::beginTransaction();

            $refund->status = 'rejected';
            $refund->rejection_reason = $request->rejection_reason;
            $refund->metadata = array_merge($refund->metadata ?? [], [
                'rejected_by' => Auth::id(),
                'rejected_at' => now()->toIso8601String()
            ]);
            $refund->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund rejection failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate refund number
     */
    protected function generateRefundNumber()
    {
        $prefix = 'REF';
        $year = date('Y');
        $month = date('m');
        
        $lastRefund = Refund::where('refund_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRefund) {
            $parts = explode('/', $lastRefund->refund_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "{$prefix}/{$year}/{$month}/{$sequence}";
    }

    /**
     * Process M-Pesa refund
     */
    protected function processMpesaRefund($refund)
    {
        // Implement M-Pesa refund logic
        // Call M-Pesa API
    }

    /**
     * Process bank transfer
     */
    protected function processBankTransfer($refund)
    {
        // Implement bank transfer logic
        // Generate payment file or call bank API
    }

    /**
     * Process cash refund
     */
    protected function processCashRefund($refund)
    {
        // Generate cash receipt
    }

    /**
     * Process cheque refund
     */
    protected function processChequeRefund($refund)
    {
        // Generate cheque
    }

    /**
     * Show refund details
     */
    public function show($id)
    {
        $refund = Refund::with([
            'student.user',
            'student.programme',
            'invoice',
            'payment',
            'requestedBy',
            'approvedBy',
            'processedBy'
        ])->findOrFail($id);

        return view('finance.accounts-receivable.refunds.show', compact('refund'));
    }
}
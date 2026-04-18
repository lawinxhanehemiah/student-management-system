<?php

namespace App\Http\Controllers\Finance\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\PaymentVoucher;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PaymentVoucherController extends Controller
{
    /**
     * Display a listing of payment vouchers
     */
    public function index(Request $request)
    {
        $query = PaymentVoucher::with(['supplier', 'supplierInvoice', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $vouchers = $query->orderBy('payment_date', 'desc')->paginate(15);

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('finance.accounts-payable.payment-vouchers.index', compact('vouchers', 'suppliers'));
    }

    /**
     * Show form for creating new payment voucher
     */
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = SupplierInvoice::with('supplier')
                ->whereIn('status', ['approved', 'partial_paid'])
                ->where('balance', '>', 0)
                ->find($invoiceId);
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $voucherNumber = $this->generateVoucherNumber();

        return view('finance.accounts-payable.payment-vouchers.create', compact(
            'suppliers', 'invoice', 'voucherNumber'
        ));
    }

    /**
     * Get invoices for supplier (AJAX)
     */
    public function getSupplierInvoices($supplierId)
    {
        $invoices = SupplierInvoice::where('supplier_id', $supplierId)
            ->whereIn('status', ['approved', 'partial_paid'])
            ->where('balance', '>', 0)
            ->orderBy('due_date')
            ->get(['id', 'invoice_number', 'supplier_invoice_number', 'total_amount', 'balance', 'due_date']);

        return response()->json($invoices);
    }

    /**
     * Store a newly created payment voucher
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_invoice_id' => 'nullable|exists:supplier_invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mpesa',
            'reference_number' => 'nullable|string|max:100',
            'bank_name' => 'required_if:payment_method,bank_transfer,cheque|nullable|string|max:255',
            'bank_account' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Validate invoice amount
            if ($request->supplier_invoice_id) {
                $invoice = SupplierInvoice::findOrFail($request->supplier_invoice_id);
                
                if ($request->amount > $invoice->balance) {
                    throw new \Exception("Payment amount cannot exceed invoice balance of " . number_format($invoice->balance, 2));
                }
            }

            // Create payment voucher
            $voucher = PaymentVoucher::create([
                'voucher_number' => $this->generateVoucherNumber(),
                'supplier_id' => $request->supplier_id,
                'supplier_invoice_id' => $request->supplier_invoice_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'description' => $request->description,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment voucher created successfully',
                'voucher' => $voucher,
                'redirect_url' => route('finance.accounts-payable.payment-vouchers.show', $voucher->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment voucher creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment voucher
     */
    public function show($id)
    {
        $voucher = PaymentVoucher::with([
            'supplier',
            'supplierInvoice',
            'creator',
            'approver'
        ])->findOrFail($id);

        return view('finance.accounts-payable.payment-vouchers.show', compact('voucher'));
    }

    /**
     * Submit for approval
     */
    public function submitForApproval($id)
    {
        try {
            $voucher = PaymentVoucher::findOrFail($id);

            if ($voucher->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft vouchers can be submitted for approval'
                ], 422);
            }

            $voucher->update([
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment voucher submitted for approval'
            ]);

        } catch (\Exception $e) {
            Log::error('Submit voucher failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit voucher'
            ], 500);
        }
    }

    /**
     * Approve payment voucher
     */
    public function approve($id)
    {
        try {
            $voucher = PaymentVoucher::findOrFail($id);

            if ($voucher->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending vouchers can be approved'
                ], 422);
            }

            DB::beginTransaction();

            // Update voucher
            $voucher->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            // If linked to invoice, update invoice
            if ($voucher->supplier_invoice_id) {
                $invoice = SupplierInvoice::find($voucher->supplier_invoice_id);
                $newPaid = $invoice->paid_amount + $voucher->amount;
                $newBalance = $invoice->total_amount - $newPaid;

                $invoice->paid_amount = $newPaid;
                $invoice->balance = $newBalance;
                
                if ($newBalance <= 0) {
                    $invoice->status = 'paid';
                } else {
                    $invoice->status = 'partial_paid';
                }
                
                $invoice->save();
            }

            // Update supplier balance
            $supplier = $voucher->supplier;
            $supplier->updateBalance($voucher->amount, 'decrease');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment voucher approved'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve voucher failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve voucher'
            ], 500);
        }
    }

    /**
     * Reject payment voucher
     */
    public function reject(Request $request, $id)
    {
        try {
            $voucher = PaymentVoucher::findOrFail($id);

            if ($voucher->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending vouchers can be rejected'
                ], 422);
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

            $voucher->update([
                'status' => 'cancelled',
                'notes' => ($voucher->notes ? $voucher->notes . "\n" : '') . 
                          "Rejected: " . $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment voucher rejected'
            ]);

        } catch (\Exception $e) {
            Log::error('Reject voucher failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject voucher'
            ], 500);
        }
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($id)
    {
        try {
            $voucher = PaymentVoucher::findOrFail($id);

            if ($voucher->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved vouchers can be marked as paid'
                ], 422);
            }

            $voucher->update([
                'status' => 'paid'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment voucher marked as paid'
            ]);

        } catch (\Exception $e) {
            Log::error('Mark as paid failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as paid'
            ], 500);
        }
    }

    /**
     * Print payment voucher
     */
    public function print($id)
    {
        $voucher = PaymentVoucher::with(['supplier', 'supplierInvoice', 'creator'])->findOrFail($id);
        
        return view('finance.accounts-payable.payment-vouchers.print', compact('voucher'));
    }

    /**
     * Generate voucher number
     */
    private function generateVoucherNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastVoucher = PaymentVoucher::where('voucher_number', 'like', "PV/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastVoucher) {
            $parts = explode('/', $lastVoucher->voucher_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "PV/{$year}/{$month}/{$sequence}";
    }
}
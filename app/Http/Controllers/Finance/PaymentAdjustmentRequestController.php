<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PaymentAdjustmentRequest;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentAdjustmentRequestController extends Controller
{
    // Helper function kuangalia role
    private function hasRole($roleName)
    {
        return auth()->user()->hasRole($roleName);
    }

    /**
     * Show form to create a new adjustment request (Financial Controller)
     */
    public function create(Student $student)
    {
        if (!$this->hasRole('Financial_Controller') && !$this->hasRole('SuperAdmin')) {
            abort(403, 'Unauthorized: Financial Controller or Super Admin only.');
        }
        return view('finance.adjustments.create', compact('student'));
    }

    /**
     * Store a new request (Financial Controller)
     */
    public function store(Request $request, Student $student)
    {
        if (!$this->hasRole('Financial_Controller') && !$this->hasRole('SuperAdmin')) {
            abort(403);
        }

        $validated = $request->validate([
            'request_type' => 'required|in:manual_payment,correction,void,refund',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:1000',
            'invoice_id' => 'nullable|exists:invoices,id',
            'notes' => 'nullable|string',
        ]);

        $adjustment = PaymentAdjustmentRequest::create([
            'student_id' => $student->id,
            'created_by' => auth()->id(),
            'request_type' => $validated['request_type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'metadata' => ['invoice_id' => $request->invoice_id, 'notes' => $request->notes],
            'status' => 'pending',
        ]);

        return redirect()->route('finance.payment-adjustments.my-requests')
            ->with('success', 'Adjustment request submitted for Principal approval.');
    }

    /**
     * List all requests for Financial Controller (own requests)
     */
    public function myRequests()
    {
        if (!$this->hasRole('Financial_Controller') && !$this->hasRole('SuperAdmin')) {
            abort(403);
        }
        $requests = PaymentAdjustmentRequest::where('created_by', auth()->id())
            ->with('student.user')
            ->latest()
            ->paginate(20);
        return view('finance.adjustments.my_requests', compact('requests'));
    }

    /**
     * List all pending requests for Principal
     */
    public function pendingRequests()
    {
        if (!$this->hasRole('Principal') && !$this->hasRole('SuperAdmin')) {
            abort(403);
        }
        $requests = PaymentAdjustmentRequest::where('status', 'pending')
            ->with('student.user', 'creator')
            ->latest()
            ->paginate(20);
        return view('principal.adjustments.pending', compact('requests'));
    }

    /**
     * Show single request details
     */
    public function show($id)
    {
        $request = PaymentAdjustmentRequest::with('student.user', 'creator', 'approver')->findOrFail($id);
        $user = auth()->user();
        if (!$user->hasRole('Principal') && !$user->hasRole('SuperAdmin') && $request->created_by != $user->id) {
            abort(403);
        }
        return view('finance.adjustments.show', compact('request'));
    }

    /**
     * Approve request (Principal only)
     */
    public function approve($id)
    {
        if (!$this->hasRole('Principal') && !$this->hasRole('SuperAdmin')) {
            abort(403);
        }
        
        $adjustment = PaymentAdjustmentRequest::findOrFail($id);
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::transaction(function () use ($adjustment) {
            $this->executeAdjustment($adjustment);
            $adjustment->update([
                'status' => 'executed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('principal.payment-adjustments.pending')
            ->with('success', 'Adjustment approved and applied successfully.');
    }

    /**
     * Reject request (Principal only)
     */
    public function reject(Request $request, $id)
    {
        if (!$this->hasRole('Principal') && !$this->hasRole('SuperAdmin')) {
            abort(403);
        }
        
        $adjustment = PaymentAdjustmentRequest::findOrFail($id);
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Request already processed.');
        }

        $request->validate(['rejection_reason' => 'required|string']);

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'metadata' => array_merge($adjustment->metadata ?? [], ['rejection_reason' => $request->rejection_reason]),
        ]);

        return redirect()->route('principal.payment-adjustments.pending')
            ->with('success', 'Adjustment rejected.');
    }

    /**
     * Super Admin: view all requests
     */
    public function allRequests()
    {
        if (!$this->hasRole('SuperAdmin')) {
            abort(403);
        }
        $requests = PaymentAdjustmentRequest::with('student.user', 'creator', 'approver')
            ->latest()
            ->paginate(30);
        return view('superadmin.adjustments.index', compact('requests'));
    }

    /**
     * Super Admin: delete a request
     */
    public function destroy($id)
    {
        if (!$this->hasRole('SuperAdmin')) {
            abort(403);
        }
        $adjustment = PaymentAdjustmentRequest::findOrFail($id);
        if (in_array($adjustment->status, ['executed', 'approved'])) {
            return back()->with('error', 'Cannot delete an executed request.');
        }
        $adjustment->delete();
        return back()->with('success', 'Request deleted.');
    }

    /**
     * Execute the actual financial change
     */
   private function executeAdjustment(PaymentAdjustmentRequest $adjustment)
{
    $student = $adjustment->student;
    $amount = $adjustment->amount;
    $type = $adjustment->request_type;
    $metadata = $adjustment->metadata ?? [];

    if ($type == 'manual_payment') {
        // Tafuta invoice ya student yenye balance > 0
        $invoice = Invoice::where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$invoice) {
            $invoice = Invoice::where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$invoice) {
            \Log::error('No invoice found for student', ['student_id' => $student->id]);
            return;
        }

        // Get academic year from invoice
        $academicYear = $invoice->academicYear;
        
        // Tumia control number iliyopo kwenye invoice
        $controlNumber = $invoice->control_number;
        
        \Log::info('Found invoice for adjustment', [
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'control_number' => $controlNumber,
            'academic_year_id' => $academicYear->id,
            'academic_year_name' => $academicYear->name,
            'invoice_balance' => $invoice->balance,
            'amount_to_pay' => $amount
        ]);

        // Update invoice balance
        $oldBalance = $invoice->balance;
        $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $amount;
        $invoice->balance = $invoice->total_amount - $invoice->paid_amount;
        $invoice->payment_status = $invoice->balance <= 0 ? 'paid' : 'partial';
        $invoice->save();

        // Generate payment number
        $year = date('Y');
        $month = date('m');
        $count = Payment::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);
        $paymentNumber = "PAY/{$year}/{$month}/{$sequence}";

        // Create payment record with existing control number
        $payment = Payment::create([
            'payment_number' => $paymentNumber,
            'payable_type' => 'App\Models\Invoice',
            'payable_id' => $invoice->id,
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'amount' => $amount,
            'paid_amount' => $amount,
            'balance' => 0,
            'payment_method' => 'manual_adjustment',
            'transaction_type' => 'payment',
            'status' => 'completed',
            'control_number' => $controlNumber,  // ← Tumia control number ya invoice
            'created_by' => $adjustment->created_by,
            'approved_by' => $adjustment->approved_by,
            'metadata' => json_encode(['adjustment_request_id' => $adjustment->id, 'reason' => $adjustment->reason]),
            'attempts' => 0,
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Log::info('Manual payment created', [
            'payment_id' => $payment->id,
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'control_number' => $controlNumber,
            'academic_year_id' => $academicYear->id,
            'academic_year_name' => $academicYear->name,
            'old_balance' => $oldBalance,
            'new_balance' => $invoice->balance,
            'amount' => $amount
        ]);
    }
}

// PaymentAdjustmentRequestController.php

/**
 * Super Admin: Direct adjustment form (no approval needed)
 */
public function createDirect(Student $student)
{
    if (!auth()->user()->hasRole('SuperAdmin')) {
        abort(403);
    }
    return view('superadmin.adjustments.create-direct', compact('student'));
}

/**
 * Super Admin: Process adjustment directly (bypass approval)
 */
public function processDirect(Request $request, Student $student)
{
    if (!auth()->user()->hasRole('SuperAdmin')) {
        abort(403);
    }
    
    $validated = $request->validate([
        'request_type' => 'required|in:manual_payment,correction,void,refund',
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string|max:1000',
        'invoice_id' => 'nullable|exists:invoices,id',
        'notes' => 'nullable|string',
    ]);
    
    // Execute adjustment directly (bypass approval)
    $this->executeDirectAdjustment($student, $validated);
    
    return redirect()->route('finance.student-payment-info.show', $student->id)
        ->with('success', 'Payment adjustment processed directly by Super Admin.');
}

/**
 * Execute adjustment directly (no approval request)
 */
private function executeDirectAdjustment($student, $data)
{
    $amount = $data['amount'];
    $type = $data['request_type'];
    
    if ($type == 'manual_payment') {
        $invoice = Invoice::where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->first();
        
        if (!$invoice) {
            throw new \Exception('No invoice found for this student.');
        }
        
        // Update invoice
        $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $amount;
        $invoice->balance = $invoice->total_amount - $invoice->paid_amount;
        $invoice->payment_status = $invoice->balance <= 0 ? 'paid' : 'partial';
        $invoice->save();
        
        // Create payment record
        Payment::create([
            'payment_number' => 'ADMIN-' . time() . '-' . rand(1000, 9999),
            'payable_type' => 'App\Models\Invoice',
            'payable_id' => $invoice->id,
            'student_id' => $student->id,
            'academic_year_id' => $invoice->academic_year_id,
            'amount' => $amount,
            'paid_amount' => $amount,
            'balance' => 0,
            'payment_method' => 'super_admin_adjustment',
            'transaction_type' => 'payment',
            'status' => 'completed',
            'control_number' => $invoice->control_number,
            'created_by' => auth()->id(),
            'metadata' => json_encode(['reason' => $data['reason'], 'notes' => $data['notes'] ?? '']),
            'paid_at' => now(),
        ]);
    }
}
/**
 * Super Admin: Search student for direct adjustment
 */
public function searchStudent(Request $request)
{
    if (!auth()->user()->hasRole('SuperAdmin')) {
        abort(403);
    }
    
    $request->validate([
        'registration_number' => 'required|string'
    ]);
    
    $student = Student::where('registration_number', $request->registration_number)->first();
    
    if (!$student) {
        return redirect()->route('superadmin.payment-adjustments.index')
            ->with('error', 'Student not found with registration number: ' . $request->registration_number);
    }
    
    return redirect()->route('superadmin.payment-adjustments.create-direct', $student->id);
}
}
<?php
// app/Http/Controllers/SuperAdmin/InvoiceController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\ProgrammeFee;
use App\Models\FeeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['student.user', 'academicYear', 'items'])
            ->latest();

        // Filter by invoice number or student
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('control_number', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by academic year
        if ($request->has('academic_year_id') && $request->academic_year_id != '') {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('issue_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('issue_date', '<=', $request->end_date);
        }

        // Get counts
        $totalInvoices = Invoice::count();
        $totalAmount = Invoice::sum('total_amount');
        $totalPaid = Invoice::sum('paid_amount');
        $totalPending = Invoice::sum('balance');
        $overdueCount = Invoice::overdue()->count();

        $invoices = $query->paginate(15);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('superadmin.invoices.index', compact(
            'invoices', 
            'academicYears',
            'totalInvoices',
            'totalAmount',
            'totalPaid',
            'totalPending',
            'overdueCount'
        ));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $students = Student::with('user')
            ->where('status', 'active')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->first_name . ' ' . $student->user->last_name . ' (' . $student->registration_number . ')'
                ];
            });

        $academicYears = AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('superadmin.invoices.create', compact('students', 'academicYears'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'invoice_type' => 'required|in:tuition,registration,other',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'due_date' => 'required|date|after:today',
            'description' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function() use ($request) {
            $student = Student::findOrFail($request->student_id);
            
            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['amount'] * $item['quantity'];
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Generate control number (12-digit)
            $controlNumber = $this->generateControlNumber($student);

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'control_number' => $controlNumber,
                'control_number_status' => 'generated',
                'student_id' => $student->id,
                'user_id' => $student->user_id,
                'academic_year_id' => $request->academic_year_id,
                'invoice_type' => $request->invoice_type,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance' => $totalAmount,
                'issue_date' => now(),
                'due_date' => $request->due_date,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'description' => $request->description,
                'notes' => $request->notes,
                'metadata' => json_encode([
                    'generated_by' => 'manual',
                    'items_count' => count($request->items)
                ]),
                'created_by' => auth()->id()
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'quantity' => $item['quantity'],
                    'total' => $item['amount'] * $item['quantity'],
                    'net_amount' => $item['amount'] * $item['quantity'],
                    'type' => $item['type'] ?? 'other',
                    'category' => $item['category'] ?? 'fee',
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'is_optional' => 0,
                    'is_waived' => 0
                ]);
            }

            // Create fee transaction
            FeeTransaction::create([
                'student_id' => $student->id,
                'academic_year_id' => $request->academic_year_id,
                'control_number' => $controlNumber,
                'receipt_number' => 'INVOICE',
                'transaction_type' => 'INVOICE',
                'description' => $request->description ?? 'Manual invoice creation',
                'debit' => $totalAmount,
                'credit' => 0,
                'running_balance' => $totalAmount,
                'reference_id' => $invoice->id,
                'reference_type' => 'App\Models\Invoice',
                'transaction_date' => now(),
                'metadata' => json_encode([
                    'invoice_number' => $invoiceNumber,
                    'created_by' => auth()->user()->name ?? 'System'
                ])
            ]);

            Log::info('Invoice created manually', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
                'control_number' => $controlNumber,
                'created_by' => auth()->id()
            ]);
        });

        return redirect()->route('superadmin.invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Generate control number
     */
    private function generateControlNumber($student)
    {
        $year = date('y');
        $month = date('m');
        $day = date('d');
        
        $studentIdPadded = str_pad($student->id, 4, '0', STR_PAD_LEFT);
        $random = rand(1000, 9999);
        
        return $year . $month . $day . $studentIdPadded . substr($random, 0, 2);
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'student.user', 
            'academicYear', 
            'items',
            'transactions' => function($q) {
                $q->latest();
            }
        ])->findOrFail($id);

        return view('superadmin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit($id)
    {
        $invoice = Invoice::with(['student.user', 'items'])->findOrFail($id);

        // Don't allow editing paid invoices
        if ($invoice->payment_status == 'paid') {
            return redirect()->route('superadmin.invoices.show', $invoice->id)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('superadmin.invoices.edit', compact('invoice', 'academicYears'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        // Don't allow updating paid invoices
        if ($invoice->payment_status == 'paid') {
            return redirect()->route('superadmin.invoices.show', $invoice->id)
                ->with('error', 'Paid invoices cannot be updated.');
        }

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function() use ($request, $invoice) {
            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['amount'] * $item['quantity'];
            }

            // Update invoice
            $invoice->update([
                'academic_year_id' => $request->academic_year_id,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount - $invoice->paid_amount,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'notes' => $request->notes,
                'metadata' => json_encode([
                    'updated_at' => now(),
                    'updated_by' => auth()->user()->name ?? 'System'
                ])
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'quantity' => $item['quantity'],
                    'total' => $item['amount'] * $item['quantity'],
                    'net_amount' => $item['amount'] * $item['quantity'],
                    'type' => $item['type'] ?? 'other',
                    'category' => $item['category'] ?? 'fee',
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'is_optional' => 0,
                    'is_waived' => 0
                ]);
            }

            $invoice->updatePaymentStatus();
        });

        return redirect()->route('superadmin.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        // Don't allow deleting paid invoices
        if ($invoice->payment_status == 'paid') {
            return redirect()->route('superadmin.invoices.index')
                ->with('error', 'Paid invoices cannot be deleted.');
        }

        // Check if there are transactions
        if ($invoice->transactions()->count() > 0) {
            return redirect()->route('superadmin.invoices.index')
                ->with('error', 'Cannot delete invoice with payment transactions.');
        }

        DB::transaction(function() use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();

            Log::info('Invoice deleted', [
                'invoice_id' => $invoice->id,
                'deleted_by' => auth()->id()
            ]);
        });

        return redirect()->route('superadmin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Record a payment for an invoice
     */
    public function addPayment(Request $request, $id)
    {
        $invoice = Invoice::with('student')->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance,
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque,other',
            'payment_reference' => 'nullable|string|max:255',
            'control_number' => 'nullable|string|size:12',
            'receipt_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function() use ($request, $invoice) {
            
            // Get last transaction to calculate running balance
            $lastTransaction = FeeTransaction::where('student_id', $invoice->student_id)
                ->where('academic_year_id', $invoice->academic_year_id)
                ->orderBy('id', 'desc')
                ->first();
            
            $previousBalance = $lastTransaction ? $lastTransaction->running_balance : 0;
            $newBalance = $previousBalance - $request->amount;

            // Create fee transaction (PAYMENT ENTRY)
            $transaction = FeeTransaction::create([
                'student_id' => $invoice->student_id,
                'academic_year_id' => $invoice->academic_year_id,
                'control_number' => $request->control_number ?? $invoice->control_number,
                'receipt_number' => $request->receipt_number ?? ('RCT-' . time()),
                'transaction_type' => 'PAYMENT',
                'description' => $request->notes ?? "Payment for invoice " . $invoice->invoice_number,
                'debit' => 0,
                'credit' => $request->amount,
                'running_balance' => $newBalance,
                'reference_id' => $invoice->id,
                'reference_type' => 'App\Models\Invoice',
                'transaction_date' => $request->payment_date,
                'metadata' => json_encode([
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'recorded_by' => auth()->user()->name ?? 'System'
                ])
            ]);

            // Update invoice
            $invoice->addPayment($request->amount, $request->payment_method, $request->payment_reference, $request->notes);

            Log::info('Payment recorded for invoice', [
                'invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'new_balance' => $newBalance,
                'recorded_by' => auth()->id()
            ]);
        });

        return redirect()->route('superadmin.invoices.show', $invoice->id)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePdf($id)
    {
        $invoice = Invoice::with([
            'student.user', 
            'academicYear', 
            'items',
            'transactions'
        ])->findOrFail($id);

        $pdf = PDF::loadView('superadmin.invoices.pdf', compact('invoice'));
        
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Send invoice via email
     */
    public function sendEmail($id)
    {
        $invoice = Invoice::with('student.user')->findOrFail($id);
        
        // Send email logic here
        // You can use Laravel mail

        return redirect()->back()->with('success', 'Invoice sent via email successfully.');
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $count = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);
        
        return "INV/{$year}/{$month}/{$sequence}";
    }

    /**
     * Invoice statistics
     */
    public function statistics()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'total_amount' => Invoice::sum('total_amount'),
            'total_paid' => Invoice::sum('paid_amount'),
            'total_pending' => Invoice::sum('balance'),
            'overdue_count' => Invoice::overdue()->count(),
            'overdue_amount' => Invoice::overdue()->sum('balance'),
            
            'monthly_stats' => Invoice::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(total_amount) as amount'),
                DB::raw('SUM(paid_amount) as paid')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
        ];

        return response()->json($stats);
    }
}
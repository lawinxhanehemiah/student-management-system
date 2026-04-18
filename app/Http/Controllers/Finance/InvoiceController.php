<?php
// app/Http/Controllers/Finance/InvoiceController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\AcademicYear;
use App\Models\RepeatModuleFee;
use App\Models\SupplementaryFee;
use App\Models\HostelFee;
use App\Models\FeeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Show form for generating invoices
     */
    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();
            
        return view('finance.invoices.create', compact('academicYears'));
    }

    /**
     * Get student details by registration number
     */
    public function getStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $student = Student::with(['user', 'programme'])
            ->where('registration_number', $request->registration_number)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found with this registration number.'
            ]);
        }

        // Get fee amounts from database
        $repeatFee = $this->getRepeatModuleFee($student);
        $supplementaryFee = $this->getSupplementaryFee($student);
        $hostelFee = $this->getHostelFee($student);

        // Get academic year name
        $academicYear = AcademicYear::find($student->academic_year_id);
        $academicYearName = $academicYear ? $academicYear->name : 'N/A';

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->user->first_name . ' ' . $student->user->last_name,
                'registration_number' => $student->registration_number,
                'programme' => $student->programme->name ?? 'N/A',
                'programme_id' => $student->programme_id,
                'level' => $student->current_level,
                'semester' => $student->current_semester,
                'repeat_fee' => $repeatFee,
                'supplementary_fee' => $supplementaryFee,
                'hostel_fee' => $hostelFee,
                'academic_year_id' => $student->academic_year_id,
                'academic_year_name' => $academicYearName
            ]
        ]);
    }

    /**
     * Get repeat module fee
     */
    private function getRepeatModuleFee($student)
    {
        $fee = RepeatModuleFee::where('programme_id', $student->programme_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('level', $student->current_level)
            ->where('semester', $student->current_semester)
            ->where('is_active', true)
            ->first();

        return $fee ? (float) $fee->total_fee : 0;
    }

    /**
     * Get supplementary fee
     */
    private function getSupplementaryFee($student)
    {
        $fee = SupplementaryFee::where('programme_id', $student->programme_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('level', $student->current_level)
            ->where('semester', $student->current_semester)
            ->where('is_active', true)
            ->first();

        return $fee ? (float) $fee->total_fee : 0;
    }

    /**
     * Get hostel fee
     */
    private function getHostelFee($student)
    {
        $fee = HostelFee::where('programme_id', $student->programme_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('level', $student->current_level)
            ->where('semester', $student->current_semester)
            ->where('is_active', true)
            ->first();

        return $fee ? (float) $fee->total_fee : 0;
    }

    /**
     * Generate invoice
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'invoice_type' => 'required|in:repeat,supplementary,hostel',
            'description' => 'nullable|string|max:500',
            'due_date' => 'required|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            DB::beginTransaction();

            $student = Student::with('user', 'programme')->findOrFail($request->student_id);
            
            // Map invoice type to database value
            $dbInvoiceType = $this->mapInvoiceType($request->invoice_type);
            
            // Check for existing unpaid invoice
            $this->checkExistingInvoice($student->id, $request->academic_year_id, $dbInvoiceType);

            // Get fee amount from database
            $feeData = $this->getFeeAmount($student, $request->invoice_type, $request->academic_year_id);
            
            // Generate invoice number and control number
            $invoiceNumber = $this->generateUniqueInvoiceNumber($request->invoice_type);
            $controlNumber = $this->generateUniqueControlNumber($student);

            // Create invoice
            $invoice = $this->createInvoice($student, $request, $invoiceNumber, $controlNumber, $feeData, $dbInvoiceType);

            // Create invoice item
            $this->createInvoiceItem($invoice, $request->invoice_type, $feeData);

            // Update running balance
            $this->updateRunningBalance($student, $request->academic_year_id, $invoice, $feeData['amount'], $controlNumber);

            DB::commit();

            return $this->successResponse($invoice, $student, $feeData['amount']);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Map frontend invoice type to database value
     */
    private function mapInvoiceType($type)
    {
        $map = [
            'repeat' => 'repeat_module',
            'supplementary' => 'supplementary',
            'hostel' => 'hostel'
        ];
        return $map[$type] ?? $type;
    }

    /**
     * Check for existing unpaid invoice
     */
    private function checkExistingInvoice($studentId, $academicYearId, $invoiceType)
    {
        $existing = Invoice::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('invoice_type', $invoiceType)
            ->where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            throw new \Exception(
                "Student already has an unpaid invoice. " .
                "Invoice #: {$existing->invoice_number}, " .
                "Amount: " . number_format($existing->balance, 2)
            );
        }
    }

    /**
     * Get fee amount from database
     */
    private function getFeeAmount($student, $type, $academicYearId)
    {
        $model = $this->getFeeModel($type);
        $fee = $model::where('programme_id', $student->programme_id)
            ->where('academic_year_id', $academicYearId)
            ->where('level', $student->current_level)
            ->where('semester', $student->current_semester)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (!$fee) {
            throw new \Exception(ucfirst($type) . ' fee not configured for this student.');
        }

        if ($fee->total_fee <= 0) {
            throw new \Exception('Invalid fee amount. Fee must be greater than zero.');
        }

        return [
            'amount' => (float) $fee->total_fee,
            'source' => $type . '_fees',
            'id' => $fee->id
        ];
    }

    /**
     * Get fee model based on type
     */
    private function getFeeModel($type)
    {
        switch ($type) {
            case 'repeat':
                return RepeatModuleFee::class;
            case 'supplementary':
                return SupplementaryFee::class;
            case 'hostel':
                return HostelFee::class;
            default:
                throw new \Exception('Invalid fee type');
        }
    }

    /**
     * Create invoice
     */
    private function createInvoice($student, $request, $invoiceNumber, $controlNumber, $feeData, $dbInvoiceType)
    {
        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'control_number' => $controlNumber,
            'control_number_status' => 'generated',
            'control_number_expiry' => now()->addYear(),
            'student_id' => $student->id,
            'user_id' => $student->user_id,
            'academic_year_id' => $request->academic_year_id,
            'invoice_type' => $dbInvoiceType,
            'total_amount' => $feeData['amount'],
            'paid_amount' => 0,
            'balance' => $feeData['amount'],
            'issue_date' => now(),
            'due_date' => $request->due_date,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'description' => $request->description ?? $this->getDefaultDescription($request->invoice_type),
            'notes' => 'Generated by Finance Controller',
            'metadata' => json_encode([
                'generated_by' => 'finance_controller',
                'generated_by_user' => auth()->user()->name ?? 'System',
                'invoice_type' => $request->invoice_type,
                'student_level' => $student->current_level,
                'student_semester' => $student->current_semester,
                'fee_source' => $feeData['source'],
                'fee_id' => $feeData['id'],
                'amount_source' => 'database'
            ]),
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Create invoice item
     */
    private function createInvoiceItem($invoice, $type, $feeData)
    {
        $invoice->items()->create([
            'description' => $this->getItemDescription($type),
            'amount' => $feeData['amount'],
            'quantity' => 1,
            'total' => $feeData['amount'],
            'net_amount' => $feeData['amount'],
            'type' => $type,
            'category' => 'fee',
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'is_optional' => 0,
            'is_waived' => 0,
            'metadata' => json_encode([
                'fee_source' => $feeData['source'],
                'fee_id' => $feeData['id'],
                'configured_amount' => $feeData['amount']
            ])
        ]);
    }

    /**
     * Update running balance
     */
    private function updateRunningBalance($student, $academicYearId, $invoice, $amount, $controlNumber)
    {
        $lastTransaction = FeeTransaction::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();
        
        $previousBalance = $lastTransaction ? $lastTransaction->running_balance : 0;
        $newBalance = $previousBalance + $amount;

        FeeTransaction::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYearId,
            'control_number' => $controlNumber,
            'receipt_number' => 'INVOICE',
            'transaction_type' => 'INVOICE',
            'description' => $this->getTransactionDescription($invoice->invoice_type, $invoice->invoice_number),
            'debit' => $amount,
            'credit' => 0,
            'running_balance' => $newBalance,
            'reference_id' => $invoice->id,
            'reference_type' => 'App\Models\Invoice',
            'transaction_date' => now(),
            'metadata' => json_encode([
                'invoice_number' => $invoice->invoice_number,
                'control_number' => $controlNumber,
                'generated_by' => auth()->user()->name ?? 'System',
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance
            ])
        ]);
    }

    /**
     * Generate unique invoice number
     */
    private function generateUniqueInvoiceNumber($type)
    {
        $prefix = $this->getInvoicePrefix($type);
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "{$prefix}/{$year}/{$month}/{$sequence}";
    }

    /**
     * Get invoice prefix
     */
    private function getInvoicePrefix($type)
    {
        $prefixes = [
            'repeat' => 'RPT',
            'supplementary' => 'SUP',
            'hostel' => 'HST'
        ];
        return $prefixes[$type] ?? strtoupper(substr($type, 0, 3));
    }

    /**
     * Generate unique control number
     */
    private function generateUniqueControlNumber($student)
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $year = date('y');
            $month = date('m');
            $day = date('d');
            
            $studentIdPadded = str_pad($student->id, 4, '0', STR_PAD_LEFT);
            $random = rand(1000, 9999);
            
            $controlNumber = $year . $month . $day . $studentIdPadded . substr($random, 0, 2);
            
            $exists = Invoice::where('control_number', $controlNumber)->exists();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                $controlNumber = $year . $month . $day . $studentIdPadded . time() % 10000;
                break;
            }
            
        } while ($exists);
        
        return $controlNumber;
    }

    /**
     * Get default description
     */
    private function getDefaultDescription($type)
    {
        $descriptions = [
            'repeat' => 'Repeat module examination fee',
            'supplementary' => 'Supplementary examination fee',
            'hostel' => 'Hostel accommodation fee'
        ];
        return $descriptions[$type] ?? ucfirst($type) . ' fee';
    }

    /**
     * Get item description
     */
    private function getItemDescription($type)
    {
        $descriptions = [
            'repeat' => 'Repeat Module Fee',
            'supplementary' => 'Supplementary Examination Fee',
            'hostel' => 'Hostel Accommodation Fee'
        ];
        return $descriptions[$type] ?? ucfirst($type) . ' Fee';
    }

    /**
     * Get transaction description
     */
    private function getTransactionDescription($type, $invoiceNumber)
    {
        $typeNames = [
            'repeat' => 'Repeat Module',
            'supplementary' => 'Supplementary',
            'hostel' => 'Hostel'
        ];
        $typeName = $typeNames[$type] ?? ucfirst($type);
        return "{$typeName} invoice generated - {$invoiceNumber}";
    }

    /**
     * Success response
     */
    private function successResponse($invoice, $student, $amount)
    {
        return response()->json([
            'success' => true,
            'message' => 'Invoice generated successfully',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'control_number' => $invoice->control_number,
                'amount' => number_format($amount, 2),
                'type' => $invoice->invoice_type,
                'issue_date' => $invoice->issue_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'student_name' => $student->user->first_name . ' ' . $student->user->last_name,
                'student_reg_no' => $student->registration_number
            ],
            'print_url' => route('finance.invoices.print', $invoice->id),
            'download_url' => route('finance.invoices.download', $invoice->id)
        ]);
    }

    /**
     * Error response
     */
    private function errorResponse($e)
    {
        Log::error('Failed to generate invoice: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }

    /**
     * Display invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'student.user', 
            'student.programme',
            'academicYear', 
            'items',
            'transactions'
        ])->findOrFail($id);

        return view('finance.invoices.show', compact('invoice'));
    }

    /**
     * Print invoice
     */
    public function print($id)
    {
        $invoice = Invoice::with([
            'student.user', 
            'student.programme',
            'academicYear', 
            'items'
        ])->findOrFail($id);

        return view('finance.invoices.print', compact('invoice'));
    }

    /**
     * Download invoice PDF
     */
    public function download($id)
    {
        $invoice = Invoice::with([
            'student.user', 
            'student.programme',
            'academicYear', 
            'items'
        ])->findOrFail($id);

        $pdf = PDF::loadView('finance.invoices.pdf', compact('invoice'));
        $filename = 'invoice-' . str_replace(['/', '\\'], '-', $invoice->invoice_number) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * List invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['student.user', 'academicYear'])
            ->whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])
            ->latest();

        if ($request->has('type') && $request->type != '') {
            $query->where('invoice_type', $request->type);
        }

        if ($request->has('reg_no') && $request->reg_no != '') {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('registration_number', 'like', '%' . $request->reg_no . '%');
            });
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'overdue') {
                $query->where('due_date', '<', now())
                    ->where('payment_status', '!=', 'paid')
                    ->where('balance', '>', 0);
            } else {
                $query->where('payment_status', $request->status);
            }
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $invoices = $query->paginate(15);

        $stats = [
            'total' => Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])->count(),
            'total_amount' => Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])->sum('total_amount'),
            'total_paid' => Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])->sum('paid_amount'),
            'total_balance' => Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])->sum('balance')
        ];

        return view('finance.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Verify fee configuration
     */
    public function verifyFeeConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'invoice_type' => 'required|in:repeat,supplementary,hostel'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $student = Student::find($request->student_id);
        $fee = null;
        
        switch ($request->invoice_type) {
            case 'repeat':
                $fee = RepeatModuleFee::where('programme_id', $student->programme_id)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('level', $student->current_level)
                    ->where('semester', $student->current_semester)
                    ->where('is_active', true)
                    ->first();
                break;
            case 'supplementary':
                $fee = SupplementaryFee::where('programme_id', $student->programme_id)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('level', $student->current_level)
                    ->where('semester', $student->current_semester)
                    ->where('is_active', true)
                    ->first();
                break;
            case 'hostel':
                $fee = HostelFee::where('programme_id', $student->programme_id)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('level', $student->current_level)
                    ->where('semester', $student->current_semester)
                    ->where('is_active', true)
                    ->first();
                break;
        }

        $dbInvoiceType = $this->mapInvoiceType($request->invoice_type);
        $existingUnpaid = Invoice::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('invoice_type', $dbInvoiceType)
            ->where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0)
            ->exists();

        return response()->json([
            'success' => true,
            'configured' => !is_null($fee),
            'amount' => $fee ? (float) $fee->total_fee : 0,
            'has_unpaid' => $existingUnpaid,
            'warning' => $existingUnpaid ? 'Student already has unpaid invoice of this type' : null
        ]);
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $today = Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])
            ->whereDate('created_at', today())
            ->count();

        $weekAmount = Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total_amount');

        $monthAmount = Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $pending = Invoice::whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])
            ->where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0)
            ->count();

        return response()->json([
            'success' => true,
            'today' => ['count' => $today],
            'week' => ['amount' => $weekAmount],
            'month' => ['amount' => $monthAmount],
            'pending' => ['count' => $pending]
        ]);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity()
    {
        $activities = Invoice::with('student.user')
            ->whereIn('invoice_type', ['repeat_module', 'supplementary', 'hostel'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_name' => $invoice->student->user->first_name . ' ' . $invoice->student->user->last_name,
                    'amount' => $invoice->total_amount,
                    'type' => $invoice->invoice_type,
                    'time_ago' => $invoice->created_at->diffForHumans()
                ];
            });

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }
}
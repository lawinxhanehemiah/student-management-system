<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AcademicYear;

class PaymentInfoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $selectedYearId = $request->get('academic_year_id');
        $showAll = $request->get('show_all', false);
        
        // Get all academic years that have invoices for this student
        $invoiceYears = Invoice::where('student_id', $student->id)
            ->select('academic_year_id')
            ->distinct()
            ->pluck('academic_year_id')
            ->toArray();
        
        // Also get years from payments
        $paymentYears = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->select('academic_year_id')
            ->distinct()
            ->pluck('academic_year_id')
            ->toArray();
        
        // Merge all years
        $yearIds = array_unique(array_merge($invoiceYears, $paymentYears));
        
        // If no years found, get current academic year
        if (empty($yearIds)) {
            $currentYear = AcademicYear::where('is_active', true)->first();
            if ($currentYear) {
                $yearIds = [$currentYear->id];
            }
        }
        
        // Get years with transactions
        $yearsWithTransactions = AcademicYear::whereIn('id', $yearIds)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($year) {
                return ['id' => $year->id, 'name' => $year->name];
            })
            ->toArray();
        
        // If no year selected and there are years, select the first one
        if (!$selectedYearId && !empty($yearsWithTransactions)) {
            $selectedYearId = $yearsWithTransactions[0]['id'];
        }
        
        $selectedYearName = null;
        if ($selectedYearId) {
            $selectedYear = AcademicYear::find($selectedYearId);
            $selectedYearName = $selectedYear ? $selectedYear->name : null;
        }
        
        // ============================================
        // GET INVOICES (IMPORTANT - hata kama hakuna payment)
        // ============================================
        $invoicesQuery = Invoice::where('student_id', $student->id);
        
        if ($selectedYearId && !$showAll) {
            $invoicesQuery->where('academic_year_id', $selectedYearId);
        }
        
        $invoices = $invoicesQuery->with('academicYear')
            ->orderBy('issue_date', 'asc')
            ->get();
        
        // ============================================
        // GET PAYMENTS
        // ============================================
        $paymentsQuery = Payment::where('student_id', $student->id)
            ->where('status', 'completed');
        
        if ($selectedYearId && !$showAll) {
            $paymentsQuery->where('academic_year_id', $selectedYearId);
        }
        
        $payments = $paymentsQuery->with('academicYear', 'payable')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // ============================================
        // BUILD TRANSACTIONS (Combine Invoices + Payments)
        // ============================================
        $transactions = [];
        $runningBalance = 0;
        
        // Add INVOICES first (as debit transactions)
        foreach ($invoices as $invoice) {
            $runningBalance += $invoice->total_amount;
            
            $transactions[] = [
                'date' => $invoice->issue_date,
                'academic_year' => $invoice->academicYear ? $invoice->academicYear->name : 'N/A',
                'control_number' => $invoice->control_number ?? 'INVOICE',
                'receipt' => 'INVOICE',
                'fee_type' => $this->formatFeeType($invoice, $student),
                'debit' => $invoice->total_amount,
                'installment' => $invoice->total_amount, // First installment
                'credit' => 0,
                'balance' => $runningBalance,
            ];
        }
        
        // Add PAYMENTS (as credit transactions)
        foreach ($payments as $payment) {
            $runningBalance -= $payment->amount;
            
            $transactions[] = [
                'date' => $payment->created_at,
                'academic_year' => $payment->academicYear ? $payment->academicYear->name : 'N/A',
                'control_number' => $payment->control_number ?? 'N/A',
                'receipt' => 'PAYMENT',
                'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
                'debit' => 0,
                'installment' => 0,
                'credit' => $payment->amount,
                'balance' => $runningBalance,
            ];
        }
        
        // Sort by date
        usort($transactions, function($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });
        
        // Calculate totals
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');
        $closingBalance = $totalDebit - $totalCredit;
        
        // Get last login
        $lastLogin = $user->last_login_at ? $user->last_login_at->format('d M, Y H:i:s') : 'First login';
        
        // Get current academic year
        $currentYear = AcademicYear::where('is_active', true)->first();
        $currentAcademicYear = $currentYear ? $currentYear->name : date('Y') . '/' . (date('Y') + 1);
        
        return view('student.payment-info', compact(
            'student',
            'transactions',
            'totalDebit',
            'totalCredit',
            'closingBalance',
            'yearsWithTransactions',
            'selectedYearId',
            'selectedYearName',
            'lastLogin',
            'currentAcademicYear'
        ));
    }
    
    /**
     * Format fee type from invoice
     */
    private function formatFeeType($invoice, $student)
    {
        // Get programme name
        $programme = $student->programme;
        $programmeName = $programme ? $programme->name : 'PROGRAMME';
        
        // Clean programme name (remove brackets)
        $programmeBase = strtoupper(preg_replace('/\s*\([^)]*\)/', '', $programmeName));
        $programmeBase = trim($programmeBase);
        
        // Get year of study based on current level
        $level = $student->current_level ?? 1;
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
            'supplementary' => 'SUPPLEMENTARY FEE',
            'repeat_module' => 'REPEAT MODULE FEE',
            default => strtoupper(str_replace('_', ' ', $invoice->invoice_type))
        };
        
        return "{$programmeBase} {$yearWord} - {$feeType}";
    }
    
    /**
     * Format fee type from payment
     */
    private function formatFeeTypeFromPayment($payment, $student)
    {
        if (!$payment->payable) {
            return 'PAYMENT';
        }
        return $this->formatFeeType($payment->payable, $student);
    }
    
    /**
     * Show all transactions (no year filter)
     */
    public function showAll(Request $request)
    {
        return $this->index($request);
    }
    
    /**
     * Print statement
     */
    public function printStatement(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $selectedYearId = $request->get('academic_year_id');
        
        // Get invoices
        $invoicesQuery = Invoice::where('student_id', $student->id);
        if ($selectedYearId) {
            $invoicesQuery->where('academic_year_id', $selectedYearId);
        }
        $invoices = $invoicesQuery->with('academicYear')->orderBy('issue_date')->get();
        
        // Get payments
        $paymentsQuery = Payment::where('student_id', $student->id)->where('status', 'completed');
        if ($selectedYearId) {
            $paymentsQuery->where('academic_year_id', $selectedYearId);
        }
        $payments = $paymentsQuery->with('academicYear')->orderBy('created_at')->get();
        
        // Build transactions
        $transactions = [];
        $runningBalance = 0;
        
        foreach ($invoices as $invoice) {
            $runningBalance += $invoice->total_amount;
            $transactions[] = [
                'date' => $invoice->issue_date,
                'academic_year' => $invoice->academicYear ? $invoice->academicYear->name : 'N/A',
                'control_number' => $invoice->control_number ?? 'INVOICE',
                'receipt' => 'INVOICE',
                'fee_type' => $this->formatFeeType($invoice, $student),
                'debit' => $invoice->total_amount,
                'installment' => $invoice->total_amount,
                'credit' => 0,
                'balance' => $runningBalance,
            ];
        }
        
        foreach ($payments as $payment) {
            $runningBalance -= $payment->amount;
            $transactions[] = [
                'date' => $payment->created_at,
                'academic_year' => $payment->academicYear ? $payment->academicYear->name : 'N/A',
                'control_number' => $payment->control_number ?? 'N/A',
                'receipt' => 'PAYMENT',
                'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
                'debit' => 0,
                'installment' => 0,
                'credit' => $payment->amount,
                'balance' => $runningBalance,
            ];
        }
        
        usort($transactions, function($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });
        
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');
        $closingBalance = $totalDebit - $totalCredit;
        
        return view('student.payment-info-print', compact(
            'student',
            'transactions',
            'totalDebit',
            'totalCredit',
            'closingBalance'
        ));
    }
    
    /**
     * Download PDF
     */
    public function downloadPDF(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $selectedYearId = $request->get('academic_year_id');
        
        // Get invoices
        $invoicesQuery = Invoice::where('student_id', $student->id);
        if ($selectedYearId) {
            $invoicesQuery->where('academic_year_id', $selectedYearId);
        }
        $invoices = $invoicesQuery->with('academicYear')->orderBy('issue_date')->get();
        
        // Get payments
        $paymentsQuery = Payment::where('student_id', $student->id)->where('status', 'completed');
        if ($selectedYearId) {
            $paymentsQuery->where('academic_year_id', $selectedYearId);
        }
        $payments = $paymentsQuery->with('academicYear')->orderBy('created_at')->get();
        
        // Build transactions
        $transactions = [];
        $runningBalance = 0;
        
        foreach ($invoices as $invoice) {
            $runningBalance += $invoice->total_amount;
            $transactions[] = [
                'date' => $invoice->issue_date,
                'academic_year' => $invoice->academicYear ? $invoice->academicYear->name : 'N/A',
                'control_number' => $invoice->control_number ?? 'INVOICE',
                'receipt' => 'INVOICE',
                'fee_type' => $this->formatFeeType($invoice, $student),
                'debit' => $invoice->total_amount,
                'installment' => $invoice->total_amount,
                'credit' => 0,
                'balance' => $runningBalance,
            ];
        }
        
        foreach ($payments as $payment) {
            $runningBalance -= $payment->amount;
            $transactions[] = [
                'date' => $payment->created_at,
                'academic_year' => $payment->academicYear ? $payment->academicYear->name : 'N/A',
                'control_number' => $payment->control_number ?? 'N/A',
                'receipt' => 'PAYMENT',
                'fee_type' => $this->formatFeeTypeFromPayment($payment, $student),
                'debit' => 0,
                'installment' => 0,
                'credit' => $payment->amount,
                'balance' => $runningBalance,
            ];
        }
        
        usort($transactions, function($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });
        
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');
        $closingBalance = $totalDebit - $totalCredit;
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.payment-info-pdf', compact(
            'student',
            'transactions',
            'totalDebit',
            'totalCredit',
            'closingBalance'
        ));
        
        return $pdf->download('payment-statement-' . $student->registration_number . '.pdf');
    }
}
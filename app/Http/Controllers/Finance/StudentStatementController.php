<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentStatementController extends Controller
{
    /**
     * Display student statement search page
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        return view('finance.student-statements.index', compact('academicYears'));
    }

    /**
     * Search for student by registration number
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string',
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student = Student::with(['user', 'programme'])
            ->where('registration_number', $request->registration_number)
            ->first();

        if (!$student) {
            return redirect()->back()
                ->with('error', 'Student not found with registration number: ' . $request->registration_number)
                ->withInput();
        }

        // Redirect to statement view
        return redirect()->route('finance.student-statements.show', [
            'studentId' => $student->id,
            'academic_year_id' => $request->academic_year_id
        ]);
    }

    /**
     * Display student statement
     */
    public function show($studentId, Request $request)
    {
        $student = Student::with([
            'user', 
            'programme',
            'academicYear'
        ])->findOrFail($studentId);

        $academicYearId = $request->get('academic_year_id');

        // Get invoices for the student
        $invoicesQuery = Invoice::where('student_id', $studentId)
            ->with(['academicYear', 'items']);

        if ($academicYearId) {
            $invoicesQuery->where('academic_year_id', $academicYearId);
        }

        $invoices = $invoicesQuery->orderBy('issue_date', 'desc')->get();

        // Get payments for the student
        $paymentsQuery = Payment::where('student_id', $studentId)
            ->with(['academicYear', 'gateway']);

        if ($academicYearId) {
            $paymentsQuery->where('academic_year_id', $academicYearId);
        }

        $payments = $paymentsQuery->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_invoiced' => $invoices->sum('total_amount'),
            'total_paid' => $payments->where('status', 'completed')->sum('amount'),
            'total_balance' => $invoices->sum('balance'),
            'invoice_count' => $invoices->count(),
            'payment_count' => $payments->where('status', 'completed')->count()
        ];

        // Get all academic years for filter
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('finance.student-statements.show', compact(
            'student',
            'invoices',
            'payments',
            'summary',
            'academicYears',
            'academicYearId'
        ));
    }

    /**
     * Print student statement
     */
    public function print($studentId, Request $request)
    {
        $student = Student::with([
            'user', 
            'programme',
            'academicYear'
        ])->findOrFail($studentId);

        $academicYearId = $request->get('academic_year_id');

        // Get invoices for the student
        $invoicesQuery = Invoice::where('student_id', $studentId)
            ->with(['academicYear']);

        if ($academicYearId) {
            $invoicesQuery->where('academic_year_id', $academicYearId);
        }

        $invoices = $invoicesQuery->orderBy('issue_date', 'desc')->get();

        // Get payments for the student
        $paymentsQuery = Payment::where('student_id', $studentId)
            ->with(['academicYear']);

        if ($academicYearId) {
            $paymentsQuery->where('academic_year_id', $academicYearId);
        }

        $payments = $paymentsQuery->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_invoiced' => $invoices->sum('total_amount'),
            'total_paid' => $payments->where('status', 'completed')->sum('amount'),
            'total_balance' => $invoices->sum('balance')
        ];

        return view('finance.student-statements.print', compact(
            'student',
            'invoices',
            'payments',
            'summary'
        ));
    }

    /**
     * Download student statement as PDF
     */
    public function download($studentId, Request $request)
    {
        $student = Student::with([
            'user', 
            'programme',
            'academicYear'
        ])->findOrFail($studentId);

        $academicYearId = $request->get('academic_year_id');

        // Get invoices for the student
        $invoices = Invoice::where('student_id', $studentId)
            ->with(['academicYear'])
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->orderBy('issue_date', 'desc')
            ->get();

        // Get payments for the student
        $payments = Payment::where('student_id', $studentId)
            ->with(['academicYear'])
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'total_invoiced' => $invoices->sum('total_amount'),
            'total_paid' => $payments->where('status', 'completed')->sum('amount'),
            'total_balance' => $invoices->sum('balance')
        ];

        $pdf = PDF::loadView('finance.student-statements.pdf', compact(
            'student',
            'invoices',
            'payments',
            'summary'
        ));

        $filename = 'statement-' . $student->registration_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Email student statement
     */
    public function email($studentId, Request $request)
    {
        $student = Student::with(['user'])->findOrFail($studentId);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate PDF
            $academicYearId = $request->academic_year_id;

            $invoices = Invoice::where('student_id', $studentId)
                ->when($academicYearId, function($query) use ($academicYearId) {
                    return $query->where('academic_year_id', $academicYearId);
                })
                ->get();

            $payments = Payment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->when($academicYearId, function($query) use ($academicYearId) {
                    return $query->where('academic_year_id', $academicYearId);
                })
                ->get();

            $summary = [
                'total_invoiced' => $invoices->sum('total_amount'),
                'total_paid' => $payments->sum('amount'),
                'total_balance' => $invoices->sum('balance')
            ];

            $pdf = PDF::loadView('finance.student-statements.pdf', compact(
                'student',
                'invoices',
                'payments',
                'summary'
            ));

            // Send email
            \Mail::send([], [], function($message) use ($student, $pdf, $request) {
                $message->to($request->email)
                    ->subject('Student Statement - ' . $student->registration_number)
                    ->attachData($pdf->output(), 'statement.pdf', [
                        'mime' => 'application/pdf',
                    ])
                    ->setBody('Please find attached your student statement.');
            });

            return response()->json([
                'success' => true,
                'message' => 'Statement emailed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to email statement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to email statement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student by registration number (API)
     */
    public function getByRegNo($registrationNumber)
    {
        $student = Student::with(['user', 'programme'])
            ->where('registration_number', $registrationNumber)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Get summary
        $totalInvoiced = Invoice::where('student_id', $student->id)->sum('total_amount');
        $totalPaid = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->sum('amount');
        $balance = $totalInvoiced - $totalPaid;

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->user->first_name . ' ' . $student->user->last_name,
                'registration_number' => $student->registration_number,
                'programme' => $student->programme->name ?? 'N/A',
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'balance' => $balance
            ]
        ]);
    }

    /**
     * Export multiple statements (bulk)
     */
    public function exportBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Implementation for bulk export (ZIP file with multiple PDFs)
        // This is a placeholder
        return response()->json([
            'success' => true,
            'message' => 'Bulk export feature coming soon'
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Application;
use App\Models\Programme;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\ProgrammeFee;
use App\Models\Invoice;
use App\Models\FeeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentRegistrationController extends Controller
{
    /**
     * Show registration form with tabs for different registration types
     */
    public function create()
    {
        $programmes = Programme::where('is_active', true)->get();
        $courses = Course::all();
        $academicYears = AcademicYear::where('is_active', true)
                            ->orWhere('status', 'active')
                            ->orderBy('start_date', 'desc')
                            ->get();
        
        return view('admission.students.register', compact('programmes', 'courses', 'academicYears'));
    }

    /**
     * AJAX: Get courses by programme
     */
    public function getCourses(Request $request)
    {
        $courses = Course::where('programme_id', $request->programme_id)
                        ->where('is_active', true)
                        ->get(['id', 'name', 'code']);
        
        return response()->json($courses);
    }

    /**
 * AJAX: Get applicant details by application ID
 */
public function getApplicant(Request $request)
{
    $request->validate([
        'application_id' => 'required|string'
    ]);

    $applicationId = $request->application_id;
    
    // Try to find by ID or application number
    $application = Application::where('id', $applicationId)
                    ->orWhere('application_number', 'LIKE', "%{$applicationId}%")
                    ->with([
                        'user', 
                        'academicYear',
                        'programme' // Relationship ya selected_program_id
                    ])
                    ->first();

    if (!$application) {
        return response()->json([
            'success' => false,
            'message' => 'Application not found'
        ], 404);
    }

    // Check if already registered as student
    $existingStudent = Student::where('user_id', $application->user_id)->first();
    if ($existingStudent) {
        return response()->json([
            'success' => false,
            'message' => 'This applicant is already registered as a student',
            'student' => [
                'registration_number' => $existingStudent->registration_number,
                'id' => $existingStudent->id
            ]
        ], 400);
    }

    // Check if application is approved
    $warning = null;
    if ($application->status !== 'approved') {
        $warning = 'Warning: This application is not approved. Current status: ' . $application->status;
    }

    // Get user data
    $user = $application->user;

    // Prepare applicant data - data zote zipo kwenye application table!
    $data = [
        'success' => true,
        'warning' => $warning,
        'application' => [
            'id' => $application->id,
            'application_number' => $application->application_number,
            
            // User data (from users table)
            'first_name' => $user->first_name ?? '',
            'middle_name' => $user->middle_name ?? '',
            'last_name' => $user->last_name ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? '',
            'gender' => $user->gender ?? null,
            
            // Programme data (from selected_program_id)
            'programme_id' => $application->selected_program_id,
            'programme_name' => optional($application->programme)->name ?? 'N/A',
            
            // Course - tunaweza kuacha empty kwa sasa
            'course_id' => null,
            'course_name' => 'N/A',
            
            // Application details - ALL FROM APPLICATIONS TABLE!
            'intake' => $application->intake,
            'study_mode' => $application->study_mode ?? 'full_time',
            'academic_year_id' => $application->academic_year_id,
            'academic_year_name' => optional($application->academicYear)->name,
            'status' => $application->status,
            'submitted_at' => $application->submitted_at,
            
            // Personal info (zipo kwenye users table)
            'date_of_birth' => null, // Hauko kwenye users table?
            'nationality' => 'Tanzanian',
            'national_id' => null,
            'region' => null,
            'district' => null,
            
            // Guardian info (hazipo kwenye table yoyote - zitajazwa manually)
            'guardian_name' => '',
            'guardian_phone' => '',
            'guardian_relationship' => '',
            'guardian_address' => '',
            'sponsorship_type' => 'Private',
            
            // Additional
            'year_of_study' => 1,
        ]
    ];

    return response()->json($data);
}
   /**
 * Register a new student (from application OR walk-in)
 */
public function store(Request $request)
{
    // ===========================================
    // VALIDATION RULES
    // ===========================================
    
    // Base rules for all registration types
    $rules = [
        'registration_type' => 'required|in:from_application,walk_in',
        // Student table fields
        'programme_id' => 'required|exists:programmes,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'current_level' => 'required|integer|min:1|max:6',
        'current_semester' => 'required|integer|in:1,2',
        'intake' => 'required|in:March,September',
        'study_mode' => 'required|in:full_time,part_time,distance',
        'guardian_name' => 'required|string|max:255',
        'guardian_phone' => 'required|string|max:255',
        'generate_invoice' => 'boolean',
    ];

    // Add user table fields for walk-in registration
    if ($request->registration_type === 'walk_in') {
        $userRules = [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'gender' => 'nullable|in:male,female,other',
        ];
        $rules = array_merge($rules, $userRules);
    }
    
    // Add application_id for application-based registration
    if ($request->registration_type === 'from_application') {
        $rules['application_id'] = 'required|exists:applications,id';
    }

    // Validate request
    $validated = $request->validate($rules);

    try {
        DB::beginTransaction();

        $user = null;
        $application = null;

        // ===========================================
        // GENERATE REGISTRATION NUMBER (ONCE!)
        // ===========================================
        $regNo = $this->generateRegistrationNumber($request->intake);
        
        Log::info('Generated registration number for new student', [
            'reg_no' => $regNo,
            'intake' => $request->intake
        ]);

        // ===========================================
        // CASE 1: REGISTRATION FROM APPLICATION
        // ===========================================
        if ($request->registration_type === 'from_application') {
            $application = Application::with('user')->findOrFail($request->application_id);

            // Check if already registered
            $existingStudent = Student::where('user_id', $application->user_id)->first();
            if ($existingStudent) {
                throw new \Exception('This applicant is already registered as a student');
            }

            // Get or create user
            $user = $application->user;
            if (!$user) {
                // Create user if doesn't exist
                $user = User::create([
                    'first_name' => $application->first_name ?? '',
                    'middle_name' => $application->middle_name ?? '',
                    'last_name' => $application->last_name ?? '',
                    'email' => $application->email ?? '',
                    'phone' => $application->phone_number ?? '',
                    'gender' => $application->gender ?? null,
                    'user_type' => 'student',
                    'registration_number' => $regNo, // SET HERE!
                    'password' => Hash::make($regNo), // USE SAME regNo
                    'must_change_password' => true,
                    'status' => 'active',
                ]);
                $user->assignRole('Student');
            } else {
                // Update existing user
                $user->update([
                    'user_type' => 'student',
                    'registration_number' => $regNo, // SET HERE!
                    'status' => 'active',
                ]);
                $user->syncRoles(['Student']);
            }

            // Update application status
            if ($application->status === 'approved') {
                $application->update([
                    'admission_status' => 'admitted',
                    'admission_date' => now(),
                    'admitted_by' => auth()->id()
                ]);
            }
        }

        // ===========================================
        // CASE 2: WALK-IN REGISTRATION (NO APPLICATION)
        // ===========================================
        else {
            // Create new user for walk-in student
            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'user_type' => 'student',
                'registration_number' => $regNo, // SET HERE!
                'password' => Hash::make($regNo), // USE SAME regNo
                'must_change_password' => true,
                'status' => 'active',
            ]);
            $user->assignRole('Student');
        }

        // ===========================================
        // CREATE STUDENT RECORD
        // ===========================================
        $studentData = [
            'user_id' => $user->id,
            'application_id' => $application->id ?? null,
            'registration_number' => $regNo, // USE SAME regNo
            'programme_id' => $request->programme_id,
            'study_mode' => $request->study_mode,
            'intake' => $request->intake,
            'current_level' => $request->current_level,
            'current_semester' => $request->current_semester,
            'academic_year_id' => $request->academic_year_id,
            'status' => 'active',
            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,
        ];

        $student = Student::create($studentData);

        Log::info('Student registered successfully', [
            'student_id' => $student->id,
            'user_id' => $user->id,
            'application_id' => $application->id ?? null,
            'registration_number' => $regNo,
            'type' => $request->registration_type,
        ]);

        // ===========================================
        // GENERATE INVOICE IF REQUESTED
        // ===========================================
        $invoice = null;
        if ($request->boolean('generate_invoice')) {
            try {
                $invoice = $this->generateAnnualTuitionInvoice(
                    $student, 
                    $request->academic_year_id, 
                    $request->current_level
                );
                
                Log::info('Invoice generated for new student', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_id' => $student->id
                ]);
            } catch (\Exception $e) {
                Log::warning('Invoice generation failed but student created', [
                    'error' => $e->getMessage(),
                    'student_id' => $student->id
                ]);
                session()->flash('warning', 'Student registered but invoice generation failed: ' . $e->getMessage());
            }
        }

        DB::commit();

        // ===========================================
        // PREPARE SUCCESS MESSAGE
        // ===========================================
        $message = "✓ Student registered successfully!\n\n";
        $message .= "📋 Registration Number: {$regNo}\n";
        $message .= "🔑 Password: {$regNo}\n";
        $message .= "⚠️ Student must change password on first login.";
        
        if ($invoice) {
            $message .= "\n\n💰 Invoice Generated: {$invoice->invoice_number}\n";
            $message .= "   Amount: TZS " . number_format($invoice->total_amount, 0);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admission.students.show', $student->id),
                'student' => [
                    'id' => $student->id,
                    'registration_number' => $regNo
                ]
            ]);
        }

        return redirect()->route('admission.students.show', $student->id)
            ->with('success', nl2br($message));

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Student registration failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->except('_token')
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Registration failed: ' . $e->getMessage());
    }
}
    /**
     * Show walk-in registration form
     */
    public function createWalkIn()
    {
        $programmes = Programme::where('is_active', true)->get();
        $courses = Course::all();
        $academicYears = AcademicYear::where('is_active', true)
                            ->orderBy('start_date', 'desc')
                            ->get();
        
        return view('admission.students.walkin', compact('programmes', 'courses', 'academicYears'));
    }

    /**
     * Show student details
     */
    public function show($id)
    {
        $student = Student::with(['user', 'programme', 'course', 'academicYear', 'application'])
                    ->findOrFail($id);
        
        $invoices = Invoice::where('student_id', $student->id)
                    ->with('items')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return view('admission.students.show', compact('student', 'invoices'));
    }

    /**
     * List all registered students
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'programme', 'course', 'academicYear'])
                    ->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('programme_id') && $request->programme_id) {
            $query->where('programme_id', $request->programme_id);
        }

        if ($request->has('course_id') && $request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('intake') && $request->intake) {
            $query->where('intake', $request->intake);
        }

        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $students = $query->paginate(15)->withQueryString();
        
        // Get data for filters
        $programmes = Programme::all();
        $courses = Course::all();
        $academicYears = AcademicYear::all();

        return view('admission.students.index', compact('students', 'programmes', 'courses', 'academicYears'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $student = Student::with('user')->findOrFail($id);
        $programmes = Programme::where('is_active', true)->get();
        $courses = Course::all();
        $academicYears = AcademicYear::all();
        
        return view('admission.students.edit', compact('student', 'programmes', 'courses', 'academicYears'));
    }

    /**
     * Update student
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        
        $request->validate([
            // Student table fields
            'programme_id' => 'required|exists:programmes,id',
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'current_level' => 'required|integer|min:1|max:6',
            'current_semester' => 'required|integer|in:1,2',
            'study_mode' => 'required|in:full_time,part_time,distance',
            'guardian_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:255',
            'status' => 'required|in:active,suspended,graduated,discontinued',
        ]);

        try {
            DB::beginTransaction();

            // Update student record
            $student->update([
                'programme_id' => $request->programme_id,
                'course_id' => $request->course_id,
                'academic_year_id' => $request->academic_year_id,
                'current_level' => $request->current_level,
                'current_semester' => $request->current_semester,
                'study_mode' => $request->study_mode,
                'guardian_name' => $request->guardian_name,
                'guardian_phone' => $request->guardian_phone,
                'status' => $request->status,
            ]);

            // Update user status to match student status (except graduated)
            if ($student->user) {
                $userStatus = $request->status;
                if ($request->status === 'graduated') {
                    $userStatus = 'active'; // Graduated students can still login
                }
                $student->user->update(['status' => $userStatus]);
            }

            DB::commit();

            return redirect()->route('admission.students.show', $student->id)
                ->with('success', 'Student updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student update failed', [
                'error' => $e->getMessage(),
                'student_id' => $id
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate registration number
     * Format: 02.[SEQUENCE].[INTAKE_CODE].[YEAR]
     */
    private function generateRegistrationNumber($intake = 'March')
    {
        $campus = '02';
        $year = date('Y');
        $intakeCode = $intake === 'September' ? '09' : '03';

        $count = Student::whereYear('created_at', $year)->count() + 1;
        $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

        $regNo = "{$campus}.{$sequence}.{$intakeCode}.{$year}";
        
        Log::info('Generated registration number', [
            'reg_no' => $regNo,
            'intake' => $intake,
            'year' => $year
        ]);
        
        return $regNo;
    }

    /**
     * Generate annual tuition invoice
     */
    private function generateAnnualTuitionInvoice($student, $academicYearId, $level)
    {
        $programmeFee = ProgrammeFee::where('programme_id', $student->programme_id)
            ->where('academic_year_id', $academicYearId)
            ->where('level', $level)
            ->where('is_active', true)
            ->first();

        if (!$programmeFee) {
            throw new \Exception('Programme fee not found for level ' . $level);
        }

        // Check existing invoice
        $existingInvoice = Invoice::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('invoice_type', 'tuition')
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();
        
        // Generate control number
        $controlNumber = $this->generateControlNumber($student);

        // Get programme
        $programme = Programme::find($student->programme_id);
        $feeDescription = $this->getProgrammeLevelDescription($programme, $level);

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'control_number' => $controlNumber,
            'control_number_status' => 'generated',
            'student_id' => $student->id,
            'user_id' => $student->user_id,
            'academic_year_id' => $academicYearId,
            'programme_fee_id' => $programmeFee->id,
            'invoice_type' => 'tuition',
            'total_amount' => $programmeFee->total_year_fee,
            'paid_amount' => 0,
            'balance' => $programmeFee->total_year_fee,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'description' => $feeDescription,
            'notes' => "Auto-generated upon student registration.",
            'metadata' => json_encode([
                'level' => $level,
                'programme' => $programme->name ?? 'N/A',
                'programme_id' => $student->programme_id,
                'course_id' => $student->course_id,
                'total_year_fee' => $programmeFee->total_year_fee,
                'control_number' => $controlNumber,
                'invoice_type' => 'annual_tuition'
            ]),
            'created_by' => auth()->id()
        ]);

        // Create invoice item
        $invoice->items()->create([
            'description' => $feeDescription,
            'amount' => $programmeFee->total_year_fee,
            'quantity' => 1,
            'total' => $programmeFee->total_year_fee,
            'net_amount' => $programmeFee->total_year_fee,
            'type' => 'tuition',
            'category' => 'fee',
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'is_optional' => 0,
            'is_waived' => 0
        ]);

        return $invoice;
    }

    /**
     * Generate invoice number
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
     * Generate control number
     */
    private function generateControlNumber($student)
    {
        $year = date('y');
        $month = date('m');
        $day = date('d');
        $studentIdPadded = str_pad($student->id, 4, '0', STR_PAD_LEFT);
        $random = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
        return $year . $month . $day . $studentIdPadded . $random;
    }

    /**
     * Get programme level description
     */
    private function getProgrammeLevelDescription($programme, $level)
    {
        $levelNames = [
            1 => 'FIRST YEAR',
            2 => 'SECOND YEAR',
            3 => 'THIRD YEAR',
            4 => 'FOURTH YEAR',
            5 => 'FIFTH YEAR',
            6 => 'SIXTH YEAR'
        ];
        $levelName = $levelNames[$level] ?? "YEAR {$level}";
        
        if ($programme) {
            return strtoupper($programme->name) . ' - ' . $levelName . ' TUITION FEE';
        }
        return "TUITION FEE - {$levelName}";
    }

    /**
     * View student invoices
     */
    public function invoices($id)
    {
        $student = Student::with('user')->findOrFail($id);
        $invoices = Invoice::where('student_id', $student->id)
                    ->with('items', 'academicYear')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        
        return view('admission.students.invoices', compact('student', 'invoices'));
    }

    /**
     * Generate new invoice for existing student
     */
    public function generateInvoice(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'level' => 'required|integer|min:1|max:6'
        ]);

        try {
            $invoice = $this->generateAnnualTuitionInvoice(
                $student, 
                $request->academic_year_id, 
                $request->level
            );

            return redirect()->route('admission.students.invoices', $student->id)
                ->with('success', "Invoice generated successfully! #{$invoice->invoice_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Invoice generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Record payment
     */
    public function recordPayment(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'control_number' => 'required|string',
            'receipt_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Get last transaction to calculate running balance
            $lastTransaction = FeeTransaction::where('student_id', $student->id)
                ->where('academic_year_id', $request->academic_year_id)
                ->orderBy('id', 'desc')
                ->first();
            
            $previousBalance = $lastTransaction ? $lastTransaction->running_balance : 0;
            $newBalance = $previousBalance - $request->amount;

            // Find invoice to update
            $invoice = Invoice::where('student_id', $student->id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('payment_status', '!=', 'paid')
                ->latest()
                ->first();
            
            if ($invoice) {
                $newPaidAmount = $invoice->paid_amount + $request->amount;
                $newBalanceInvoice = $invoice->total_amount - $newPaidAmount;
                
                $paymentStatus = 'partial';
                if ($newPaidAmount >= $invoice->total_amount) {
                    $paymentStatus = 'paid';
                } elseif ($newPaidAmount > 0) {
                    $paymentStatus = 'partial';
                }
                
                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'balance' => $newBalanceInvoice,
                    'payment_status' => $paymentStatus,
                    'status' => $paymentStatus === 'paid' ? 'paid' : 'pending'
                ]);
            }

            // Create fee transaction
            $transaction = FeeTransaction::create([
                'student_id' => $student->id,
                'academic_year_id' => $request->academic_year_id,
                'control_number' => $request->control_number,
                'receipt_number' => $request->receipt_number,
                'transaction_type' => 'PAYMENT',
                'description' => $request->description ?? 'Fee Payment',
                'debit' => 0,
                'credit' => $request->amount,
                'running_balance' => $newBalance,
                'reference_id' => $invoice ? $invoice->id : null,
                'reference_type' => $invoice ? 'App\Models\Invoice' : null,
                'transaction_date' => $request->payment_date,
                'metadata' => json_encode([
                    'recorded_by' => auth()->user()->name ?? 'System',
                    'recorded_by_id' => auth()->id(),
                ])
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Payment recorded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment recording failed', [
                'error' => $e->getMessage(),
                'student_id' => $id
            ]);

            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Student fee statement
     */
    public function studentStatement($id, $academicYearId = null)
    {
        $student = Student::with('user', 'programme')->findOrFail($id);
        
        $query = FeeTransaction::where('student_id', $id)
            ->with('academicYear');
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        $transactions = $query->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentAcademicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
        
        // Calculate summary
        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');
        $currentBalance = $transactions->isNotEmpty() ? $transactions->last()->running_balance : 0;
        
        // Get invoices for this student
        $invoices = Invoice::where('student_id', $id)
            ->when($academicYearId, function($q) use ($academicYearId) {
                return $q->where('academic_year_id', $academicYearId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admission.students.statement', compact(
            'student', 
            'transactions', 
            'academicYears', 
            'currentAcademicYear',
            'totalDebit',
            'totalCredit',
            'currentBalance',
            'invoices'
        ));
    }

    /**
     * Export students list
     */
    public function export($format)
    {
        $students = Student::with(['user', 'programme', 'course', 'academicYear'])
                    ->get();

        if ($format === 'csv') {
            $filename = 'students_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($students) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, [
                    'Reg No',
                    'Full Name',
                    'Email',
                    'Phone',
                    'Programme',
                    'Course',
                    'Intake',
                    'Study Mode',
                    'Current Level',
                    'Semester',
                    'Academic Year',
                    'Status',
                    'Guardian Name',
                    'Guardian Phone'
                ]);
                
                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->registration_number,
                        $student->user->first_name . ' ' . $student->user->middle_name . ' ' . $student->user->last_name,
                        $student->user->email ?? 'N/A',
                        $student->user->phone ?? 'N/A',
                        $student->programme->name ?? 'N/A',
                        $student->course->name ?? 'N/A',
                        $student->intake,
                        ucfirst(str_replace('_', ' ', $student->study_mode)),
                        'Year ' . $student->current_level,
                        'Semester ' . $student->current_semester,
                        $student->academicYear->name ?? 'N/A',
                        ucfirst($student->status),
                        $student->guardian_name,
                        $student->guardian_phone
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }

        return redirect()->back()->with('info', 'Export feature coming soon');
    }
}
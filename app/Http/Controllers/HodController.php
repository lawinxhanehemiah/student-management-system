<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Programme;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\Result;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class HodController extends Controller implements HasMiddleware
{
    /**
     * Define middleware for this controller
     */
    public static function middleware()
    {
        return [
            new Middleware('auth'),
            new Middleware('role:Head_of_Department'),
        ];
    }

    /**
     * Dashboard - Show statistics and overview
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $department = Department::find($user->department_id);
        
        if (!$department) {
            return view('hod.dashboard')->with([
                'error' => 'No department assigned to your account.',
                'stats' => $this->getEmptyStats(),
                'recentStudents' => collect([]),
                'user' => $user,
                'currentAcademicYear' => null,
                'department' => null
            ]);
        }
        
        // Find programme for this department
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            $programme = Programme::first();
        }
        
        // Share students with layout
        $this->shareStudentsWithLayout();
        
        // Get students for this programme
        $students = collect([]);
        if ($programme) {
            $students = Student::with('user')
                ->where('programme_id', $programme->id)
                ->get();
        }
        
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        
        // Calculate gender statistics
        $maleCount = 0;
        $femaleCount = 0;
        
        foreach ($students as $student) {
            if ($student->user && $student->user->gender == 'male') {
                $maleCount++;
            } elseif ($student->user && $student->user->gender == 'female') {
                $femaleCount++;
            }
        }
        
        $stats = [
            'total_students' => $students->count(),
            'active_students' => $students->where('status', 'active')->count(),
            'graduating' => $students->where('current_level', 4)->count(),
            'by_level' => [
                1 => $students->where('current_level', 1)->count(),
                2 => $students->where('current_level', 2)->count(),
                3 => $students->where('current_level', 3)->count(),
                4 => $students->where('current_level', 4)->count(),
            ],
            'by_gender' => [
                'male' => $maleCount,
                'female' => $femaleCount,
            ]
        ];
        
        $recentStudents = Student::with('user')
            ->where('programme_id', $programme ? $programme->id : null)
            ->latest()
            ->limit(10)
            ->get();
        
        return view('hod.dashboard', compact(
            'students', 
            'stats', 
            'user', 
            'department',
            'programme',
            'currentAcademicYear', 
            'recentStudents'
        ));
    }
    
    /**
     * List all students in department
     */
    public function students(Request $request)
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $query = Student::with('user')
            ->where('programme_id', $programme->id);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('registration_number', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by level
        if ($request->filled('level')) {
            $query->where('current_level', $request->level);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $students = $query->paginate(20);
        
        $levels = [1, 2, 3, 4];
        $statuses = ['active', 'graduated', 'inactive', 'suspended'];
        
        return view('hod.students', compact('students', 'levels', 'statuses', 'programme'));
    }
    
    /**
     * List active students only
     */
    public function activeStudents(Request $request)
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $query = Student::with('user')
            ->where('programme_id', $programme->id)
            ->where('status', 'active');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('registration_number', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by level
        if ($request->filled('level')) {
            $query->where('current_level', $request->level);
        }
        
        $students = $query->paginate(20);
        $levels = [1, 2, 3, 4];
        
        return view('hod.active-students', compact('students', 'levels', 'programme'));
    }
    
    /**
     * List deferred/inactive students
     */
    public function deferredStudents(Request $request)
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $query = Student::with('user')
            ->where('programme_id', $programme->id)
            ->where('status', 'inactive');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('registration_number', 'LIKE', "%{$search}%");
            });
        }
        
        $students = $query->paginate(20);
        
        return view('hod.deferred-students', compact('students', 'programme'));
    }
    
    /**
     * List alumni (graduated students)
     */
    public function alumni(Request $request)
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $query = Student::with('user')
            ->where('programme_id', $programme->id)
            ->where('status', 'graduated');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('registration_number', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by graduation year
        if ($request->filled('graduation_year')) {
            $query->whereYear('updated_at', $request->graduation_year);
        }
        
        $students = $query->paginate(20);
        
        // Get distinct graduation years for filter
        $graduationYears = Student::where('programme_id', $programme->id)
            ->where('status', 'graduated')
            ->selectRaw('YEAR(updated_at) as year')
            ->distinct()
            ->pluck('year')
            ->filter();
        
        return view('hod.alumni', compact('students', 'programme', 'graduationYears'));
    }
    
   /**
 * Show student profile with all details
 * 
 * @param int $id
 * @param Request|null $request
 */
public function studentProfile($id, Request $request = null)
{
    // If request is null, create a new request instance
    if ($request === null) {
        $request = request();
    }
    
    $this->shareStudentsWithLayout();
    
    $user = auth()->user();
    $department = Department::find($user->department_id);
    $programme = $this->getProgrammeByDepartment($department);
    
    if (!$programme) {
        return redirect()->route('hod.dashboard')->with('error', 'No programme found for your department');
    }
    
    $student = Student::with(['user', 'programme', 'academicYear'])
        ->where('programme_id', $programme->id)
        ->findOrFail($id);
    
    // Get current course registrations
    $currentRegistrations = CourseRegistration::with('course')
        ->where('student_id', $student->id)
        ->where('academic_year_id', $student->academic_year_id)
        ->where('semester', $student->current_semester)
        ->get();
    
    // Get academic year filter
    $academicYearId = $request->get('academic_year_id');
    
    // Get all academic years for filter dropdown
    $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
    
    // Get invoices for this student
    $invoicesQuery = Invoice::where('student_id', $student->id);
    if ($academicYearId) {
        $invoicesQuery->where('academic_year_id', $academicYearId);
    }
    $invoices = $invoicesQuery->with(['academicYear', 'items'])
        ->orderBy('issue_date', 'asc')
        ->get();
    
    // Get payments for this student
    $paymentsQuery = Payment::where('student_id', $student->id)
        ->where('status', 'completed');
    if ($academicYearId) {
        $paymentsQuery->where('academic_year_id', $academicYearId);
    }
    $payments = $paymentsQuery->with(['academicYear', 'payable'])
        ->orderBy('created_at', 'asc')
        ->get();
    
    // Calculate totals
    $totalInvoiced = $invoices->sum('total_amount');
    $totalPaid = $payments->sum('amount');
    $totalBalance = $invoices->sum('balance');
    
    // Build transactions array (like statement format)
    $transactions = [];
    $runningBalance = 0;
    
    // Add invoices as DEBIT transactions
    foreach ($invoices as $invoice) {
        $runningBalance += $invoice->total_amount;
        
        // Get fee type description
        $feeType = $this->formatFeeType($invoice, $student);
        
        $transactions[] = [
            'date' => $invoice->issue_date,
            'academic_year' => $invoice->academicYear->name ?? 'N/A',
            'control_number' => $invoice->control_number ?? 'INVOICE',
            'receipt' => 'INVOICE',
            'fee_type' => $feeType,
            'debit' => $invoice->total_amount,
            'installment' => $this->getInstallmentAmount($invoice, $student),
            'credit' => 0,
            'running_balance' => $runningBalance,
            'type' => 'invoice'
        ];
    }
    
    // Add payments as CREDIT transactions
    foreach ($payments as $payment) {
        $runningBalance -= $payment->amount;
        
        // Get fee type from associated invoice
        $feeType = 'PAYMENT';
        if ($payment->payable) {
            $feeType = $this->formatFeeType($payment->payable, $student);
        }
        
        $transactions[] = [
            'date' => $payment->created_at,
            'academic_year' => $payment->academicYear->name ?? 'N/A',
            'control_number' => $payment->control_number ?? 'N/A',
            'receipt' => 'PAYMENT',
            'fee_type' => $feeType,
            'debit' => 0,
            'installment' => 0,
            'credit' => $payment->amount,
            'running_balance' => $runningBalance,
            'type' => 'payment'
        ];
    }
    
    // Sort by date
    $transactions = collect($transactions)->sortBy('date')->values()->toArray();
    
    // Calculate totals
    $totalDebit = collect($transactions)->sum('debit');
    $totalCredit = collect($transactions)->sum('credit');
    $closingBalance = $totalDebit - $totalCredit;
    
    return view('hod.student-profile', compact(
        'student', 
        'currentRegistrations', 
        'invoices',
        'payments',
        'totalInvoiced',
        'totalPaid',
        'totalBalance',
        'academicYears',
        'transactions',
        'totalDebit',
        'totalCredit',
        'closingBalance'
    ));
}

/**
 * Format fee type like "THREE YEAR-ORDINARY DIPLOMA THIRD YEAR"
 */
private function formatFeeType($invoice, $student)
{
    $programme = $student->programme;
    $programmeName = $programme->name ?? 'PROGRAMME';
    
    // Extract programme base (remove brackets)
    $programmeBase = preg_replace('/\s*\([^)]*\)/', '', $programmeName);
    $programmeBase = strtoupper(trim($programmeBase));
    
    // Get year in words based on invoice type or student level
    $level = $student->current_level;
    if ($invoice->invoice_type == 'repeat_module' || $invoice->invoice_type == 'supplementary') {
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

/**
 * Get installment amount (half of total for tuition, full for others)
 */
private function getInstallmentAmount($invoice, $student)
{
    if ($invoice->invoice_type == 'tuition') {
        return $invoice->total_amount / 2;
    }
    return $invoice->total_amount;
}
    
    /**
     * Show academic history with grades
     */
    public function academicHistory($id)
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->route('hod.dashboard')->with('error', 'No programme found for your department');
        }
        
        $student = Student::with(['user', 'programme'])
            ->where('programme_id', $programme->id)
            ->findOrFail($id);
        
        // Get all course registrations with results
        $registrations = CourseRegistration::with(['course', 'results', 'academicYear'])
            ->where('student_id', $student->id)
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('semester', 'desc')
            ->get();
        
        // Calculate GPA per semester and CGPA
        $semesterResults = [];
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($registrations->groupBy(function($item) {
            return $item->academic_year_id . '-S' . $item->semester;
        }) as $key => $group) {
            $semesterPoints = 0;
            $semesterCredits = 0;
            
            foreach ($group as $reg) {
                if ($reg->results && $reg->results->grade_point) {
                    $creditHours = $reg->course->credit_hours ?? 3;
                    $semesterPoints += $reg->results->grade_point * $creditHours;
                    $semesterCredits += $creditHours;
                }
            }
            
            $gpa = $semesterCredits > 0 ? $semesterPoints / $semesterCredits : 0;
            
            $semesterResults[$key] = [
                'academic_year' => $group->first()->academicYear->name ?? 'N/A',
                'semester' => $group->first()->semester,
                'registrations' => $group,
                'gpa' => round($gpa, 2),
                'total_credits' => $semesterCredits,
                'total_points' => $semesterPoints
            ];
            
            $totalPoints += $semesterPoints;
            $totalCredits += $semesterCredits;
        }
        
        $cgpa = $totalCredits > 0 ? $totalPoints / $totalCredits : 0;
        
        return view('hod.academic-history', compact('student', 'registrations', 'semesterResults', 'cgpa'));
    }
    
    /**
 * Show course registration form
 */
public function registerCoursesForm($id)
{
    $this->shareStudentsWithLayout();
    
    $user = auth()->user();
    $department = Department::find($user->department_id);
    $programme = $this->getProgrammeByDepartment($department);
    
    if (!$programme) {
        return redirect()->route('hod.dashboard')->with('error', 'No programme found for your department');
    }
    
    $student = Student::with(['user', 'programme'])
        ->where('programme_id', $programme->id)
        ->findOrFail($id);
    
    // Get available courses for this programme, level, and semester
    $availableCourses = Course::where('programme_id', $programme->id)
        ->where('level', $student->current_level)
        ->where('semester', $student->current_semester)
        ->where('status', 'active')
        ->orderBy('code')
        ->get();
    
    // Get already registered courses for current semester
    $registeredCourseIds = CourseRegistration::where('student_id', $student->id)
        ->where('academic_year_id', $student->academic_year_id)
        ->where('semester', $student->current_semester)
        ->pluck('course_id')
        ->toArray();
    
    return view('hod.register-courses', compact('student', 'availableCourses', 'registeredCourseIds'));
}

/**
 * API: Search students by name or registration number
 */
public function apiSearchStudents(Request $request)
{
    $query = $request->get('query', '');
    
    if (strlen($query) < 2) {
        return response()->json([
            'success' => true,
            'students' => []
        ]);
    }
    
    $user = auth()->user();
    $department = Department::find($user->department_id);
    $programme = $this->getProgrammeByDepartment($department);
    
    if (!$programme) {
        return response()->json([
            'success' => false,
            'message' => 'No programme found'
        ]);
    }
    
    $students = Student::with('user', 'programme')
        ->where('programme_id', $programme->id)
        ->where(function($q) use ($query) {
            $q->where('registration_number', 'LIKE', "%{$query}%")
              ->orWhereHas('user', function($userQuery) use ($query) {
                  $userQuery->where('first_name', 'LIKE', "%{$query}%")
                            ->orWhere('last_name', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%");
              });
        })
        ->limit(10)
        ->get();
    
    $formattedStudents = $students->map(function($student) {
        return [
            'id' => $student->id,
            'registration_number' => $student->registration_number,
            'name' => ($student->user->first_name ?? '') . ' ' . ($student->user->last_name ?? ''),
            'current_level' => $student->current_level,
            'status' => $student->status,
            'programme_name' => $student->programme->name ?? 'N/A'
        ];
    });
    
    return response()->json([
        'success' => true,
        'students' => $formattedStudents
    ]);
}
    
    /**
     * Store course registrations
     */
    public function storeCourseRegistration(Request $request, $id)
    {
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $student = Student::where('programme_id', $programme->id)->findOrFail($id);
        
        $request->validate([
            'courses' => 'required|array',
            'courses.*' => 'exists:courses,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Delete existing registrations for this semester
            CourseRegistration::where('student_id', $student->id)
                ->where('academic_year_id', $student->academic_year_id)
                ->where('semester', $student->current_semester)
                ->delete();
            
            // Register new courses
            foreach ($request->courses as $courseId) {
                CourseRegistration::create([
                    'student_id' => $student->id,
                    'course_id' => $courseId,
                    'academic_year_id' => $student->academic_year_id,
                    'semester' => $student->current_semester,
                    'registration_date' => now(),
                    'status' => 'registered',
                    'registered_by' => auth()->id()
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('hod.students.profile', $student->id)
                ->with('success', 'Courses registered successfully for ' . $student->user->first_name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course registration failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to register courses: ' . $e->getMessage());
        }
    }
    
    /**
     * Show clearance status for a student
     */
    public function clearanceStatus($id)
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->route('hod.dashboard')->with('error', 'No programme found for your department');
        }
        
        $student = Student::with(['user', 'programme', 'invoices'])
            ->where('programme_id', $programme->id)
            ->findOrFail($id);
        
        // Calculate clearance items
        $clearanceItems = [];
        $allCleared = true;
        
        // 1. Academic Clearance
        $hasOutstandingCourses = CourseRegistration::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'failed')
            ->exists();
        
        $academicStatus = !$hasOutstandingCourses ? 'cleared' : 'pending';
        if ($academicStatus === 'pending') $allCleared = false;
        
        $clearanceItems[] = [
            'name' => 'Academic Clearance',
            'status' => $academicStatus,
            'icon' => 'book-open',
            'notes' => $hasOutstandingCourses ? 'Student has pending courses' : 'All courses completed',
            'date' => null
        ];
        
        // 2. Financial Clearance
        $totalBalance = $student->invoices()->sum('balance');
        $hasFinancialObligation = $totalBalance > 0;
        
        $financialStatus = !$hasFinancialObligation ? 'cleared' : 'pending';
        if ($financialStatus === 'pending') $allCleared = false;
        
        $clearanceItems[] = [
            'name' => 'Financial Clearance',
            'status' => $financialStatus,
            'icon' => 'dollar-sign',
            'notes' => $hasFinancialObligation ? 'Balance: TZS ' . number_format($totalBalance, 0) : 'No outstanding balance',
            'amount' => $totalBalance,
            'date' => null
        ];
        
        // 3. Library Clearance (placeholder - would integrate with library system)
        $clearanceItems[] = [
            'name' => 'Library Clearance',
            'status' => 'pending',
            'icon' => 'book',
            'notes' => 'Pending library check',
            'date' => null
        ];
        $allCleared = false;
        
        // 4. Department Clearance
        $clearanceItems[] = [
            'name' => 'Department Clearance',
            'status' => 'pending',
            'icon' => 'users',
            'notes' => 'Pending HOD approval',
            'date' => null
        ];
        $allCleared = false;
        
        // Calculate overall status
        $overallStatus = $allCleared ? 'cleared' : 'pending';
        
        return view('hod.clearance-status', compact('student', 'clearanceItems', 'overallStatus'));
    }
    
    /**
     * Update clearance status for a specific item
     */
    public function updateClearance(Request $request, $id, $item)
    {
        $user = auth()->user();
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $student = Student::where('programme_id', $programme->id)->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:cleared,pending',
            'notes' => 'nullable|string'
        ]);
        
        try {
            // Store clearance in a clearance_log table or update student record
            // This is a placeholder - you may want to create a clearances table
            
            Log::info('Clearance updated', [
                'student_id' => $student->id,
                'item' => $item,
                'status' => $request->status,
                'updated_by' => auth()->id()
            ]);
            
            $message = $request->status === 'cleared' 
                ? "$item clearance approved for {$student->user->first_name}"
                : "$item clearance marked as pending";
            
            return redirect()->route('hod.students.clearance', $student->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update clearance: ' . $e->getMessage());
        }
    }
    
    /**
     * Activate a deferred student
     */
    public function activateStudent($id)
    {
        try {
            $user = auth()->user();
            $department = Department::find($user->department_id);
            $programme = $this->getProgrammeByDepartment($department);
            
            if (!$programme) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No programme found for your department'
                    ], 404);
                }
                return redirect()->back()->with('error', 'No programme found for your department');
            }
            
            $student = Student::where('programme_id', $programme->id)->findOrFail($id);
            
            // Update student status to active
            $student->update([
                'status' => 'active'
            ]);
            
            // Also update user status if needed
            if ($student->user) {
                $student->user->update([
                    'status' => 'active'
                ]);
            }
            
            Log::info('Student activated', [
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'activated_by' => auth()->id()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student activated successfully'
                ]);
            }
            
            return redirect()->back()->with('success', 'Student activated successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to activate student', [
                'student_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to activate student: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to activate student: ' . $e->getMessage());
        }
    }
    
    /**
     * Show student details (legacy method)
     */
    public function studentDetails($id)
    {
        return $this->studentProfile($id);
    }
    
    /**
     * Helper: Get programme by department code
     */
    private function getProgrammeByDepartment($department)
    {
        if (!$department) {
            return null;
        }
        
        $programmeCode = null;
        switch ($department->code) {
            case 'PST':
                $programmeCode = 'PST';
                break;
            case 'CMT':
                $programmeCode = 'CMT';
                break;
            case 'BA':
                $programmeCode = 'BAT';
                break;
            case 'SW':
                $programmeCode = 'SWT';
                break;
            case 'CD':
                $programmeCode = 'CDT';
                break;
            default:
                return null;
        }
        
        return Programme::where('code', $programmeCode)->first();
    }
    
    /**
     * Get empty stats for dashboard
     */
    private function getEmptyStats()
    {
        return [
            'total_students' => 0,
            'active_students' => 0,
            'graduating' => 0,
            'by_level' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'by_gender' => ['male' => 0, 'female' => 0]
        ];
    }
    
    /**
     * Profile page for HOD
     */
    public function profile()
    {
        $this->shareStudentsWithLayout();
        
        $user = auth()->user();
        $department = Department::find($user->department_id);
        
        return view('hod.profile', compact('user', 'department'));
    }
    
    /**
     * Share students with the layout
     */
    protected function shareStudentsWithLayout()
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }
        
        $department = Department::find($user->department_id);
        $programme = $this->getProgrammeByDepartment($department);
        
        if ($programme) {
            $students = Student::with('user')
                ->where('programme_id', $programme->id)
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->limit(500)
                ->get();
                
            view()->share('allStudents', $students);
        } else {
            view()->share('allStudents', collect([]));
        }
    }
    
    /**
     * Export students
     */
    public function exportStudents(Request $request)
    {
        // Add your export logic here
        return redirect()->back()->with('info', 'Export feature coming soon');
    }
}
<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Programme;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\Student;
use App\Models\ProgrammeFee;
use App\Models\Invoice;
use App\Models\FeeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users with filters
     */
    public function index(Request $request)
    {
        $query = User::with('roles', 'department')->latest();
        
        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($request->has('role') && $request->role != '') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Apply user type filter
        if ($request->has('user_type') && $request->user_type != '') {
            $query->where('user_type', $request->user_type);
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Apply department filter
        if ($request->has('department_id') && $request->department_id != '') {
            $query->where('department_id', $request->department_id);
        }
        
        // Apply date range filter
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Apply programme filter (for students/applicants)
        if ($request->has('programme_id') && $request->programme_id != '') {
            $query->whereHas('applications', function($q) use ($request) {
                $q->where('programme_id', $request->programme_id);
            })->orWhereHas('student', function($q) use ($request) {
                $q->where('programme_id', $request->programme_id);
            });
        }
        
        // Sorting
        if ($request->has('sort') && $request->sort != '') {
            $order = $request->has('order') && in_array($request->order, ['asc', 'desc']) 
                    ? $request->order 
                    : 'asc';
            $query->orderBy($request->sort, $order);
        }
        
        // Get per page value
        $perPage = $request->has('per_page') ? $request->per_page : 15;
        
        $users = $query->paginate($perPage)->withQueryString();
        
        // Get counts for summary cards
        $totalUsers = User::count();
        $studentCount = User::role('Student')->count();
        $staffCount = User::whereHas('roles', function($q) {
            $q->whereNotIn('name', ['Student', 'Applicant']);
        })->count();
        $applicantCount = User::role('Applicant')->count();
        
        // Get data for filters
        $roles = Role::all();
        $programmes = Programme::all();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        return view('superadmin.users.index', compact(
            'users', 
            'totalUsers', 
            'studentCount', 
            'staffCount', 
            'applicantCount',
            'roles',
            'programmes',
            'departments'
        ));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $programmes = Programme::all();
        $courses = Course::all();
        $faculties = Faculty::all();
        
        // Get departments for HOD and staff
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        $academicYears = AcademicYear::where('is_active', true)
            ->orWhere('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('superadmin.users.create', compact(
            'roles', 
            'programmes', 
            'courses', 
            'faculties', 
            'academicYears',
            'departments'
        ));
    }

    /**
     * Store a newly created user in storage
     */
   public function store(Request $request)
{
    // =====================
    // BASE VALIDATION - COMMON FOR ALL
    // =====================
    $baseRules = [
        'first_name'  => 'required|string|max:255',
        'middle_name' => 'required|string|max:255',
        'last_name'   => 'required|string|max:255',
        'role'        => 'required|exists:roles,name',
    ];

    // Get role from request
    $role = $request->role;

    // Determine user type based on role
    $userType = match ($role) {
        'Student'   => 'student',
        'Applicant' => 'applicant',
        default     => 'staff',
    };

    // =====================
    // DEPARTMENT VALIDATION FOR HOD
    // =====================
    if ($role === 'Head_of_Department') {
        $request->validate([
            'department_id' => 'required|exists:departments,id'
        ], [
            'department_id.required' => 'Department is required for Head of Department'
        ]);
    }

    // =====================
    // CONDITIONAL VALIDATION BASED ON ROLE
    // =====================

    // Student specific validation
    if ($role === 'Student') {
        $rules = array_merge($baseRules, [
            'intake'            => 'required|in:March,September',
            'programme_id'      => 'required|exists:programmes,id',
            'study_mode'        => 'required|in:full_time,part_time,distance',
            'guardian_name'     => 'required|string|max:255',
            'guardian_phone'    => 'required|string|max:255',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'current_level'     => 'required|integer|min:1|max:6',
            'current_semester'  => 'required|integer|in:1,2',
            'email'             => 'nullable|email|unique:users,email',
        ]);
    }

    // Applicant specific validation
    elseif ($role === 'Applicant') {
        $rules = array_merge($baseRules, [
            'phone_number'       => 'required|string|max:255',
            'date_of_birth'      => 'required|date',
            'region'             => 'required|string|max:255',
            'district'           => 'required|string|max:255',
            'intake'             => 'required|in:March,September',
            'programme_id'       => 'required|exists:programmes,id',
            'course_id'          => 'required|exists:courses,id',
            'study_mode'         => 'required|in:full_time,part_time,distance',
            'email'              => 'required|email|unique:users,email',
            'nationality'        => 'nullable|string|max:255',
            'national_id'        => 'nullable|string|max:255',
            'entry_qualification'=> 'nullable|string|max:255',
            'secondary_school'   => 'nullable|string|max:255',
            'completion_year'    => 'nullable|integer',
            'faculty_id'         => 'nullable|exists:faculties,id',
            'academic_year_id'   => 'nullable|exists:academic_years,id',
            'year_of_study'      => 'nullable|integer',
            'guardian_name'      => 'nullable|string|max:255',
            'guardian_phone'     => 'nullable|string|max:255',
            'guardian_relationship'=> 'nullable|string|max:255',
            'guardian_address'   => 'nullable|string|max:255',
            'sponsorship_type'   => 'nullable|string|max:255',
        ]);
    }

    // Staff/Other roles validation
    else {
        $rules = array_merge($baseRules, [
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'department_id' => 'nullable|exists:departments,id',
        ]);
    }

    // Add common optional fields for all roles
    $rules['phone'] = 'nullable|string|max:20';
    $rules['gender'] = 'nullable|in:male,female,other';
    $rules['profile_photo'] = 'nullable|image|max:2048';
    $rules['status'] = 'sometimes|in:active,inactive,suspended';

    // =====================
    // RUN VALIDATION
    // =====================
    $validated = $request->validate($rules);

    // =====================
    // HANDLE PROFILE PHOTO UPLOAD
    // =====================
    $profilePhotoPath = null;
    if ($request->hasFile('profile_photo')) {
        // Store in 'public/profile_photos' directory
        $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
    }

    // =====================
    // CREDENTIAL LOGIC
    // =====================
    $regNo = null;
    $username = null;
    $email = null;

    if ($userType === 'student') {
        // Generate unique registration number (with retry)
        $regNo = $this->generateUniqueRegistrationNumber($request->intake ?? 'March');
        $username = $regNo;
        $password = $regNo;
        $mustChange = true;
        $email = $request->email;
    } elseif ($userType === 'staff') {
        $username = $request->email;
        $password = $request->email;
        $mustChange = true;
        $email = $request->email;
    } else { // applicant
        $username = $request->email;
        $password = 'password123';
        $mustChange = false;
        $email = $request->email;
    }

    // =====================
    // DATABASE TRANSACTION
    // =====================
    try {
        DB::beginTransaction();

        // --- CREATE USER ---
        $user = User::create([
            'first_name'           => $request->first_name,
            'middle_name'          => $request->middle_name,
            'last_name'            => $request->last_name,
            'email'                => $email,
            'registration_number'  => $regNo,
            'phone'                => $request->phone ?? $request->phone_number ?? null,
            'gender'               => $request->gender ?? null,
            'profile_photo'        => $profilePhotoPath, // stored path, not file object
            'password'             => Hash::make($password),
            'user_type'            => $userType,
            'must_change_password' => $mustChange,
            'status'               => $request->status ?? 'active',
            'department_id'        => $request->department_id ?? null,
        ]);

        $user->assignRole($role);

        // --- If this is Head of Department, update department record ---
        if ($role === 'Head_of_Department' && $request->department_id) {
            Department::where('id', $request->department_id)->update([
                'head_of_department' => $user->id,
                'updated_by' => auth()->id()
            ]);

            Log::info('Head of Department assigned', [
                'user_id' => $user->id,
                'department_id' => $request->department_id
            ]);
        }

        // --- STUDENT PROFILE ---
        if ($userType === 'student') {
            // Ensure registration number is set
            if (!$user->registration_number) {
                $user->update(['registration_number' => $regNo]);
            }

            $student = Student::create([
                'user_id' => $user->id,
                'application_id' => null,
                'registration_number' => $user->registration_number,
                'programme_id' => $request->programme_id,
                'study_mode' => $request->study_mode,
                'intake' => $request->intake,
                'current_level' => $request->current_level,
                'current_semester' => $request->current_semester,
                'academic_year_id' => $request->academic_year_id,
                'status' => $request->student_status ?? 'active',
                'guardian_name' => $request->guardian_name,
                'guardian_phone' => $request->guardian_phone,
            ]);

            Log::info('Student created successfully', [
                'student_id' => $student->id,
                'user_id' => $user->id,
                'registration_number' => $student->registration_number,
                'programme_id' => $student->programme_id,
            ]);

            // --- GENERATE ANNUAL TUITION INVOICE ---
            try {
                $invoice = $this->generateAnnualTuitionInvoice($student, $request->academic_year_id, $request->current_level);

                if ($invoice) {
                    Log::info('Invoice generated successfully', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'control_number' => $invoice->control_number ?? 'N/A'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Invoice generation failed', [
                    'error' => $e->getMessage(),
                    'student_id' => $student->id
                ]);
                session()->flash('warning', 'User created but invoice generation failed: ' . $e->getMessage());
            }
        }

        // --- APPLICANT / APPLICATION ---
        if ($userType === 'applicant') {
            $appNumber = $this->generateApplicationNumber();

            Application::create([
                'user_id'          => $user->id,
                'application_number'=> $appNumber,
                'programme_id'     => $request->programme_id,
                'course_id'        => $request->course_id,
                'faculty_id'       => $request->faculty_id,
                'academic_year_id' => $request->academic_year_id,
                'year_of_study'    => $request->year_of_study,
                'intake'           => $request->intake,
                'study_mode'       => $request->study_mode,
                'first_name'       => $request->first_name,
                'middle_name'      => $request->middle_name,
                'last_name'        => $request->last_name,
                'date_of_birth'    => $request->date_of_birth,
                'phone_number'     => $request->phone_number,
                'email'            => $request->email,
                'nationality'      => $request->nationality,
                'national_id'      => $request->national_id,
                'entry_qualification' => $request->entry_qualification,
                'secondary_school' => $request->secondary_school,
                'completion_year'  => $request->completion_year,
                'guardian_name'    => $request->guardian_name,
                'guardian_phone'   => $request->guardian_phone,
                'guardian_relationship'=> $request->guardian_relationship,
                'guardian_address' => $request->guardian_address,
                'sponsorship_type' => $request->sponsorship_type,
                'region'           => $request->region,
                'district'         => $request->district,
                'status'           => 'pending',
                'application_stage' => 'submitted',
            ]);
        }

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('User creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create user: ' . $e->getMessage());
    }

    // =====================
    // RETURN SUCCESS MESSAGE
    // =====================
    $successMessage = "✓ User created successfully!\n\n";

    if ($userType === 'student') {
        $successMessage .= "📋 REGISTRATION NUMBER: {$user->registration_number}\n";
        $successMessage .= "🔑 PASSWORD: {$user->registration_number}\n";
        $successMessage .= "⚠️ Student must change password on first login.";
    } elseif ($userType === 'staff') {
        $successMessage .= "📧 EMAIL: {$request->email}\n";
        $successMessage .= "🔑 PASSWORD: {$request->email}\n";
        $successMessage .= "⚠️ Staff must change password on first login.";

        if ($role === 'Head_of_Department' && $request->department_id) {
            $dept = Department::find($request->department_id);
            $successMessage .= "\n🏢 Department: " . ($dept->name ?? 'N/A');
        }
    } else {
        $successMessage .= "📧 EMAIL: {$request->email}\n";
        $successMessage .= "🔑 PASSWORD: password123\n";
    }

    return redirect()
        ->route('superadmin.users.index')
        ->with('success', nl2br($successMessage));
}

/**
 * Generate a unique registration number (with retry to avoid duplicates)
 */
private function generateUniqueRegistrationNumber($intake = 'March', $retry = 0)
{
    $campus = '02';
    $year   = date('Y');
    $intakeCode = $intake === 'September' ? '09' : '03';

    // Find the last registration number (including soft-deleted users)
    $lastRegNo = User::withTrashed()
        ->where('user_type', 'student')
        ->where('registration_number', 'like', "{$campus}.%.{$intakeCode}.{$year}")
        ->orderBy('registration_number', 'desc')
        ->value('registration_number');

    if ($lastRegNo) {
        $parts = explode('.', $lastRegNo);
        $lastSeq = (int)($parts[1] ?? 0);
        $sequence = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $sequence = '001';
    }

    $regNo = "{$campus}.{$sequence}.{$intakeCode}.{$year}";

    // Double-check uniqueness (including trashed)
    if (User::withTrashed()->where('registration_number', $regNo)->exists() && $retry < 5) {
        return $this->generateUniqueRegistrationNumber($intake, $retry + 1);
    }

    Log::info('Generated unique registration number', compact('regNo', 'intake', 'year'));
    return $regNo;
}
    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with(['roles', 'applications', 'student', 'student.invoices', 'student.invoices.items', 'department'])
            ->findOrFail($id);
        
        $invoices = collect();
        $transactions = collect();
        $feeSummary = null;
        
        if ($user->student) {
            $invoices = Invoice::where('student_id', $user->student->id)
                ->with('items', 'academicYear')
                ->orderBy('created_at', 'desc')
                ->get();
                
            $transactions = FeeTransaction::where('student_id', $user->student->id)
                ->with('academicYear')
                ->orderBy('transaction_date', 'desc')
                ->limit(20)
                ->get();
                
            // Calculate fee summary
            $totalInvoiced = $invoices->sum('total_amount');
            $totalPaid = $invoices->sum('paid_amount');
            $totalBalance = $invoices->sum('balance');
            
            $feeSummary = [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
                'invoice_count' => $invoices->count()
            ];
        }
        
        return view('superadmin.users.show', compact('user', 'invoices', 'transactions', 'feeSummary'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::with('student')->findOrFail($id);
        $roles = Role::all();
        $programmes = Programme::all();
        
        // Get departments for HOD and staff
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('superadmin.users.edit', compact(
            'user', 
            'roles', 
            'programmes', 
            'academicYears',
            'departments'
        ));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentRole = $user->roles->first()->name ?? null;
        $newRole = $request->role;

        $rules = [
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email' => "nullable|email|unique:users,email,{$id}",
            'role'  => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'status' => 'required|in:active,inactive,suspended',
            'profile_photo' => 'nullable|image|max:2048',
            'department_id' => 'nullable|exists:departments,id'
        ];

        // Department validation for Head of Department
        if ($newRole === 'Head_of_Department' && !$request->department_id) {
            return back()->withErrors([
                'department_id' => 'Department is required for Head of Department'
            ])->withInput();
        }

        // Add student-specific fields if user is a student
        if ($user->user_type === 'student' && $user->student) {
            $rules = array_merge($rules, [
                'programme_id' => 'sometimes|exists:programmes,id',
                'current_level' => 'sometimes|integer|min:1|max:6',
                'current_semester' => 'sometimes|integer|in:1,2',
                'study_mode' => 'sometimes|in:full_time,part_time,distance',
                'guardian_name' => 'nullable|string|max:255',
                'guardian_phone' => 'nullable|string|max:255',
            ]);
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Update user
            $userData = [
                'first_name'  => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name'   => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'status' => $request->status,
                'department_id' => $request->department_id,
            ];

            if ($request->hasFile('profile_photo')) {
                $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
                $userData['profile_photo'] = $profilePhotoPath;
            }

            $user->update($userData);

            // Sync roles
            $user->syncRoles([$newRole]);

            // Update department head if role changed to HOD
            if ($newRole === 'Head_of_Department' && $request->department_id) {
                // Remove previous HOD if any
                Department::where('head_of_department', $user->id)->update(['head_of_department' => null]);
                
                // Set new HOD
                Department::where('id', $request->department_id)->update([
                    'head_of_department' => $user->id,
                    'updated_by' => auth()->id()
                ]);
            }

            // Update student profile if exists
            if ($user->user_type === 'student' && $user->student) {
                $studentData = [];
                
                if ($request->has('programme_id')) {
                    $studentData['programme_id'] = $request->programme_id;
                }
                if ($request->has('current_level')) {
                    $studentData['current_level'] = $request->current_level;
                }
                if ($request->has('current_semester')) {
                    $studentData['current_semester'] = $request->current_semester;
                }
                if ($request->has('study_mode')) {
                    $studentData['study_mode'] = $request->study_mode;
                }
                if ($request->has('guardian_name')) {
                    $studentData['guardian_name'] = $request->guardian_name;
                }
                if ($request->has('guardian_phone')) {
                    $studentData['guardian_phone'] = $request->guardian_phone;
                }
                
                if (!empty($studentData)) {
                    $user->student->update($studentData);
                }
            }

            DB::commit();

            return redirect()->route('superadmin.users.show', $user->id)
                ->with('success', 'User updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $user->status = $request->status;
        $user->save();
        
        Log::info('User status updated', [
            'user_id' => $user->id,
            'new_status' => $request->status,
            'updated_by' => auth()->id()
        ]);
        
        return back()->with('success', "User status updated to {$request->status} successfully");
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->first_name . ' ' . $user->last_name;
        
        try {
            DB::beginTransaction();
            
            // If user is HOD, remove from department
            if ($user->department_id) {
                Department::where('id', $user->department_id)
                    ->where('head_of_department', $user->id)
                    ->update(['head_of_department' => null]);
            }
            
            // Check for related records
            if ($user->student) {
                // Delete student's invoices and transactions first
                Invoice::where('student_id', $user->student->id)->delete();
                FeeTransaction::where('student_id', $user->student->id)->delete();
                $user->student->delete();
            }
            
            if ($user->applications()->exists()) {
                $user->applications()->delete();
            }
            
            $user->delete();
            
            DB::commit();
            
            Log::info('User deleted', [
                'user_id' => $id,
                'user_name' => $userName,
                'deleted_by' => auth()->id()
            ]);
            
            return redirect()->route('superadmin.users.index')
                ->with('success', "User {$userName} deleted successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPassword($id)
    {
        $user = User::findOrFail($id);
        
        $defaultCredentials = $this->getDefaultCredentials($user);
        
        return view('superadmin.users.reset-password', compact('user', 'defaultCredentials'));
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'confirm_reset' => 'required|accepted'
        ]);
        
        try {
            $defaultCredentials = $this->getDefaultCredentials($user);
            
            $user->password = Hash::make($defaultCredentials['password']);
            $user->must_change_password = true;
            $user->save();
            
            Log::info('Password reset', [
                'user_id' => $user->id,
                'reset_by' => auth()->id()
            ]);
            
            return redirect()->route('superadmin.users.show', $user->id)
                ->with('success', 'Password reset successfully!')
                ->with('credentials', [
                    'username' => $defaultCredentials['username'],
                    'password' => $defaultCredentials['password'],
                    'message' => $defaultCredentials['message']
                ]);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Get default credentials based on user type
     */
    private function getDefaultCredentials(User $user)
    {
        switch ($user->user_type) {
            case 'student':
                if (!$user->registration_number) {
                    throw new \Exception('Student does not have a registration number');
                }
                return [
                    'username' => $user->registration_number,
                    'password' => $user->registration_number,
                    'message' => 'Student: Registration Number'
                ];
                
            case 'staff':
                if (!$user->email) {
                    throw new \Exception('Staff must have an email address');
                }
                return [
                    'username' => $user->email,
                    'password' => $user->email,
                    'message' => 'Staff: Email Address'
                ];
                
            case 'applicant':
                if (!$user->email) {
                    throw new \Exception('Applicant must have an email address');
                }
                return [
                    'username' => $user->email,
                    'password' => 'password123',
                    'message' => 'Applicant: Email & Default Password'
                ];
                
            default:
                $username = $user->email ?: $user->registration_number ?: ('USER-' . $user->id);
                return [
                    'username' => $username,
                    'password' => 'password123',
                    'message' => 'Default credentials'
                ];
        }
    }

    /**
     * Display user invoices
     */
    public function invoices($id)
    {
        $user = User::with('student')->findOrFail($id);
        
        if (!$user->student) {
            return redirect()->route('superadmin.users.show', $user->id)
                ->with('error', 'This user is not a student and has no invoices.');
        }
        
        $invoices = Invoice::where('student_id', $user->student->id)
            ->with('items', 'academicYear')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $summary = [
            'total_invoiced' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('paid_amount'),
            'total_balance' => $invoices->sum('balance'),
            'paid_count' => $invoices->where('payment_status', 'paid')->count(),
            'unpaid_count' => $invoices->where('payment_status', 'unpaid')->count(),
            'partial_count' => $invoices->where('payment_status', 'partial')->count()
        ];
        
        return view('superadmin.users.invoices', compact('user', 'invoices', 'summary'));
    }

    /**
     * Show single invoice
     */
    public function showInvoice($userId, $invoiceId)
    {
        $user = User::with('student')->findOrFail($userId);
        
        if (!$user->student) {
            return redirect()->route('superadmin.users.show', $user->id)
                ->with('error', 'This user is not a student.');
        }
        
        $invoice = Invoice::where('student_id', $user->student->id)
            ->with('items', 'academicYear', 'programmeFee.programme')
            ->findOrFail($invoiceId);
        
        // Get related transactions
        $transactions = FeeTransaction::where('student_id', $user->student->id)
            ->where('academic_year_id', $invoice->academic_year_id)
            ->where('reference_id', $invoice->id)
            ->where('reference_type', 'App\Models\Invoice')
            ->orderBy('transaction_date', 'desc')
            ->get();
        
        return view('superadmin.users.invoice-show', compact('user', 'invoice', 'transactions'));
    }

    /**
     * Generate new invoice for existing student
     */
    public function generateInvoice(Request $request, $id)
    {
        $user = User::with('student')->findOrFail($id);
        
        if (!$user->student) {
            return redirect()->route('superadmin.users.show', $user->id)
                ->with('error', 'This user is not a student.');
        }
        
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'level' => 'required|integer|min:1|max:6'
        ]);
        
        try {
            $invoice = $this->generateAnnualTuitionInvoice(
                $user->student, 
                $request->academic_year_id, 
                $request->level
            );
            
            $message = "Invoice generated successfully! ";
            $message .= "Invoice #: {$invoice->invoice_number} | ";
            $message .= "Control #: {$invoice->control_number} | ";
            $message .= "Amount: TZS " . number_format($invoice->total_amount, 0);
            
            return redirect()->route('superadmin.users.invoices', $user->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Manual invoice generation failed', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'student_id' => $user->student->id,
                'academic_year_id' => $request->academic_year_id,
                'level' => $request->level
            ]);
            
            return redirect()->route('superadmin.users.invoices', $user->id)
                ->with('error', 'Invoice generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $query = User::with('roles', 'student', 'applications', 'department');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('role') && $request->role != '') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        if ($request->has('user_type') && $request->user_type != '') {
            $query->where('user_type', $request->user_type);
        }
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('department_id') && $request->department_id != '') {
            $query->where('department_id', $request->department_id);
        }
        
        $users = $query->get();
        
        if ($format === 'pdf') {
            return view('superadmin.users.export_pdf', compact('users'));
        } else {
            $filename = 'users_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, [
                    'ID', 
                    'Full Name', 
                    'Email', 
                    'Phone', 
                    'Registration #', 
                    'User Type', 
                    'Role', 
                    'Department',
                    'Programme', 
                    'Current Level',
                    'Status', 
                    'Created Date'
                ]);
                
                foreach ($users as $user) {
                    $programme = '';
                    $level = '';
                    
                    if ($user->student) {
                        $programme = $user->student->programme->name ?? 'N/A';
                        $level = $user->student->current_level ?? 'N/A';
                    } elseif ($user->applications->isNotEmpty()) {
                        $programme = $user->applications->first()->programme->name ?? 'N/A';
                    }
                    
                    fputcsv($file, [
                        $user->id,
                        $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name,
                        $user->email ?? 'N/A',
                        $user->phone ?? 'N/A',
                        $user->registration_number ?? 'N/A',
                        ucfirst($user->user_type),
                        $user->roles->pluck('name')->first() ?? 'N/A',
                        $user->department->name ?? 'N/A',
                        $programme,
                        $level,
                        ucfirst($user->status),
                        $user->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
    }

    // =======================================================================
    // FEE MANAGEMENT METHODS
    // =======================================================================

    /**
     * Generate ANNUAL tuition invoice with control number
     */
    private function generateAnnualTuitionInvoice($student, $academicYearId, $level)
    {
        try {
            // Get programme fee
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
            
            // Get academic year
            $academicYear = AcademicYear::find($academicYearId);
            $academicYearName = $academicYear ? $academicYear->name : 'Academic Year ' . $academicYearId;

            // Get programme
            $programme = Programme::find($student->programme_id);
            $programmeName = $programme ? $programme->name : 'Programme';
            $feeDescription = $this->getProgrammeLevelDescription($programme, $level);

            // CREATE INVOICE
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
                'notes' => "Auto-generated invoice upon student registration for {$academicYearName}.",
                'metadata' => json_encode([
                    'level' => $level,
                    'academic_year' => $academicYearName,
                    'programme' => $programmeName,
                    'programme_fee_id' => $programmeFee->id,
                    'total_year_fee' => $programmeFee->total_year_fee,
                    'control_number' => $controlNumber,
                    'registration_fee' => $programmeFee->registration_fee,
                    'intake' => $student->intake,
                    'study_mode' => $student->study_mode,
                    'invoice_type' => 'annual_tuition',
                    'generated_at' => now()->toDateTimeString()
                ]),
                'created_by' => auth()->id() ?? 1
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

            // Create fee transaction
            if (class_exists('App\Models\FeeTransaction')) {
                FeeTransaction::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'control_number' => $controlNumber,
                    'receipt_number' => 'INVOICE-' . $invoiceNumber,
                    'transaction_type' => 'INVOICE',
                    'description' => 'Invoice: ' . $feeDescription,
                    'debit' => $programmeFee->total_year_fee,
                    'credit' => 0,
                    'running_balance' => $programmeFee->total_year_fee,
                    'reference_id' => $invoice->id,
                    'reference_type' => 'App\Models\Invoice',
                    'transaction_date' => now(),
                    'metadata' => json_encode([
                        'invoice_number' => $invoiceNumber,
                        'level' => $level,
                        'programme_fee_id' => $programmeFee->id
                    ])
                ]);
            }

            Log::info('Invoice generated', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
                'control_number' => $controlNumber,
                'student_id' => $student->id,
                'amount' => $programmeFee->total_year_fee
            ]);

            return $invoice;

        } catch (\Exception $e) {
            Log::error('Invoice generation error: ' . $e->getMessage(), [
                'student_id' => $student->id ?? null,
                'academic_year_id' => $academicYearId,
                'level' => $level,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique 12-digit control number
     */
    private function generateControlNumber($student)
    {
        // Format: YYMMDD + StudentID(4) + Random(2) = 12 digits
        $year = date('y');
        $month = date('m');
        $day = date('d');
        
        // Student ID padded to 4 digits
        $studentIdPadded = str_pad($student->id, 4, '0', STR_PAD_LEFT);
        
        // Random 2 digits
        $random = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
        
        // Generate 12-digit number (YYMMDD + ID(4) + RAND(2) = 12)
        $controlNumber = $year . $month . $day . $studentIdPadded . $random;
        
        // Ensure it's exactly 12 digits
        $controlNumber = substr($controlNumber, 0, 12);
        
        Log::info('Control number generated', [
            'control_number' => $controlNumber,
            'student_id' => $student->id,
            'length' => strlen($controlNumber)
        ]);
        
        return $controlNumber;
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
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $attempts = 0;
        $maxAttempts = 10;
        
        while ($attempts < $maxAttempts) {
            try {
                $count = Invoice::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->lockForUpdate()
                    ->count();
                
                $sequence = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
                $invoiceNumber = "INV/{$year}/{$month}/{$sequence}";
                
                $exists = Invoice::where('invoice_number', $invoiceNumber)->exists();
                
                if (!$exists) {
                    return $invoiceNumber;
                }
                
                $attempts++;
                
            } catch (\Exception $e) {
                Log::error('Error generating invoice number', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempts
                ]);
                $attempts++;
                
                if ($attempts >= $maxAttempts) {
                    return "INV/{$year}/{$month}/" . time() . "/" . rand(100, 999);
                }
            }
        }
        
        return "INV/" . time() . "/" . uniqid();
    }

    /**
     * Generate registration number
     * Format: 02.[SEQUENCE].[INTAKE_CODE].[YEAR]
     */
   private function generateRegistrationNumber($intake = 'March', $retry = 0)
{
    $campus = '02';
    $year   = date('Y');
    $intakeCode = $intake === 'September' ? '09' : '03';

    // HESABU KWA KUTUMIA withTrashed() – kuona hata waliopangwa kufutwa
    $lastRegNo = User::withTrashed()
        ->where('user_type', 'student')
        ->where('registration_number', 'like', "{$campus}.%.{$intakeCode}.{$year}")
        ->orderBy('registration_number', 'desc')
        ->value('registration_number');
    
    if ($lastRegNo) {
        $parts = explode('.', $lastRegNo);
        $lastSeq = (int)($parts[1] ?? 0);
        $sequence = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $sequence = '001';
    }

    $regNo = "{$campus}.{$sequence}.{$intakeCode}.{$year}";
    
    // ANGALIA UPO KWA WOTE (pamoja na waliofutwa)
    if (User::withTrashed()->where('registration_number', $regNo)->exists() && $retry < 5) {
        // Kama ipo, jaribu tena (itakuwa sequence +1)
        return $this->generateRegistrationNumber($intake, $retry + 1);
    }
    
    Log::info('Generated registration number', compact('regNo', 'intake', 'year'));
    return $regNo;
}
    /**
     * Generate application number
     */
    private function generateApplicationNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $count = Application::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
            
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $appNumber = "APP/{$year}/{$month}/{$sequence}";
        
        Log::info('Generated application number', [
            'app_number' => $appNumber,
            'year' => $year,
            'month' => $month
        ]);
        
        return $appNumber;
    }
}
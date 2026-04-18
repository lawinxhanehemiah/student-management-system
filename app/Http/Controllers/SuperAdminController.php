<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Helpers\LayoutHelper;
use App\Models\Programme;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class SuperAdminController extends Controller
{
    protected $layout;

    public function __construct()
    {
        $this->layout = LayoutHelper::getLayoutForRole();
    }

    // =======================
    // DASHBOARD
    // =======================
    public function index()
    {
        return $this->dashboard();
    }

    // =======================
    // USERS CRUD
    // =======================
    public function indexUsers(Request $request)
    {
        $query = User::with('roles')->latest();
        
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
        $perPage = $request->has('per_page') ? $request->per_page : 10;
        
        $users = $query->paginate($perPage);
        
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
        
        return view('superadmin.users.index', compact(
            'users', 
            'totalUsers', 
            'studentCount', 
            'staffCount', 
            'applicantCount',
            'roles',
            'programmes'
        ));
    }

    public function createUser()
    {
        $roles = Role::all();
        $programmes = Programme::all();
        $courses    = Course::all();
        $faculties  = Faculty::all();
        $academicYears = AcademicYear::all();

        return view('superadmin.users.create', compact(
            'roles', 'programmes', 'courses', 'faculties', 'academicYears'
        ));
    }

    public function storeUser(Request $request)
    {
        // =====================
        // BASE VALIDATION
        // =====================
        $baseValidation = [
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'role'        => 'required|exists:roles,name',
        ];

        $role = $request->role;
        $userType = match ($role) {
            'Student'   => 'student',
            'Applicant' => 'applicant',
            default     => 'staff',
        };

        // Add email validation based on role
        if ($userType === 'applicant' || $userType === 'staff') {
            $baseValidation['email'] = 'required|email|unique:users,email';
        } else {
            $baseValidation['email'] = 'nullable|email|unique:users,email';
        }

        $request->validate($baseValidation);

        // =====================
        // USER TYPE
        // =====================
        // Already defined above

        // =====================
        // CREDENTIAL LOGIC
        // =====================
        if ($userType === 'student') {
            $regNo = $this->generateRegistrationNumber($request->intake);
            $username = $regNo;
            $password = $regNo;
            $mustChange = true;
            $email = null;
        } elseif ($userType === 'staff') {
            $username = $request->email;
            $password = $request->email;
            $mustChange = true;
            $regNo = null;
            $email = $request->email;
        } else { // applicant
            $username = $request->email;
            $password = 'password123';
            $mustChange = false;
            $regNo = null;
            $email = $request->email;
        }

        // =====================
        // DATABASE TRANSACTION
        // =====================
        DB::transaction(function() use ($request, $role, $userType, $email, $password, $regNo, $mustChange, &$username) {

            // --- CREATE USER ---
            $user = User::create([
                'first_name'       => $request->first_name,
                'middle_name'      => $request->middle_name,
                'last_name'        => $request->last_name,
                'email'            => $email,
                'registration_number' => $regNo,
                'phone'            => $request->phone ?? null,
                'gender'           => $request->gender ?? null,
                'profile_photo'    => $request->profile_photo ?? null, 
                'password'         => Hash::make($password),
                'user_type'        => $userType,
                'must_change_password' => $mustChange,
                'status'           => 'active',
            ]);

            $user->assignRole($role);

            // --- STUDENT PROFILE ---
            if ($userType === 'student') {
                $request->validate([
                    'intake'        => 'required|in:March,September',
                    'programme_id'  => 'required|exists:programmes,id',
                    'course_id'     => 'required|exists:courses,id',
                    'study_mode'    => 'required|in:full_time,part_time,distance',
                    'guardian_name' => 'required|string|max:255',
                    'guardian_phone'=> 'required|string|max:255',
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'application_id' => $request->application_id ?? null,
                    'registration_number' => $regNo,
                    'programme_id' => $request->programme_id,
                    'course_id' => $request->course_id,
                    'study_mode' => $request->study_mode,
                    'intake' => $request->intake,
                    'status' => 'active',
                    'guardian_name' => $request->guardian_name,
                    'guardian_phone'=> $request->guardian_phone,
                ]);
            }

            // --- APPLICANT / APPLICATION ---
            if ($userType === 'applicant') {
                $request->validate([
                    'phone_number'  => 'required|string|max:255',
                    'date_of_birth' => 'required|date',
                    'region'        => 'required|string|max:255',
                    'district'      => 'required|string|max:255',
                    'intake'        => 'required|in:March,September',
                    'programme_id'  => 'required|exists:programmes,id',
                    'course_id'     => 'required|exists:courses,id',
                    'study_mode'    => 'required|in:full_time,part_time,distance',
                    
                    // Optional fields
                    'nationality'       => 'nullable|string|max:255',
                    'national_id'       => 'nullable|string|max:255',
                    'entry_qualification' => 'nullable|string|max:255',
                    'secondary_school'  => 'nullable|string|max:255',
                    'completion_year'   => 'nullable|integer',
                    'faculty_id'        => 'nullable|exists:faculties,id',
                    'academic_year_id'  => 'nullable|exists:academic_years,id',
                    'year_of_study'     => 'nullable|integer',
                    'guardian_name'     => 'nullable|string|max:255',
                    'guardian_phone'    => 'nullable|string|max:255',
                    'guardian_relationship'=> 'nullable|string|max:255',
                    'guardian_address'  => 'nullable|string|max:255',
                    'sponsorship_type'  => 'nullable|string|max:255',
                ]);

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
                    'region'    => $request->region,
                    'district'  => $request->district,
                    
                    'status'           => 'pending',
                    'application_stage' => 'submitted',
                ]);
            }
        });

        // =====================
        // RETURN SUCCESS MESSAGE
        // =====================
        return redirect()
            ->route('superadmin.users.index')
            ->with(
                'success',
                "User created successfully. Username: {$username} | Temp Password: {$password}"
            );
    }

    public function showUser($id)
    {
        $user = User::with(['roles', 'applications', 'student'])->findOrFail($id);
        return view('superadmin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();

        return view('superadmin.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email' => "nullable|email|unique:users,email,$id",
            'role'  => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'status' => 'required|in:active,inactive,suspended',
            'profile_photo' => 'nullable|image|max:2048'
        ]);

        // Update profile photo if uploaded
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = $profilePhotoPath;
        }

        $user->first_name  = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name   = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->status = $request->status;
        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('superadmin.users.index')
                         ->with('success','User updated successfully');
    }

    public function updateUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $user->status = $request->status;
        $user->save();
        
        return back()->with('success', 'User status updated successfully');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->first_name . ' ' . $user->last_name;
        
        $user->delete();

        return redirect()->route('superadmin.users.index')
                         ->with('success', "User {$userName} deleted successfully");
    }

    // =======================
    // EXPORT USERS
    // =======================
    public function exportUsers(Request $request)
    {
        $format = $request->get('format', 'excel');
        
        $query = User::with('roles');
        
        // Apply same filters as index
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
        
        $users = $query->get();
        
        if ($format === 'pdf') {
            // Return PDF view
            return view('superadmin.users.export_pdf', compact('users'));
        } else {
            // For Excel, you would typically use a package like Maatwebsite/Laravel-Excel
            // For now, return a CSV
            $filename = 'users_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['ID', 'Full Name', 'Email', 'Phone', 'User Type', 'Role', 'Status', 'Created Date']);
                
                // Add data
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->first_name . ' ' . $user->last_name,
                        $user->email,
                        $user->phone ?? 'N/A',
                        ucfirst($user->user_type),
                        $user->roles->pluck('name')->first() ?? 'N/A',
                        ucfirst($user->status),
                        $user->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
    }

    // =======================
    // DASHBOARD WITH COMPLETE STATS
    // =======================
    public function dashboard()
    {
        // User Statistics
        $totalStudents = User::role('Student')->count();
        $activeStudents = $totalStudents;
        $newStudentsThisMonth = User::role('Student')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $studentGrowth = $this->calculateGrowthRate('Student');
        
        // Staff Statistics
        $totalStaff = User::whereHas('roles', function($q) {
            $q->whereNotIn('name', ['Student', 'Applicant']);
        })->count();
        
        $teachingStaff = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Tutor']);
        })->count();
        
        $nonTeachingStaff = User::whereHas('roles', function($q) {
            $q->whereIn('name', [
                'Director', 'Principal', 'Deputy_Principal_Academics', 
                'Deputy_Principal_Administration', 'Head_of_Department',
                'Dean_of_Students', 'Admission_Officer', 'Examination_Officer',
                'ICT_Manager', 'HR_Manager', 'Financial_Controller', 'Accountant',
                'Procurement_Officer', 'Librarian', 'Records_Officer', 'Estate_Manager',
                'PR_Marketing_Officer', 'Quality_Assurance_Manager', 'Secretary'
            ]);
        })->count();
        
        $staffGrowth = $this->calculateGrowthRate('staff');
        
        // Academic Statistics
        $totalCourses = Course::count();
        $activeCourses = $totalCourses;
        $totalPrograms = Programme::count();
        $courseGrowth = $this->calculateGrowthRate('course');
        
        // Financial Statistics - USING APPLICATION COUNTS INSTEAD OF FEES
        $totalApplications = Application::count();
        $todayApplications = Application::whereDate('created_at', Carbon::today())->count();
        
        // Calculate estimated revenue based on application counts
        $applicationFeeAmount = 10000; // Assuming 10,000 TZS per application
        $todayRevenue = $todayApplications * $applicationFeeAmount;
        $todayTransactions = $todayApplications;
        $averageTransaction = $todayTransactions > 0 ? $todayRevenue / $todayTransactions : 0;
        
        $yesterdayApplications = Application::whereDate('created_at', Carbon::yesterday())->count();
        $yesterdayRevenue = $yesterdayApplications * $applicationFeeAmount;
        $revenueChange = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : 0;
        
        // Application Statistics
        $pendingApplications = Application::where('status', 'pending')->count();
        $newApplicationsToday = Application::whereDate('created_at', Carbon::today())->count();
        $overdueApplications = Application::where('status', 'pending')
            ->whereDate('created_at', '<', Carbon::now()->subDays(7))
            ->count();
        
        // System Statistics
        $systemUptime = 99.9;
        $avgResponseTime = 120;
        
        // Session Statistics - USING UPDATED_AT INSTEAD OF LAST_ACTIVITY
        // We'll use users updated in the last 24 hours as "active"
        $activeUsers = User::where('updated_at', '>', Carbon::now()->subDay())->count();
        $studentSessions = User::role('Student')->where('updated_at', '>', Carbon::now()->subDay())->count();
        $staffSessions = User::whereHas('roles', function($q) {
            $q->whereNotIn('name', ['Student', 'Applicant']);
        })->where('updated_at', '>', Carbon::now()->subDay())->count();
        
        $sessionChange = 5;
        
        // Storage Statistics
        $storageUsed = 150;
        $storageTotal = 500;
        $storageUsage = ($storageUsed / $storageTotal) * 100;
        $storageChange = 2;
        
        // Fee Collection Statistics - USING APPLICATION COUNTS FOR ESTIMATION
        // These are now estimates based on application counts
        $paidApplicationsEstimate = round($totalApplications * 0.7); // Estimate 70% paid
        $unpaidApplicationsEstimate = $totalApplications - $paidApplicationsEstimate;
        
        $feePaidPercentage = $totalApplications > 0 ? ($paidApplicationsEstimate / $totalApplications) * 100 : 0;
        $feePendingPercentage = $totalApplications > 0 ? ($unpaidApplicationsEstimate / $totalApplications) * 100 : 0;
        
        // Enrollment Chart Data
        $enrollmentData = [];
        $returningStudentsData = [];
        $enrollmentMonths = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $enrollmentMonths[] = $month->format('M Y');
            
            $enrollmentData[] = User::role('Student')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            
            // Placeholder for returning students
            $returningStudentsData[] = rand(5, 20);
        }
        
        // Recent Activities
        $recentActivities = $this->getRecentActivities();
        
        // System Alerts
        $systemAlerts = [];
        
        // Check for overdue applications
        if ($overdueApplications > 0) {
            $systemAlerts[] = (object)[
                'type' => 'warning',
                'title' => 'Overdue Applications',
                'message' => "There are {$overdueApplications} applications pending review for more than 7 days."
            ];
        }
        
        // Add default alert if no alerts
        if (empty($systemAlerts)) {
            $systemAlerts[] = (object)[
                'type' => 'success',
                'title' => 'System Status',
                'message' => 'All systems operational'
            ];
        }
        
        // Notifications for layout
        $unreadNotifications = auth()->user()->unreadNotifications()->count();
        $recentNotifications = auth()->user()->notifications()->latest()->take(5)->get();
        
        $layout = $this->layout;
        
        return view('dashboards.superadmin', compact(
            'layout',
            'totalStudents', 'activeStudents', 'newStudentsThisMonth', 'studentGrowth',
            'totalStaff', 'teachingStaff', 'nonTeachingStaff', 'staffGrowth',
            'totalCourses', 'activeCourses', 'totalPrograms', 'courseGrowth',
            'todayRevenue', 'todayTransactions', 'averageTransaction', 'revenueChange',
            'pendingApplications', 'newApplicationsToday', 'overdueApplications',
            'systemUptime', 'avgResponseTime',
            'activeUsers', 'studentSessions', 'staffSessions', 'sessionChange',
            'storageUsed', 'storageTotal', 'storageUsage', 'storageChange',
            'paidApplicationsEstimate', 'unpaidApplicationsEstimate', 'totalApplications',
            'feePaidPercentage', 'feePendingPercentage',
            'enrollmentData', 'returningStudentsData', 'enrollmentMonths',
            'recentActivities', 'systemAlerts',
            'unreadNotifications', 'recentNotifications'
        ));
    }
    
    // =======================
    // AJAX DASHBOARD METHODS
    // =======================
    
    /**
     * Get dashboard stats via AJAX
     */
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'totalStudents' => User::role('Student')->count(),
                'totalStaff' => User::whereHas('roles', function($q) {
                    $q->whereNotIn('name', ['Student', 'Applicant']);
                })->count(),
                'pendingApplications' => Application::where('status', 'pending')->count(),
                'activeUsers' => User::where('updated_at', '>', Carbon::now()->subDay())->count(),
            ];
            
            // Calculate today's estimated revenue from applications
            $todayApplications = Application::whereDate('created_at', Carbon::today())->count();
            $stats['todayRevenue'] = $todayApplications * 10000;
            
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get chart data via AJAX
     */
    public function getChartData(Request $request)
    {
        try {
            $period = $request->input('period', 6);
            
            $months = [];
            $newStudents = [];
            $newApplications = [];
            
            for ($i = $period - 1; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $months[] = $month->format('M Y');
                
                $newStudents[] = User::role('Student')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                
                // Applications per month
                $newApplications[] = Application::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
            }
            
            return response()->json([
                'months' => $months,
                'newStudents' => $newStudents,
                'newApplications' => $newApplications
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Calculate growth rate for a model/type
     */
    private function calculateGrowthRate($type)
    {
        try {
            $currentMonthCount = 0;
            $lastMonthCount = 0;
            
            $lastMonth = Carbon::now()->subMonth();
            
            switch ($type) {
                case 'Student':
                    $currentMonthCount = User::role('Student')
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count();
                        
                    $lastMonthCount = User::role('Student')
                        ->whereMonth('created_at', $lastMonth->month)
                        ->whereYear('created_at', $lastMonth->year)
                        ->count();
                    break;
                    
                case 'staff':
                    $currentMonthCount = User::whereHas('roles', function($q) {
                        $q->whereNotIn('name', ['Student', 'Applicant']);
                    })
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count();
                    
                    $lastMonthCount = User::whereHas('roles', function($q) {
                        $q->whereNotIn('name', ['Student', 'Applicant']);
                    })
                    ->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year)
                    ->count();
                    break;
                    
                case 'course':
                    $currentMonthCount = Course::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count();
                        
                    $lastMonthCount = Course::whereMonth('created_at', $lastMonth->month)
                        ->whereYear('created_at', $lastMonth->year)
                        ->count();
                    break;
                    
                case 'application':
                    $currentMonthCount = Application::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count();
                        
                    $lastMonthCount = Application::whereMonth('created_at', $lastMonth->month)
                        ->whereYear('created_at', $lastMonth->year)
                        ->count();
                    break;
            }
            
            if ($lastMonthCount > 0) {
                return round((($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
            }
            
            return $currentMonthCount > 0 ? 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        // Get recent user registrations
        $recentUsers = User::with('roles')->latest()->take(3)->get();
        
        foreach ($recentUsers as $user) {
            $role = $user->roles->first();
            $roleName = $role ? $role->name : 'User';
            
            $activities[] = (object)[
                'icon' => 'user-plus',
                'color' => $user->user_type === 'student' ? 'primary' : 
                          ($user->user_type === 'staff' ? 'success' : 'info'),
                'description' => "New {$roleName} registered: {$user->first_name} {$user->last_name}",
                'time_ago' => $user->created_at->diffForHumans(),
                'badge' => 'New',
                'badge_color' => $user->user_type === 'student' ? 'primary' : 
                               ($user->user_type === 'staff' ? 'success' : 'info')
            ];
        }
        
        // Get recent applications
        $recentApplications = Application::with('user')->latest()->take(3)->get();
        
        foreach ($recentApplications as $application) {
            $activities[] = (object)[
                'icon' => 'file-alt',
                'color' => 'warning',
                'description' => "New application from {$application->first_name} {$application->last_name}",
                'time_ago' => $application->created_at->diffForHumans(),
                'badge' => ucfirst($application->status),
                'badge_color' => $application->status === 'approved' ? 'success' : 
                               ($application->status === 'rejected' ? 'danger' : 'warning')
            ];
        }
        
        // If no activities, add default
        if (empty($activities)) {
            $activities = [
                (object)[
                    'icon' => 'check-circle',
                    'color' => 'success',
                    'description' => 'System initialized successfully',
                    'time_ago' => 'Just now',
                    'badge' => 'System',
                    'badge_color' => 'success'
                ]
            ];
        }
        
        return array_slice($activities, 0, 5); // Return max 5 activities
    }

    // =======================
    // STUDENTS MANAGEMENT
    // =======================
    
    /**
     * List all students
     */
    public function indexStudents()
    {
        $students = User::role('Student')->latest()->get();
        return view('superadmin.students.index', compact('students'));
    }
    
    /**
     * Show student details
     */
    public function showStudent($id)
    {
        $student = User::role('Student')->with('applications')->findOrFail($id);
        return view('superadmin.students.show', compact('student'));
    }
    
    // =======================
    // STAFF MANAGEMENT
    // =======================
    
    /**
     * List all staff
     */
    public function indexStaff()
    {
        $staff = User::whereHas('roles', function($q) {
            $q->whereNotIn('name', ['Student', 'Applicant']);
        })->latest()->get();
        
        return view('superadmin.staff.index', compact('staff'));
    }
    
    // =======================
    // COURSES MANAGEMENT
    // =======================
    
    /**
     * List all courses
     */
    public function indexCourses()
    {
        $courses = Course::latest()->get();
        return view('superadmin.courses.index', compact('courses'));
    }
    
    /**
     * Create course
     */
    public function createCourse()
    {
        $programmes = Programme::all();
        $faculties = Faculty::all();
        return view('superadmin.courses.create', compact('programmes', 'faculties'));
    }
    
    /**
     * Store course
     */
    public function storeCourse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses,code',
            'programme_id' => 'nullable|exists:programmes,id',
            'faculty_id' => 'nullable|exists:faculties,id',
            'description' => 'nullable|string',
        ]);
        
        Course::create($request->all());
        
        return redirect()->route('superadmin.courses.index')
                         ->with('success', 'Course created successfully');
    }
    
    // =======================
    // APPLICATIONS MANAGEMENT
    // =======================
    
    /**
     * List all applications
     */
    public function indexApplications()
    {
        $applications = Application::with('user')->latest()->get();
        return view('superadmin.applications.index', compact('applications'));
    }
    
    /**
     * Show application details
     */
    public function showApplication($id)
    {
        $application = Application::with('user', 'programme', 'course', 'faculty')->findOrFail($id);
        return view('superadmin.applications.show', compact('application'));
    }
    
    /**
     * Update application status
     */
    public function updateApplicationStatus(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected',
            'review_notes' => 'nullable|string',
        ]);
        
        $application->status = $request->status;
        $application->review_notes = $request->review_notes;
        $application->reviewed_by = auth()->id();
        
        if ($request->status === 'approved') {
            $application->approved_at = Carbon::now();
        }
        
        $application->save();
        
        return back()->with('success', 'Application status updated successfully');
    }
    
    // =======================
    // REPORTS MANAGEMENT
    // =======================
    
    /**
     * Enrollment reports
     */
    public function enrollmentReport()
    {
        // Get users by type
        $usersByType = User::select('user_type', \DB::raw('count(*) as total'))
            ->groupBy('user_type')
            ->get();
        
        // Get applications by status
        $applicationsByStatus = Application::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
            
        // Get students by intake
        $students = User::role('Student')->get();
        $intakeCounts = [
            'March' => $students->where('intake_month', '03')->count(),
            'September' => $students->where('intake_month', '09')->count(),
            'Other' => $students->whereNotIn('intake_month', ['03', '09'])->count(),
        ];
        
        // Get applications by month for the last 6 months
        $applicationsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $applicationsByMonth[$month->format('M Y')] = Application::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }
            
        return view('superadmin.reports.enrollment', compact(
            'usersByType', 
            'applicationsByStatus', 
            'intakeCounts',
            'applicationsByMonth'
        ));
    }
    
    // =======================
    // SYSTEM SETTINGS
    // =======================
    
    /**
     * System settings page
     */
    public function settings()
    {
        return view('superadmin.settings.index');
    }
    
    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|email',
            'currency' => 'required|string|max:10',
            'academic_year' => 'required|string',
        ]);
        
        // Store settings in config or database
        // For now, we'll just return success
        return back()->with('success', 'Settings updated successfully');
    }
    
    // =======================
    // NOTIFICATIONS
    // =======================
    
    /**
     * List all notifications
     */
    public function indexNotifications()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('superadmin.notifications.index', compact('notifications'));
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    }
    
    // =======================
    // PROFILE MANAGEMENT
    // =======================
    
    /**
     * Show super admin profile
     */
    public function profile()
    {
        $user = auth()->user();
        return view('superadmin.profile', compact('user'));
    }
    
    /**
     * Update super admin profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        // Update basic info
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        // Update password if provided
        if ($request->filled('new_password')) {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
            } else {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
        }
        
        // Update profile photo if provided
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = $profilePhotoPath;
        }
        
        $user->save();
        
        return back()->with('success', 'Profile updated successfully');
    }
    
    // =======================
    // HELPER METHODS
    // =======================
    
    private function generateRegistrationNumber(string $intake)
    {
        $campus = '02';
        $year   = date('Y');
        $intakeCode = $intake === 'September' ? '09' : '03';

        $count = User::where('user_type','student')
            ->where('intake_month', $intakeCode)
            ->whereYear('created_at', $year)
            ->count() + 1;

        $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "{$campus}.{$sequence}.{$intakeCode}.{$year}";
    }

    private function generateApplicationNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $count = Application::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
            
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return "APP/{$year}/{$month}/{$sequence}";
    }

}
<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Student;
use App\Models\Application;
use App\Models\Programme;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\StudentAcademicStatus;
use App\Models\StudentAddress;

class AdmissionController extends Controller
{
    /**
     * Show create student form with auto-fill capability
     */
    public function createStudent()
    {
        // Get approved applications that haven't been converted
        $applications = DB::table('applications as a')
            ->select('a.*', 'p.first_name', 'p.middle_name', 'p.last_name')
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->whereIn('a.status', ['approved', 'selected'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('students')
                    ->whereColumn('students.application_id', 'a.id');
            })
            ->orderBy('a.created_at', 'desc')
            ->limit(100)
            ->get();

        // Get other data needed for form
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $programmes = Programme::where('status', 'active')->orderBy('name')->get();
        $courses = Course::where('status', 'active')->orderBy('name')->get();

        return view('admission.students.create', compact(
            'applications',
            'academicYears',
            'programmes',
            'courses'
        ));
    }

    /**
     * Search application by number (AJAX)
     */
    public function searchApplication(Request $request)
{
    try {
        $request->validate([
            'application_number' => 'required|string|max:50'
        ]);
        
        Log::info('Searching for application:', ['number' => $request->application_number]);
        
        // Get application with all related data
        $application = DB::table('applications')
            ->where('application_number', $request->application_number)
            ->first();
        
        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => '❌ Application not found.'
            ], 404);
        }
        
        // Check if application is approved/selected
        if (!in_array($application->status, ['approved', 'selected', 'submitted'])) {
            return response()->json([
                'success' => false,
                'message' => '❌ Application is not approved. Status: ' . ucfirst($application->status)
            ], 400);
        }
        
        // Check if already converted to student
        $studentExists = DB::table('students')
            ->where('application_id', $application->id)
            ->exists();
            
        if ($studentExists) {
            return response()->json([
                'success' => false,
                'message' => '❌ This application has already been converted to a student.'
            ], 400);
        }
        
        // Get all related data
        $personal = DB::table('application_personal_infos')
            ->where('application_id', $application->id)
            ->first();
        
        $contact = DB::table('application_contacts')
            ->where('application_id', $application->id)
            ->first();
        
        $nextOfKin = DB::table('application_next_of_kins')
            ->where('application_id', $application->id)
            ->first();
        
        $academic = DB::table('application_academics')
            ->where('application_id', $application->id)
            ->first();
        
        $programChoice = DB::table('application_program_choices')
            ->where('application_id', $application->id)
            ->first();
        
        // FIX: Get the CORRECT selected program
        $selectedProgram = null;
        
        // 1. First priority: selected_program_id from applications table
        if ($application->selected_program_id) {
            $selectedProgram = DB::table('programmes')
                ->where('id', $application->selected_program_id)
                ->first();
        }
        // 2. Second priority: selected_program_id from program_choices table
        elseif ($programChoice && $programChoice->selected_program_id) {
            $selectedProgram = DB::table('programmes')
                ->where('id', $programChoice->selected_program_id)
                ->first();
        }
        // 3. Third priority: first choice program (old behavior)
        elseif ($programChoice && $programChoice->first_choice_program_id) {
            $selectedProgram = DB::table('programmes')
                ->where('id', $programChoice->first_choice_program_id)
                ->first();
        }
        
        // Get courses for the SELECTED programme (not first choice)
        $courses = [];
        if ($selectedProgram) {
            $courses = DB::table('courses')
                ->where('programme_id', $selectedProgram->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }
        
        // Prepare response
        $responseData = [
            'id' => $application->id,
            'application_number' => $application->application_number,
            'user_id' => $application->user_id,
            'intake' => $application->intake,
            'study_mode' => $application->study_mode,
            'entry_level' => $application->entry_level,
            'status' => $application->status,
            'selected_program_id' => $application->selected_program_id, // This is the KEY
            'personal_info' => $personal,
            'contact_info' => $contact,
            'next_of_kin' => $nextOfKin,
            'academic_info' => $academic,
            'program_choice' => $programChoice,
            'selected_program' => $selectedProgram,
            'courses' => $courses
        ];
        
        Log::info('Application found with selected program:', [
            'id' => $application->id,
            'selected_program_id' => $application->selected_program_id,
            'program_name' => $selectedProgram ? $selectedProgram->name : 'None'
        ]);
        
        return response()->json([
            'success' => true,
            'application' => $responseData,
            'message' => '✅ Application found successfully!'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error searching application:', [
            'number' => $request->application_number,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => '❌ Server error: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Get courses by programme ID (AJAX)
     */
    public function getCoursesByProgramme(Request $request, $programmeId)
    {
        try {
            Log::info('Loading courses for programme:', ['id' => $programmeId]);
            
            $courses = DB::table('courses')
                ->where('programme_id', $programmeId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'programme_id']);
            
            $programme = DB::table('programmes')
                ->where('id', $programmeId)
                ->first(['id', 'code', 'name']);
            
            return response()->json([
                'success' => true,
                'courses' => $courses,
                'programme' => $programme,
                'count' => $courses->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading courses:', [
                'programme_id' => $programmeId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error loading courses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new student with auto-fill capability
     */
    public function storeStudent(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'application_number' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'guardian_name' => 'required|string|max:200',
            'guardian_phone' => 'required|string|max:20',
            'programme_id' => 'required|exists:programmes,id',
            'course_id' => 'required|exists:courses,id',
            'study_mode' => 'required|in:full_time,part_time,distance,evening,weekend,online',
            'intake' => 'required|in:March,September',
            'academic_year_id' => 'required|exists:academic_years,id',
            'application_id' => 'nullable|exists:applications,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $application = null;
            
            // Check if application number was provided
            if ($request->filled('application_number')) {
                $application = DB::table('applications')
                    ->where('application_number', $request->application_number)
                    ->whereIn('status', ['approved', 'selected'])
                    ->first();
                
                if (!$application) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['application_number' => 'Invalid or unapproved application number']);
                }
                
                // Mark application as converted
                DB::table('applications')
                    ->where('id', $application->id)
                    ->update([
                        'status' => 'converted_to_student',
                        'converted_at' => now(),
                        'converted_by' => auth()->id()
                    ]);
            }

            // Generate registration number
            $intake = $request->intake ?? 'March';
            $regNo = $this->generateRegistrationNumber($intake);
            
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email ?? $regNo . '@college.ac.tz',
                'registration_number' => $regNo,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'password' => Hash::make($regNo), // Password same as reg number
                'user_type' => 'student',
                'must_change_password' => true,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $user->assignRole('Student');

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'application_id' => $application ? $application->id : $request->application_id,
                'registration_number' => $regNo,
                'programme_id' => $request->programme_id,
                'course_id' => $request->course_id,
                'study_mode' => $request->study_mode,
                'intake' => $request->intake,
                'status' => 'active',
                'guardian_name' => $request->guardian_name,
                'guardian_phone' => $request->guardian_phone,
                'guardian_relationship' => $request->guardian_relationship,
                'guardian_address' => $request->guardian_address,
                'registered_by' => auth()->id(),
                'registration_date' => now(),
            ]);

            // Create academic status
            StudentAcademicStatus::create([
                'student_id' => $student->id,
                'academic_year_id' => $request->academic_year_id,
                'year_of_study' => 1,
                'semester' => 1,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            // Create address if provided
            if ($request->filled('region') || $request->filled('district')) {
                StudentAddress::create([
                    'student_id' => $student->id,
                    'region' => $request->region,
                    'district' => $request->district,
                    'street_address' => $request->street_address,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->route('admission.students.show', $student->id)
                ->with('success', "✅ Student registered successfully!<br><br>
                    <strong>Registration Number:</strong> {$regNo}<br>
                    <strong>Temporary Password:</strong> {$regNo}<br><br>
                    <small class='text-muted'>Student must change password on first login.</small>");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Student registration failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '❌ Registration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate registration number
     */
    private function generateRegistrationNumber($intake)
    {
        $campus = '02'; // Campus code
        $year = date('Y');
        $intakeCode = $intake === 'September' ? '09' : '03';
        
        $count = DB::table('students')
            ->whereYear('registration_date', $year)
            ->where('intake', $intake)
            ->count() + 1;
        
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return "{$campus}.{$sequence}.{$intakeCode}.{$year}";
    }

    /**
     * Get approved applications for dropdown
     */
    public function getApprovedApplications(Request $request)
    {
        try {
            $applications = DB::table('applications as a')
                ->select(
                    'a.id',
                    'a.application_number',
                    'a.intake',
                    'a.study_mode',
                    'a.selected_program_id',
                    'p.first_name',
                    'p.middle_name',
                    'p.last_name',
                    'prog.code as programme_code',
                    'prog.name as programme_name'
                )
                ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
                ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
                ->whereIn('a.status', ['approved', 'selected'])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('students')
                        ->whereColumn('students.application_id', 'a.id');
                })
                ->orderBy('a.created_at', 'desc')
                ->limit(50)
                ->get();
            
            return response()->json([
                'success' => true,
                'applications' => $applications,
                'count' => $applications->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading approved applications:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading applications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API version of search application (for frontend)
     */
    public function searchApplicationApi(Request $request)
    {
        return $this->searchApplication($request);
    }

    /**
     * API version of get courses by programme
     */
    public function getCoursesByProgrammeApi(Request $request, $programmeId)
    {
        return $this->getCoursesByProgramme($request, $programmeId);
    }

    /**
     * API version of get approved applications
     */
    public function getApprovedApplicationsApi(Request $request)
    {
        return $this->getApprovedApplications($request);
    }

    /**
     * Validate application number
     */
    public function validateApplicationNumber(Request $request)
    {
        try {
            $request->validate([
                'application_number' => 'required|string|max:50'
            ]);
            
            $exists = DB::table('applications')
                ->where('application_number', $request->application_number)
                ->whereIn('status', ['approved', 'selected'])
                ->exists();
            
            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Application number is valid' : 'Application number not found or not approved'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Other methods as needed...
}
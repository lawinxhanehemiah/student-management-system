<?php
// app/Http/Controllers/Admission/ApplicationManagementController.php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Services\EligibilityService;
use App\Services\NectaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationManagementController extends Controller
{
    protected $eligibilityService;
    protected $nectaService;

    public function __construct(
        EligibilityService $eligibilityService,
        NectaService $nectaService
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->nectaService = $nectaService;
    }

    /**
     * Display list of all applications
     */
    public function index(Request $request)
    {
        $query = DB::table('applications')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
            ->select(
                'applications.*',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as applicant_name"),
                'users.email as applicant_email',
                'academic_years.name as academic_year_name'
            );

        if ($request->status && $request->status != 'all') {
            $query->where('applications.status', $request->status);
        }

        if ($request->academic_year) {
            $query->where('applications.academic_year_id', $request->academic_year);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where(DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))"), 'like', '%' . $request->search . '%')
                  ->orWhere('users.email', 'like', '%' . $request->search . '%')
                  ->orWhere('applications.application_number', 'like', '%' . $request->search . '%');
            });
        }

        $applications = $query->orderBy('applications.created_at', 'desc')->paginate(20);
        
        $academicYears = DB::table('academic_years')->orderBy('name', 'desc')->get();
        
        $applicants = DB::table('users')
            ->where('user_type', 'applicant')
            ->select('id', 'email', DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as name"))
            ->orderBy('first_name')
            ->get();

        return view('admission.applications.index', compact('applications', 'academicYears', 'applicants'));
    }

   /**
     * Show form to create new application - NO AUTO-CREATION
     */
    public function create()
    {
        // Get academic years for dropdown
        $academicYears = DB::table('academic_years')->orderBy('name', 'desc')->get();
        
        // Get existing applicants for reference (optional)
        $applicants = DB::table('users')
            ->where('user_type', 'applicant')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        
        // Just show the form - DO NOT CREATE APPLICATION HERE
        return view('admission.applications.create', compact('academicYears', 'applicants'));
    }

    /**
     * Store new application - Creates application for CORRECT applicant
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:academic_years,id',
            'intake' => 'required|in:March,September',
            'entry_level' => 'required|in:CSEE,ACSEE,Diploma,Degree,Mature',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            $userId = $request->user_id;
            
            // If no user_id provided, check if applicant exists by email
            if (!$userId) {
                $existingUser = DB::table('users')
                    ->where('email', $request->email)
                    ->first();
                
                if ($existingUser) {
                    $userId = $existingUser->id;
                } else {
                    // Create new applicant
                    $password = $request->password ? bcrypt($request->password) : bcrypt(Str::random(8));
                    
                    $userId = DB::table('users')->insertGetId([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'gender' => $request->gender,
                        'password' => $password,
                        'user_type' => 'applicant',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Check for existing application for this academic year
            $existing = DB::table('applications')
                ->where('user_id', $userId)
                ->where('academic_year_id', $request->academic_year_id)
                ->first();

            if ($existing) {
                DB::rollBack();
                return redirect()->back()->with('error', 'This applicant already has an application for this academic year.');
            }
            
            // Create application for the CORRECT applicant
            $applicationId = DB::table('applications')->insertGetId([
                'user_id' => $userId,
                'application_number' => 'APP-' . date('Y') . '-' . Str::upper(Str::random(6)),
                'academic_year_id' => $request->academic_year_id,
                'intake' => $request->intake,
                'entry_level' => $request->entry_level,
                'study_mode' => $request->study_mode ?? 'Full Time',
                'status' => 'draft',
                'is_free_application' => 1,
                'step_basic_completed' => 1,
                'step_personal_completed' => 0,
                'step_contact_completed' => 0,
                'step_next_of_kin_completed' => 0,
                'step_academic_completed' => 0,
                'step_programs_completed' => 0,
                'step_documents_completed' => 0,
                'step_declaration_completed' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            $applicantName = $request->first_name . ' ' . $request->last_name;
            
            return redirect()->route('admission.officer.applications.edit', $applicationId)
                ->with('success', 'Application created successfully for ' . $applicantName);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create application: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create application: ' . $e->getMessage());
        }
    }
/**
 * Display draft applications only
 */
public function drafts(Request $request)
{
    $query = DB::table('applications')
        ->join('users', 'applications.user_id', '=', 'users.id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.status', 'draft')
        ->select(
            'applications.*',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as applicant_name"),
            'users.email as applicant_email',
            'academic_years.name as academic_year_name'
        );

    if ($request->academic_year) {
        $query->where('applications.academic_year_id', $request->academic_year);
    }

    if ($request->search) {
        $query->where(function($q) use ($request) {
            $q->where(DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))"), 'like', '%' . $request->search . '%')
              ->orWhere('users.email', 'like', '%' . $request->search . '%')
              ->orWhere('applications.application_number', 'like', '%' . $request->search . '%');
        });
    }

    $applications = $query->orderBy('applications.updated_at', 'desc')->paginate(20);
    
    $academicYears = DB::table('academic_years')->orderBy('name', 'desc')->get();

    return view('admission.applications.drafts', compact('applications', 'academicYears'));
}

    /**
     * Edit application - shows form for existing application
     */
    public function edit($id)
    {
        $application = DB::table('applications')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
            ->where('applications.id', $id)
            ->select(
                'applications.*',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as applicant_name"),
                'users.email as applicant_email',
                'academic_years.name as academic_year_name'
            )
            ->first();

        if (!$application) {
            abort(404, 'Application not found');
        }

        // Get all data needed
        $academicYears = DB::table('academic_years')->where('is_active', 1)->orderBy('name', 'desc')->get();
        
        $personal = DB::table('application_personal_infos')->where('application_id', $id)->first();
        $contact = DB::table('application_contacts')->where('application_id', $id)->first();
        $kin = DB::table('application_next_of_kins')->where('application_id', $id)->first();
        $academic = DB::table('application_academics')->where('application_id', $id)->first();
        $subjects = $academic ? DB::table('application_olevel_subjects')->where('application_academic_id', $academic->id)->get() : collect();
        $programChoice = DB::table('application_program_choices')->where('application_id', $id)->first();
        
        // Get eligible programmes
        $eligibleProgrammesList = collect();
        $nonEligibleProgrammesList = collect();
        
        if ($application->step_academic_completed == 1 && $academic) {
            try {
                $eligibleCollection = $this->eligibilityService->getEligibleProgrammes($id);
                
                $eligibleIds = [];
                foreach ($eligibleCollection as $prog) {
                    $eligibleIds[] = $prog['programme_id'];
                    $eligibleProgrammesList->push((object)[
                        'id' => $prog['programme_id'],
                        'name' => $prog['programme_name'],
                        'code' => $prog['programme_code'] ?? ''
                    ]);
                }
                
                $allProgrammes = DB::table('programmes')
                    ->where('is_active', 1)
                    ->where('status', 'active')
                    ->get();
                
                foreach ($allProgrammes as $programme) {
                    if (!in_array($programme->id, $eligibleIds)) {
                        $nonEligibleProgrammesList->push((object)[
                            'id' => $programme->id,
                            'name' => $programme->name,
                            'code' => $programme->code ?? ''
                        ]);
                    }
                }
                
                Log::info('Eligible programmes loaded: ' . $eligibleProgrammesList->count());
                
            } catch (\Exception $e) {
                Log::error('Failed to get eligible programmes: ' . $e->getMessage());
            }
        }
        
        $programs = DB::table('programmes')->where('is_active', 1)->orderBy('name')->get();
        
        // Calculate current step
        $currentStep = 1;
        if ($application->step_basic_completed ?? 0) $currentStep = 2;
        if ($application->step_personal_completed ?? 0) $currentStep = 3;
        if ($application->step_contact_completed ?? 0) $currentStep = 4;
        if ($application->step_next_of_kin_completed ?? 0) $currentStep = 5;
        if ($application->step_academic_completed ?? 0) $currentStep = 6;
        if ($application->step_programs_completed ?? 0) $currentStep = 7;

        return view('admission.applications.edit', compact(
            'application', 'academicYears', 'personal', 'contact', 'kin', 
            'academic', 'subjects', 'programChoice', 'eligibleProgrammesList', 
            'nonEligibleProgrammesList', 'programs', 'currentStep'
        ));
    }

    /**
     * Display application details (View only - no editing)
     */
    public function show($id)
    {
        $application = DB::table('applications')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
            ->where('applications.id', $id)
            ->select(
                'applications.*',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as applicant_name"),
                'users.email as applicant_email',
                'users.phone as applicant_phone',
                'academic_years.name as academic_year_name'
            )
            ->first();

        if (!$application) {
            abort(404, 'Application not found');
        }

        $personal = DB::table('application_personal_infos')->where('application_id', $id)->first();
        $contact = DB::table('application_contacts')->where('application_id', $id)->first();
        $kin = DB::table('application_next_of_kins')->where('application_id', $id)->first();
        $academic = DB::table('application_academics')->where('application_id', $id)->first();
        $subjects = $academic ? DB::table('application_olevel_subjects')->where('application_academic_id', $academic->id)->get() : collect();
        $programChoice = DB::table('application_program_choices')->where('application_id', $id)->first();
        $declaration = DB::table('application_declarations')->where('application_id', $id)->first();
        
        $selectedProgram = null;
        if ($application->selected_program_id) {
            $selectedProgram = DB::table('programmes')->where('id', $application->selected_program_id)->first();
        }
        
        $firstProgram = null;
        if ($programChoice && $programChoice->first_choice_program_id) {
            $firstProgram = DB::table('programmes')->where('id', $programChoice->first_choice_program_id)->first();
        }
        
        $secondProgram = null;
        if ($programChoice && $programChoice->second_choice_program_id) {
            $secondProgram = DB::table('programmes')->where('id', $programChoice->second_choice_program_id)->first();
        }
        
        $thirdProgram = null;
        if ($programChoice && $programChoice->third_choice_program_id) {
            $thirdProgram = DB::table('programmes')->where('id', $programChoice->third_choice_program_id)->first();
        }

        return view('admission.applications.show', compact(
            'application', 'personal', 'contact', 'kin', 'academic', 
            'subjects', 'programChoice', 'declaration', 'selectedProgram',
            'firstProgram', 'secondProgram', 'thirdProgram'
        ));
    }

    /**
     * Update application - AJAX endpoint for step saving
     */
    public function saveStep(Request $request)
    {
        $step = $request->step;
        $applicationId = $request->application_id;

        try {
            switch ($step) {
                case 1:
                    return $this->saveStep1($request);
                case 2:
                    return $this->savePersonal($request);
                case 3:
                    return $this->saveContact($request);
                case 4:
                    return $this->saveNextOfKin($request);
                case 5:
                    return $this->saveAcademics($request);
                case 6:
                    return $this->savePrograms($request);
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid step']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to save step: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function saveStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'intake' => 'required|in:March,September',
            'entry_level' => 'required|in:CSEE,ACSEE,Diploma,Degree,Mature',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::table('applications')->where('id', $request->application_id)->update([
            'academic_year_id' => $request->academic_year_id,
            'intake' => $request->intake,
            'entry_level' => $request->entry_level,
            'study_mode' => $request->study_mode,
            'is_free_application' => $request->is_free_application ? 1 : 0,
            'step_basic_completed' => 1,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Basic info saved', 'next_step' => 2]);
    }

    private function savePersonal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $this->upsert('application_personal_infos', 'application_id', $request->application_id, [
            'application_id' => $request->application_id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'nationality' => $request->nationality ?? 'Tanzanian',
            'marital_status' => $request->marital_status,
            'updated_at' => now(),
        ]);

        DB::table('applications')->where('id', $request->application_id)->update([
            'step_personal_completed' => 1,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Personal info saved', 'next_step' => 3]);
    }

    private function saveContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'region' => 'required|string',
            'district' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $this->upsert('application_contacts', 'application_id', $request->application_id, [
            'application_id' => $request->application_id,
            'phone' => $request->phone,
            'email' => $request->email,
            'region' => $request->region,
            'district' => $request->district,
            'updated_at' => now(),
        ]);

        DB::table('applications')->where('id', $request->application_id)->update([
            'step_contact_completed' => 1,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Contact info saved', 'next_step' => 4]);
    }

    private function saveNextOfKin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'guardian_name' => 'required|string',
            'guardian_phone' => 'required|string',
            'relationship' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $this->upsert('application_next_of_kins', 'application_id', $request->application_id, [
            'application_id' => $request->application_id,
            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,
            'relationship' => $request->relationship,
            'guardian_address' => $request->guardian_address,
            'updated_at' => now(),
        ]);

        DB::table('applications')->where('id', $request->application_id)->update([
            'step_next_of_kin_completed' => 1,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Next of kin saved', 'next_step' => 5]);
    }

    private function saveAcademics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'entry_level' => 'required|string',
            'csee_index_number' => 'required|string',
            'csee_school' => 'required|string',
            'csee_year' => 'required|string',
            'csee_division' => 'required|string',
            'csee_points' => 'required|string',
            'subjects' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $subjects = json_decode($request->subjects, true);

        DB::beginTransaction();

        try {
            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'entry_level' => $request->entry_level,
                    'updated_at' => now(),
                ]);

            $academic = DB::table('application_academics')
                ->where('application_id', $request->application_id)
                ->first();

            $academicData = [
                'application_id' => $request->application_id,
                'csee_index_number' => $request->csee_index_number,
                'csee_school' => $request->csee_school,
                'csee_year' => $request->csee_year,
                'csee_division' => $request->csee_division,
                'csee_points' => $request->csee_points,
                'acsee_index_number' => $request->acsee_index_number,
                'acsee_school' => $request->acsee_school,
                'acsee_year' => $request->acsee_year,
                'updated_at' => now(),
            ];

            if ($academic) {
                DB::table('application_academics')
                    ->where('application_id', $request->application_id)
                    ->update($academicData);
                $academicId = $academic->id;
            } else {
                $academicData['created_at'] = now();
                $academicId = DB::table('application_academics')->insertGetId($academicData);
            }

            DB::table('application_olevel_subjects')
                ->where('application_academic_id', $academicId)
                ->delete();

            foreach ($subjects as $subject) {
                DB::table('application_olevel_subjects')->insert([
                    'application_academic_id' => $academicId,
                    'subject' => $subject['name'],
                    'grade' => $subject['grade'],
                    'points' => $this->calculateGradePoints($subject['grade']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $eligibleProgrammes = $this->calculateEligibleProgrammes($request->application_id, $request->entry_level, $subjects, $request->csee_division, $request->csee_points);
            
            DB::table('application_eligible_programmes')
                ->where('application_id', $request->application_id)
                ->delete();

            foreach ($eligibleProgrammes as $programme) {
                DB::table('application_eligible_programmes')->insert([
                    'application_id' => $request->application_id,
                    'programme_id' => $programme['id'],
                    'priority' => $programme['priority'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'step_academic_completed' => 1,
                    'updated_at' => now(),
                ]);

            DB::commit();

            $programmeList = [];
            foreach ($eligibleProgrammes as $prog) {
                $programmeList[] = [
                    'id' => $prog['id'],
                    'name' => $prog['name'],
                    'code' => $prog['code'],
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Academic information saved successfully. ' . count($eligibleProgrammes) . ' programmes available.',
                'next_step' => 6,
                'eligible_programmes' => $programmeList,
                'eligible_count' => count($eligibleProgrammes),
                'has_eligible_programmes' => count($eligibleProgrammes) > 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save academics: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    private function calculateEligibleProgrammes($applicationId, $entryLevel, $subjects, $division, $points)
    {
        $programmes = DB::table('programmes')
            ->where('is_active', 1)
            ->where('status', 'active')
            ->get();
        
        $eligibleProgrammes = [];
        
        $subjectGrades = [];
        foreach ($subjects as $subject) {
            $subjectGrades[strtolower(trim($subject['name']))] = $subject['grade'];
        }
        
        foreach ($programmes as $programme) {
            $rules = DB::table('eligibility_rules')
                ->where('programme_id', $programme->id)
                ->first();
            
            if (!$rules) {
                $eligibleProgrammes[] = [
                    'id' => $programme->id,
                    'name' => $programme->name,
                    'code' => $programme->code ?? '',
                    'priority' => 1,
                ];
                continue;
            }
            
            if ($rules->entry_level && $rules->entry_level !== $entryLevel) {
                continue;
            }
            
            if ($rules->min_csee_division) {
                $divisionRank = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
                $requiredRank = $divisionRank[$rules->min_csee_division] ?? 4;
                $applicantRank = $divisionRank[$division] ?? 4;
                
                if ($applicantRank > $requiredRank) {
                    continue;
                }
            }
            
            if ($rules->min_csee_points && !empty($points)) {
                if (intval($points) > intval($rules->min_csee_points)) {
                    continue;
                }
            }
            
            $coreSubjects = [];
            if ($rules->core_subjects) {
                $coreSubjects = is_array($rules->core_subjects) ? $rules->core_subjects : json_decode($rules->core_subjects, true);
                if (!is_array($coreSubjects)) {
                    $coreSubjects = [];
                }
            }
            
            if (!empty($coreSubjects)) {
                $minGrade = $rules->min_subject_grade ?? 'D';
                $passingGrades = ['A', 'B', 'C', 'D'];
                if ($minGrade === 'C') $passingGrades = ['A', 'B', 'C'];
                if ($minGrade === 'B') $passingGrades = ['A', 'B'];
                if ($minGrade === 'A') $passingGrades = ['A'];
                
                $allCorePassed = true;
                foreach ($coreSubjects as $coreSubject) {
                    $subjectKey = strtolower(trim($coreSubject));
                    $grade = $subjectGrades[$subjectKey] ?? null;
                    
                    if (!$grade || !in_array($grade, $passingGrades)) {
                        $allCorePassed = false;
                        break;
                    }
                }
                
                if (!$allCorePassed) {
                    continue;
                }
            }
            
            $eligibleProgrammes[] = [
                'id' => $programme->id,
                'name' => $programme->name,
                'code' => $programme->code ?? '',
                'priority' => $rules->priority ?? 1,
            ];
        }
        
        usort($eligibleProgrammes, function($a, $b) {
            return ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99);
        });
        
        Log::info('Eligible programmes calculated: ' . count($eligibleProgrammes));
        
        return $eligibleProgrammes;
    }

    private function savePrograms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'first_choice_program_id' => 'required|exists:programmes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::table('application_program_choices')->updateOrInsert(
            ['application_id' => $request->application_id],
            [
                'first_choice_program_id' => $request->first_choice_program_id,
                'second_choice_program_id' => $request->second_choice_program_id,
                'third_choice_program_id' => $request->third_choice_program_id,
                'information_source' => $request->information_source,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('applications')->where('id', $request->application_id)->update([
            'selected_program_id' => $request->first_choice_program_id,
            'step_programs_completed' => 1,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Program choices saved', 'next_step' => 7]);
    }

    public function submit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'confirm_information' => 'required|accepted',
            'accept_terms' => 'required|accepted',
            'confirm_documents' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Please accept all declarations'], 422);
        }

        try {
            DB::table('applications')->where('id', $id)->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'step_declaration_completed' => 1,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'redirect_url' => route('admission.officer.applications.index')
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('application_personal_infos')->where('application_id', $id)->delete();
            DB::table('application_contacts')->where('application_id', $id)->delete();
            DB::table('application_next_of_kins')->where('application_id', $id)->delete();
            
            $academic = DB::table('application_academics')->where('application_id', $id)->first();
            if ($academic) {
                DB::table('application_olevel_subjects')->where('application_academic_id', $academic->id)->delete();
                DB::table('application_academics')->where('id', $academic->id)->delete();
            }
            
            DB::table('application_program_choices')->where('application_id', $id)->delete();
            DB::table('application_eligible_programmes')->where('application_id', $id)->delete();
            DB::table('applications')->where('id', $id)->delete();

            return redirect()->route('admission.officer.applications.index')
                ->with('success', 'Application deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete application');
        }
    }

    public function fetchNectaResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'index_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Index number required'], 422);
        }

        try {
            $results = $this->nectaService->fetchResults($request->index_number);
            
            if ($results['status'] === 'found') {
                return response()->json([
                    'success' => true,
                    'data' => $results['data']
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $results['message'] ?? 'No results found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch results'], 500);
        }
    }

    public function getEligibleProgrammes($id)
    {
        $eligibleIds = DB::table('application_eligible_programmes')
            ->where('application_id', $id)
            ->pluck('programme_id')
            ->toArray();
            
        $programmes = DB::table('programmes')
            ->whereIn('id', $eligibleIds)
            ->get();

        return response()->json([
            'success' => true,
            'programmes' => $programmes,
            'count' => $programmes->count()
        ]);
    }

    public function searchApplicant(Request $request)
    {
        $search = $request->get('q', '');
        
        if (empty($search)) {
            return response()->json(['found' => false]);
        }
        
        $applicant = DB::table('users')
            ->where('user_type', 'applicant')
            ->where(function($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'gender')
            ->first();
        
        if ($applicant) {
            $activeYear = DB::table('academic_years')->where('is_active', 1)->first();
            $existingApp = null;
            $existingYear = null;
            
            if ($activeYear) {
                $existingApp = DB::table('applications')
                    ->where('user_id', $applicant->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->first();
                    
                if ($existingApp) {
                    $existingYear = DB::table('academic_years')->where('id', $existingApp->academic_year_id)->value('name');
                }
            }
            
            return response()->json([
                'found' => true,
                'applicant' => $applicant,
                'has_application' => !is_null($existingApp),
                'existing_academic_year' => $existingYear
            ]);
        }
        
        return response()->json(['found' => false]);
    }

    private function upsert(string $table, string $key, $value, array $data): void
    {
        if (DB::table($table)->where($key, $value)->exists()) {
            DB::table($table)->where($key, $value)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table($table)->insert($data);
        }
    }

    private function calculateGradePoints($grade)
    {
        $gradeMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7];
        $grade = strtoupper(trim($grade));
        return $gradeMap[$grade] ?? 7;
    }
}
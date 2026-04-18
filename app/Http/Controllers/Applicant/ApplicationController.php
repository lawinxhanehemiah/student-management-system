<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\EligibilityService;
use App\Services\NectaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationController extends Controller
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
     * Start new application
     */
    public function start()
    {
        $user = Auth::user();

        $activeYear = DB::table('academic_years')->where('is_active', 1)->first();
        if (!$activeYear) {
            $activeYear = DB::table('academic_years')->latest('id')->first();
        }
        if (!$activeYear) {
            $activeYear = $this->createDefaultAcademicYear();
        }

        $existingApplication = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        if ($existingApplication) {
            return $this->handleExistingApplication($existingApplication);
        }

        $draftApplication = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if ($draftApplication) {
            return redirect()->route('applicant.application.form', $draftApplication->id)
                ->with('info', 'You have an unfinished draft. Please complete it first.');
        }

        $applicationId = $this->createNewApplication($user, $activeYear);
        return redirect()->route('applicant.application.form', $applicationId)
            ->with('success', 'Application started successfully.');
    }

    /**
     * Show application form
     */
    public function showForm($id = null)
    {
        try {
            if (!$id) {
                return redirect()->route('applicant.application.start');
            }

            $application = $this->verifyOwnership($id);

            if (!in_array($application->status, ['draft', 'submitted'])) {
                return redirect()->route('applicant.application.show', $id)
                    ->with('warning', 'Application is already processed and cannot be edited.');
            }

            $newerDraft = DB::table('applications')
                ->where('user_id', Auth::id())
                ->where('academic_year_id', $application->academic_year_id)
                ->where('status', 'draft')
                ->where('id', '!=', $id)
                ->first();

            if ($newerDraft) {
                return redirect()->route('applicant.application.form', $newerDraft->id)
                    ->with('info', 'Redirected to your latest draft application.');
            }

            $data = $this->getApplicationFormData($id, $application);
            return view('applicant.application-form', $data);

        } catch (\Exception $e) {
            Log::error('Error loading application form: ' . $e->getMessage());
            return redirect()->route('applicant.dashboard')
                ->with('error', 'An error occurred while loading the application form.');
        }
    }

    /**
     * Save Step 1 - Basic Info
     */
    public function saveStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'intake' => 'required|in:March,September',
            'entry_level' => 'required|in:CSEE,ACSEE,Diploma,Degree,Mature',
            'study_mode' => 'nullable|string|max:50',
            'is_free_application' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);

            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'academic_year_id' => $request->academic_year_id,
                    'intake' => $request->intake,
                    'entry_level' => $request->entry_level,
                    'study_mode' => $request->study_mode,
                    'is_free_application' => $request->is_free_application ? 1 : 0,
                    'step_basic_completed' => 1,
                    'updated_at' => now(),
                ]);

            return $this->successResponse('Basic information saved successfully', 2);

        } catch (\Exception $e) {
            Log::error('Failed to save basic info: ' . $e->getMessage());
            return $this->errorResponse('Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Save Step 2 - Personal Info
     */
    public function savePersonal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date',
            'nationality' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);
            $data = $validator->validated();
            $data['updated_at'] = now();

            $this->upsert('application_personal_infos', 'application_id', $data['application_id'], $data);

            DB::table('applications')
                ->where('id', $data['application_id'])
                ->update([
                    'step_personal_completed' => 1,
                    'updated_at' => now(),
                ]);

            return $this->successResponse('Personal information saved successfully', 3);

        } catch (\Exception $e) {
            Log::error('Failed to save personal info: ' . $e->getMessage());
            return $this->errorResponse('Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Save Step 3 - Contact Info
     */
    public function saveContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'region' => 'required|string|max:100',
            'district' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);
            $data = $validator->validated();
            $data['updated_at'] = now();

            $this->upsert('application_contacts', 'application_id', $data['application_id'], $data);

            DB::table('applications')
                ->where('id', $data['application_id'])
                ->update([
                    'step_contact_completed' => 1,
                    'updated_at' => now(),
                ]);

            return $this->successResponse('Contact information saved successfully', 4);

        } catch (\Exception $e) {
            Log::error('Failed to save contact info: ' . $e->getMessage());
            return $this->errorResponse('Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Save Step 4 - Next of Kin
     */
    public function saveNextOfKin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'guardian_name' => 'required|string|max:200',
            'guardian_phone' => 'required|string|max:20',
            'relationship' => 'required|string|max:100',
            'guardian_address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);
            $data = $validator->validated();
            $data['updated_at'] = now();

            $this->upsert('application_next_of_kins', 'application_id', $data['application_id'], $data);

            DB::table('applications')
                ->where('id', $data['application_id'])
                ->update([
                    'step_next_of_kin_completed' => 1,
                    'updated_at' => now(),
                ]);

            return $this->successResponse('Next of kin saved successfully', 5);

        } catch (\Exception $e) {
            Log::error('Failed to save next of kin: ' . $e->getMessage());
            return $this->errorResponse('Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Save Step 5 - Academics
     */
    public function saveAcademics(Request $request)
    {
        $data = $this->prepareAcademicData($request);

        $validator = Validator::make($data, [
            'application_id' => 'required|exists:applications,id',
            'entry_level' => 'required|in:CSEE,ACSEE,Diploma,Degree,Mature',
            'csee_index_number' => 'required|string|max:20',
            'csee_school' => 'required|string|max:200',
            'csee_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'csee_division' => 'required|in:I,II,III,IV',
            'csee_points' => 'required|integer|min:7|max:36',
            'acsee_index_number' => 'nullable|string|max:20',
            'acsee_school' => 'nullable|string|max:200',
            'acsee_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'subjects' => 'required|array|min:4|max:12',
            'subjects.*.name' => 'required|string|max:100',
            'subjects.*.grade' => 'required|string|max:2|in:A,B,C,D,E,F,S',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed: ' . $validator->errors()->first(), $validator->errors());
        }

        $validatedData = $validator->validated();
        $subjects = $validatedData['subjects'];

        DB::beginTransaction();

        try {
            $this->verifyOwnership($validatedData['application_id']);

            DB::table('applications')
                ->where('id', $validatedData['application_id'])
                ->update([
                    'entry_level' => $validatedData['entry_level'],
                    'updated_at' => now(),
                ]);

            $academicId = $this->saveAcademicRecord($validatedData);
            $this->saveSubjects($academicId, $subjects);

            $academicDataForEligibility = $this->buildAcademicDataObject($validatedData);
            $eligibleProgrammes = $this->eligibilityService->getEligibleProgrammes(
                $validatedData['application_id'],
                $academicDataForEligibility
            );

            $this->storeEligibleProgrammes($validatedData['application_id'], $eligibleProgrammes);
            $this->checkExistingProgramChoices($validatedData['application_id']);

            DB::table('applications')
                ->where('id', $validatedData['application_id'])
                ->update(['step_academic_completed' => 1]);

            DB::commit();

            $programmeList = $this->formatProgrammeList($eligibleProgrammes);

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
            return $this->errorResponse('Failed to save academic information: ' . $e->getMessage());
        }
    }

    /**
     * Save Step 6 - Program Choices
     */
    public function savePrograms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'first_choice_program_id' => 'required|exists:programmes,id',
            'second_choice_program_id' => 'nullable|exists:programmes,id',
            'third_choice_program_id' => 'nullable|exists:programmes,id',
            'information_source' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);

            $eligibleIds = $this->getEligibleProgrammeIds($request->application_id);
            $invalidChoices = $this->validateProgramChoices($request, $eligibleIds);

            if (!empty($invalidChoices)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected programmes are not eligible based on your academic results.',
                    'invalid_choices' => $invalidChoices
                ], 422);
            }

            $this->saveProgramChoices($request);

            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'selected_program_id' => $request->first_choice_program_id,
                    'step_programs_completed' => 1,
                    'updated_at' => now(),
                ]);

            return $this->successResponse('Program choices saved successfully', 7);

        } catch (\Exception $e) {
            Log::error('Failed to save program choices: ' . $e->getMessage());
            return $this->errorResponse('Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Save Step 7 - Documents
     */
    public function saveDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);

            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'step_documents_completed' => 1,
                    'updated_at' => now(),
                ]);

            return $this->successResponse('Documents step completed', 8);

        } catch (\Exception $e) {
            Log::error('Failed to complete documents step: ' . $e->getMessage());
            return $this->errorResponse('Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Submit application
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'confirm_information' => 'required|accepted',
            'accept_terms' => 'required|accepted',
            'confirm_documents' => 'required|accepted',
            'allow_data_sharing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        DB::beginTransaction();

        try {
            $application = $this->verifyOwnership($request->application_id);

            if (!$this->allStepsCompleted($application)) {
                $missingSteps = $this->getMissingSteps($application);
                throw new \Exception('Please complete: ' . implode(', ', $missingSteps));
            }

            $this->saveDeclaration($request);

            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'step_declaration_completed' => 1,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully. Please wait for approval.',
                'application_id' => $request->application_id,
                'redirect_url' => route('applicant.dashboard', $request->application_id)
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Application submission failed', [
                'error' => $e->getMessage(),
                'application_id' => $request->application_id,
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Fetch NECTA results
     */
    public function fetchNectaResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'index_number' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
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
                'message' => $results['message'] ?? 'No results found for this index number.',
                'requires_upload' => $results['requires_upload'] ?? false
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to fetch NECTA results: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch results. Please try again.');
        }
    }

    /**
     * Get eligible programmes via AJAX
     */
    public function getEligibleProgrammesAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors());
        }

        try {
            $this->verifyOwnership($request->application_id);

            $eligibleProgrammes = DB::table('application_eligible_programmes as aep')
                ->join('programmes as p', 'aep.programme_id', '=', 'p.id')
                ->where('aep.application_id', $request->application_id)
                ->select('p.id', 'p.name', 'p.code')
                ->orderBy('p.name')
                ->get();

            if ($eligibleProgrammes->isEmpty()) {
                $eligibleCollection = $this->eligibilityService->getEligibleProgrammes($request->application_id);
                $eligibleProgrammes = $this->convertToCollection($eligibleCollection);
            }

            return response()->json([
                'success' => true,
                'programmes' => $eligibleProgrammes,
                'count' => $eligibleProgrammes->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get eligible programmes: ' . $e->getMessage());
            return $this->errorResponse('Failed to load eligible programmes');
        }
    }

    /**
     * Cancel draft
     */
    public function cancelDraft($id)
    {
        try {
            $application = DB::table('applications')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'draft')
                ->first();

            if (!$application) {
                return redirect()->route('applicant.dashboard')
                    ->with('error', 'Draft application not found');
            }

            DB::table('applications')->where('id', $id)->delete();

            return redirect()->route('applicant.dashboard')
                ->with('success', 'Draft application cancelled');

        } catch (\Exception $e) {
            Log::error('Failed to cancel draft: ' . $e->getMessage());
            return redirect()->route('applicant.dashboard')
                ->with('error', 'Failed to cancel draft application.');
        }
    }

    /**
     * Get review summary
     */
    public function getReviewSummary($id)
    {
        try {
            $this->verifyOwnership($id);
            $data = $this->getReviewData($id);
            return view('applicant.partials.review-summary', $data);

        } catch (\Exception $e) {
            Log::error('Failed to load review summary: ' . $e->getMessage());
            return '<div class="alert alert-danger">Failed to load review: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Show application details
     */
    public function show($id)
    {
        try {
            $application = DB::table('applications')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$application) {
                abort(404, 'Application not found');
            }

            $data = $this->getApplicationDetailsData($id, $application);
            return view('applicant.application-details', $data);

        } catch (\Exception $e) {
            Log::error('Failed to load application details: ' . $e->getMessage());
            return redirect()->route('applicant.dashboard')
                ->with('error', 'Failed to load application details.');
        }
    }

    /**
     * Check existing application
     */
    public function checkExistingApplication()
    {
        try {
            $user = Auth::user();
            $activeYear = DB::table('academic_years')->where('is_active', 1)->first();

            if (!$activeYear) {
                return response()->json(['has_application' => false, 'message' => 'No active academic year found']);
            }

            $existingApplication = DB::table('applications')
                ->where('user_id', $user->id)
                ->where('academic_year_id', $activeYear->id)
                ->first();

            if ($existingApplication) {
                return response()->json([
                    'has_application' => true,
                    'application_id' => $existingApplication->id,
                    'status' => $existingApplication->status,
                    'can_edit' => $existingApplication->status === 'draft',
                ]);
            }

            return response()->json(['has_application' => false, 'message' => 'No existing application found']);

        } catch (\Exception $e) {
            Log::error('Failed to check existing application: ' . $e->getMessage());
            return response()->json(['has_application' => false, 'message' => 'Failed to check application status'], 500);
        }
    }

    /* =====================================================
       PRIVATE HELPER METHODS
    ====================================================== */

    private function verifyOwnership($applicationId)
    {
        $application = DB::table('applications')
            ->where('id', $applicationId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$application) {
            throw new \Exception('Unauthorized access to application.');
        }

        return $application;
    }

    private function upsert(string $table, string $key, $value, array $data): void
    {
        $data[$key] = $value;

        if (DB::table($table)->where($key, $value)->exists()) {
            DB::table($table)->where($key, $value)->update($data);
        } else {
            if (!isset($data['created_at'])) {
                $data['created_at'] = now();
            }
            DB::table($table)->insert($data);
        }
    }

    private function calculateGradePoints($grade)
    {
        $gradeMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7];
        $grade = strtoupper(trim($grade));
        return $gradeMap[$grade] ?? 7;
    }

    private function getCurrentStep($application)
    {
        if (($application->step_basic_completed ?? 0) == 0) return 1;
        if ($application->step_personal_completed == 0) return 2;
        if ($application->step_contact_completed == 0) return 3;
        if ($application->step_next_of_kin_completed == 0) return 4;
        if ($application->step_academic_completed == 0) return 5;
        if ($application->step_programs_completed == 0) return 6;
        return 7;
    }

    private function allStepsCompleted($application)
    {
        return ($application->step_basic_completed ?? 0) == 1 &&
               $application->step_personal_completed == 1 &&
               $application->step_contact_completed == 1 &&
               $application->step_next_of_kin_completed == 1 &&
               $application->step_academic_completed == 1 &&
               $application->step_programs_completed == 1;
    }

    private function getMissingSteps($application)
    {
        $missing = [];
        if (($application->step_basic_completed ?? 0) != 1) $missing[] = 'Basic Information';
        if ($application->step_personal_completed != 1) $missing[] = 'Personal Information';
        if ($application->step_contact_completed != 1) $missing[] = 'Contact Information';
        if ($application->step_next_of_kin_completed != 1) $missing[] = 'Next of Kin';
        if ($application->step_academic_completed != 1) $missing[] = 'Academic Information';
        if ($application->step_programs_completed != 1) $missing[] = 'Program Selection';
        return $missing;
    }

    private function createDefaultAcademicYear()
    {
        $id = DB::table('academic_years')->insertGetId([
            'name' => now()->year . '/' . (now()->year + 1),
            'is_active' => 1,
            'start_date' => now()->year . '-09-01',
            'end_date' => (now()->year + 1) . '-08-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return DB::table('academic_years')->find($id);
    }

    private function handleExistingApplication($application)
    {
        if ($application->status === 'draft') {
            return redirect()->route('applicant.application.form', $application->id)
                ->with('info', 'Continue your draft application.');
        }
        return redirect()->route('applicant.application.show', $application->id)
            ->with('info', 'You already have an application for this academic year.');
    }

    private function createNewApplication($user, $activeYear)
    {
        return DB::table('applications')->insertGetId([
            'user_id' => $user->id,
            'application_number' => 'APP-' . date('Y') . '-' . Str::upper(Str::random(6)),
            'academic_year_id' => $activeYear->id,
            'intake' => 'March',
            'entry_level' => 'CSEE',
            'status' => 'draft',
            'is_free_application' => 1,
            'step_basic_completed' => 0,
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
    }

   private function getApplicationFormData($id, $application)
{
    $academicYear = DB::table('academic_years')->where('id', $application->academic_year_id)->first();
    $academicYears = DB::table('academic_years')->where('is_active', 1)->orderBy('name', 'desc')->get();
    $programs = DB::table('programmes')->where('is_active', 1)->where('status', 'active')->orderBy('name')->get();

    $personal = DB::table('application_personal_infos')->where('application_id', $id)->first();
    $contact = DB::table('application_contacts')->where('application_id', $id)->first();
    $kin = DB::table('application_next_of_kins')->where('application_id', $id)->first();
    $academic = DB::table('application_academics')->where('application_id', $id)->first();

    $subjects = $academic ? DB::table('application_olevel_subjects')->where('application_academic_id', $academic->id)->whereNull('deleted_at')->get() : collect();
    $programChoice = DB::table('application_program_choices')->where('application_id', $id)->first();
    
    // SIMPLE current step calculation
    $currentStep = 1;
    if (($application->step_basic_completed ?? 0) == 1) $currentStep = 2;
    if (($application->step_personal_completed ?? 0) == 1) $currentStep = 3;
    if (($application->step_contact_completed ?? 0) == 1) $currentStep = 4;
    if (($application->step_next_of_kin_completed ?? 0) == 1) $currentStep = 5;
    if (($application->step_academic_completed ?? 0) == 1) $currentStep = 6;
    if (($application->step_programs_completed ?? 0) == 1) $currentStep = 7;
    
    $eligibleProgrammesList = collect();
    $nonEligibleProgrammesList = collect();
    
    // Get eligible programmes from service
    if ($application->step_academic_completed == 1) {
        $eligibleCollection = $this->eligibilityService->getEligibleProgrammes($id);
        
        // Get all programmes
        $allProgrammes = DB::table('programmes')
            ->where('is_active', 1)
            ->where('status', 'active')
            ->get();
        
        $eligibleIds = [];
        
        foreach ($eligibleCollection as $prog) {
            $eligibleIds[] = $prog['programme_id'];
            $eligibleProgrammesList->push((object)[
                'id' => $prog['programme_id'],
                'name' => $prog['programme_name'],
                'code' => $prog['programme_code']
            ]);
        }
        
        // Get non-eligible programmes
        foreach ($allProgrammes as $programme) {
            if (!in_array($programme->id, $eligibleIds)) {
                $nonEligibleProgrammesList->push((object)[
                    'id' => $programme->id,
                    'name' => $programme->name,
                    'code' => $programme->code
                ]);
            }
        }
    }

    return compact('application', 'academicYear', 'academicYears', 'programs', 'personal', 'contact', 'kin', 'academic', 'subjects', 'programChoice', 'currentStep', 'eligibleProgrammesList', 'nonEligibleProgrammesList');
}
    private function prepareAcademicData($request)
    {
        $data = $request->all();
        if (isset($data['subjects']) && is_string($data['subjects'])) {
            $decoded = json_decode($data['subjects'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['subjects'] = $decoded;
            }
        }
        return $data;
    }

    private function saveAcademicRecord($data)
    {
        $academic = DB::table('application_academics')->where('application_id', $data['application_id'])->first();

        $academicData = [
            'application_id' => $data['application_id'],
            'csee_index_number' => $data['csee_index_number'],
            'csee_school' => $data['csee_school'],
            'csee_year' => $data['csee_year'],
            'csee_division' => $data['csee_division'],
            'csee_points' => $data['csee_points'],
            'acsee_index_number' => $data['acsee_index_number'] ?? null,
            'acsee_school' => $data['acsee_school'] ?? null,
            'acsee_year' => $data['acsee_year'] ?? null,
            'updated_at' => now(),
        ];

        if ($academic) {
            DB::table('application_academics')->where('application_id', $data['application_id'])->update($academicData);
            return $academic->id;
        }

        $academicData['created_at'] = now();
        return DB::table('application_academics')->insertGetId($academicData);
    }

    private function saveSubjects($academicId, $subjects)
    {
        DB::table('application_olevel_subjects')->where('application_academic_id', $academicId)->delete();

        $subjectData = [];
        foreach ($subjects as $subject) {
            $subjectData[] = [
                'application_academic_id' => $academicId,
                'subject' => trim($subject['name']),
                'grade' => strtoupper(trim($subject['grade'])),
                'points' => $this->calculateGradePoints($subject['grade']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($subjectData)) {
            DB::table('application_olevel_subjects')->insert($subjectData);
        }
    }

    private function buildAcademicDataObject($data)
    {
        $academicData = new \stdClass();
        $academicData->application_id = $data['application_id'];
        $academicData->csee_index_number = $data['csee_index_number'];
        $academicData->csee_school = $data['csee_school'];
        $academicData->csee_year = $data['csee_year'];
        $academicData->csee_division = $data['csee_division'];
        $academicData->csee_points = $data['csee_points'];
        $academicData->acsee_index_number = $data['acsee_index_number'] ?? null;
        $academicData->acsee_school = $data['acsee_school'] ?? null;
        $academicData->acsee_year = $data['acsee_year'] ?? null;
        $academicData->entry_level = $data['entry_level'];
        return $academicData;
    }

    private function storeEligibleProgrammes($applicationId, $eligibleProgrammes)
    {
        DB::table('application_eligible_programmes')->where('application_id', $applicationId)->delete();

        foreach ($eligibleProgrammes as $programme) {
            DB::table('application_eligible_programmes')->insert([
                'application_id' => $applicationId,
                'programme_id' => $programme['programme_id'],
                'priority' => $programme['priority'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function checkExistingProgramChoices($applicationId)
    {
        $programChoices = DB::table('application_program_choices')->where('application_id', $applicationId)->first();

        if ($programChoices) {
            $eligibleIds = DB::table('application_eligible_programmes')->where('application_id', $applicationId)->pluck('programme_id')->toArray();
            $choices = [$programChoices->first_choice_program_id, $programChoices->second_choice_program_id, $programChoices->third_choice_program_id];

            foreach ($choices as $choice) {
                if ($choice && !in_array($choice, $eligibleIds)) {
                    DB::table('applications')->where('id', $applicationId)->update(['step_programs_completed' => 0]);
                    break;
                }
            }
        }
    }

    private function formatProgrammeList($eligibleProgrammes)
    {
        $programmeList = [];
        foreach ($eligibleProgrammes as $prog) {
            $programmeList[] = [
                'id' => $prog['programme_id'],
                'name' => $prog['programme_name'],
                'code' => $prog['programme_code'],
                'entry_level' => $prog['entry_level'],
            ];
        }
        return $programmeList;
    }

    private function getEligibleProgrammeIds($applicationId)
    {
        $eligibleIds = DB::table('application_eligible_programmes')->where('application_id', $applicationId)->pluck('programme_id')->toArray();

        if (empty($eligibleIds)) {
            $eligibleProgrammes = $this->eligibilityService->getEligibleProgrammes($applicationId);
            $eligibleIds = $eligibleProgrammes->pluck('programme_id')->toArray();
        }
        return $eligibleIds;
    }

    private function validateProgramChoices($request, $eligibleIds)
    {
        $choices = [$request->first_choice_program_id, $request->second_choice_program_id, $request->third_choice_program_id];
        $invalidChoices = [];

        foreach ($choices as $choice) {
            if ($choice && !in_array($choice, $eligibleIds)) {
                $invalidChoices[] = $choice;
            }
        }
        return $invalidChoices;
    }

    private function saveProgramChoices($request)
    {
        DB::table('application_program_choices')->updateOrInsert(
            ['application_id' => $request->application_id],
            [
                'first_choice_program_id' => $request->first_choice_program_id,
                'second_choice_program_id' => $request->second_choice_program_id,
                'third_choice_program_id' => $request->third_choice_program_id,
                'information_source' => $request->information_source,
                'updated_at' => now(),
                'created_at' => DB::raw('IFNULL(created_at, NOW())'),
            ]
        );
    }

    private function saveDeclaration($request)
    {
        $this->upsert('application_declarations', 'application_id', $request->application_id, [
            'application_id' => $request->application_id,
            'confirm_information' => 1,
            'accept_terms' => 1,
            'confirm_documents' => 1,
            'allow_data_sharing' => $request->allow_data_sharing ? 1 : 0,
            'declared_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function convertToCollection($eligibleCollection)
    {
        $collection = collect();
        foreach ($eligibleCollection as $prog) {
            $collection->push((object)[
                'id' => $prog['programme_id'],
                'name' => $prog['programme_name'],
                'code' => $prog['programme_code'],
            ]);
        }
        return $collection;
    }

    private function getReviewData($id)
    {
        $application = DB::table('applications')
            ->select('applications.*', 'academic_years.name as academic_year_name')
            ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
            ->where('applications.id', $id)
            ->first();

        $personal = DB::table('application_personal_infos')->where('application_id', $id)->first();
        $contact = DB::table('application_contacts')->where('application_id', $id)->first();
        $kin = DB::table('application_next_of_kins')->where('application_id', $id)->first();
        $academic = DB::table('application_academics')->where('application_id', $id)->first();

        $subjects = $academic ? DB::table('application_olevel_subjects')->where('application_academic_id', $academic->id)->whereNull('deleted_at')->get() : collect();
        $programChoice = DB::table('application_program_choices')->where('application_id', $id)->first();

        $firstProgram = $programChoice && $programChoice->first_choice_program_id ? DB::table('programmes')->find($programChoice->first_choice_program_id) : null;
        $secondProgram = $programChoice && $programChoice->second_choice_program_id ? DB::table('programmes')->find($programChoice->second_choice_program_id) : null;
        $thirdProgram = $programChoice && $programChoice->third_choice_program_id ? DB::table('programmes')->find($programChoice->third_choice_program_id) : null;
        $selectedProgram = $application->selected_program_id ? DB::table('programmes')->find($application->selected_program_id) : null;

        return compact('application', 'personal', 'contact', 'kin', 'academic', 'subjects', 'programChoice', 'firstProgram', 'secondProgram', 'thirdProgram', 'selectedProgram');
    }

    private function getApplicationDetailsData($id, $application)
    {
        $personal = DB::table('application_personal_infos')->where('application_id', $id)->first();
        $contact = DB::table('application_contacts')->where('application_id', $id)->first();
        $kin = DB::table('application_next_of_kins')->where('application_id', $id)->first();
        $academic = DB::table('application_academics')->where('application_id', $id)->first();

        $subjects = $academic ? DB::table('application_olevel_subjects')->where('application_academic_id', $academic->id)->whereNull('deleted_at')->get() : collect();
        $programChoice = DB::table('application_program_choices')->where('application_id', $id)->first();
        $declaration = DB::table('application_declarations')->where('application_id', $id)->first();
        $selectedProgram = $application->selected_program_id ? DB::table('programmes')->find($application->selected_program_id) : null;
        $academicYear = DB::table('academic_years')->where('id', $application->academic_year_id)->first();

        return compact('application', 'personal', 'contact', 'kin', 'academic', 'subjects', 'programChoice', 'declaration', 'selectedProgram', 'academicYear');
    }

    private function successResponse($message, $nextStep = null)
    {
        $response = ['success' => true, 'message' => $message];
        if ($nextStep !== null) {
            $response['next_step'] = $nextStep;
        }
        return response()->json($response);
    }

    private function errorResponse($message, $errors = null)
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        return response()->json($response, 422);
    }

    
}
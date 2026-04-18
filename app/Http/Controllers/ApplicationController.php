<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\NectaService;

class ApplicationController extends Controller
{
    public function __construct(
        private NectaService $nectaService
    ) {}

    /* =====================================================
       START / LOAD APPLICATION
    ====================================================== */
    public function start()
    {
        $activeYear = DB::table('academic_years')
            ->where('is_active', 1)
            ->first()
            ?? DB::table('academic_years')->latest('id')->first();

        if (!$activeYear) {
            $id = DB::table('academic_years')->insertGetId([
                'name' => now()->year . '/' . (now()->year + 1),
                'status' => 'active',
                'is_active' => 1,
                'start_date' => now()->year . '-09-01',
                'end_date' => (now()->year + 1) . '-08-31',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $activeYear = DB::table('academic_years')->find($id);
        }

        $application = DB::table('applications')
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->first();

        if (!$application) {
            $id = DB::table('applications')->insertGetId([
                'user_id' => Auth::id(),
                'application_number' => 'APP-' . now()->year . '-' . Str::upper(Str::random(6)),
                'academic_year_id' => $activeYear->id,
                'intake' => 'March',
                'entry_level' => 'CSEE',
                'status' => 'draft',
                'is_free_application' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $application = DB::table('applications')->find($id);
        }

        return view('applications.apply-form', compact('application'));
    }

    /* =====================================================
       STEP 1 – APPLICATION META
    ====================================================== */
    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            
            'entry_level' => 'required',
            'intake' => 'required',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_free_application' => 'required|boolean',
            'fee_waiver_reason' => 'nullable|string'
        ]);

        $data['step_personal_completed'] = true;
        $data['updated_at'] = now();

        DB::table('applications')->where('id', $request->application_id)->update($data);

        return response()->json(['success' => true]);
    }

    /* =====================================================
       STEP 2 – PERSONAL INFO
    ====================================================== */
    public function savePersonal(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' => 'required|date',
            'nationality' => 'nullable|string',
            'national_id' => 'nullable|string',
            'marital_status' => 'nullable|string',
        ]);

        $this->upsert('application_personal_infos', 'application_id', $data['application_id'], $data);

        DB::table('applications')
            ->where('id', $data['application_id'])
            ->update([
                'step_personal_completed' => true,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /* =====================================================
       STEP 3 – CONTACT INFO
    ====================================================== */
    public function saveContact(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'phone' => 'required|string',
            'region' => 'required|string',
            'district' => 'required|string',
            'address' => 'nullable|string',
        ]);

        $this->upsert('application_contacts', 'application_id', $data['application_id'], $data);

        DB::table('applications')
            ->where('id', $data['application_id'])
            ->update([
                'step_contact_completed' => true,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /* =====================================================
       STEP 4 – NEXT OF KIN
    ====================================================== */
    public function saveNextOfKin(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'guardian_name' => 'required|string',
            'guardian_phone' => 'required|string',
            'relationship' => 'nullable|string',
            'guardian_address' => 'nullable|string',
        ]);

        $this->upsert('application_next_of_kins', 'application_id', $data['application_id'], $data);

        DB::table('applications')
            ->where('id', $data['application_id'])
            ->update([
                'step_next_of_kin_completed' => true,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /* =====================================================
       STEP 5 – ACADEMICS + SUBJECTS
    ====================================================== */
    public function saveAcademics(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'csee_index_number' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $academic = DB::table('application_academics')
                ->where('application_id', $request->application_id)
                ->first();

            $data = [
                'application_id' => $request->application_id,
                'csee_index_number' => $request->csee_index_number,
                'csee_school' => $request->csee_school_manual ?? $request->csee_school,
                'csee_year' => $request->csee_year_manual ?? $request->csee_year,
                'csee_division' => $request->csee_division_manual ?? $request->csee_division,
                'csee_points' => $request->csee_points_manual ?? $request->csee_points,
                'acsee_school' => $request->acsee_school,
                'acsee_index_number' => $request->acsee_index_number,
                'acsee_year' => $request->acsee_year,
                'updated_at' => now(),
            ];

            $academicId = $academic
                ? tap(
                    DB::table('application_academics')
                        ->where('application_id', $request->application_id)
                        ->update($data),
                    fn () => $academic->id
                )
                : DB::table('application_academics')->insertGetId($data);

            DB::table('application_olevel_subjects')
                ->where('application_academic_id', $academicId)
                ->delete();

            $subjects = array_merge(
                $request->olevel_subjects ?? [],
                $request->manual_subjects ?? []
            );

            foreach ($subjects as &$subject) {
                $subject['application_academic_id'] = $academicId;
                $subject['points'] = $this->calculateGradePoints($subject['grade'] ?? 'F');
                $subject['created_at'] = now();
                $subject['updated_at'] = now();
            }

            if ($subjects) {
                DB::table('application_olevel_subjects')->insert($subjects);
            }

            DB::table('applications')
                ->where('id', $request->application_id)
                ->update([
                    'step_academic_completed' => true,
                    'updated_at' => now(),
                ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save academic details'
            ], 500);
        }
    }

    /* =====================================================
       STEP 6 – PROGRAM CHOICES
    ====================================================== */
    public function savePrograms(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'first_choice_program_id' => 'required|exists:programmes,id',
            'second_choice_program_id' => 'nullable|exists:programmes,id',
            'third_choice_program_id' => 'nullable|exists:programmes,id',
            'information_source' => 'nullable|string|max:255',
            
        ]);

        $this->upsert('application_program_choices', 'application_id', $data['application_id'], $data);

        DB::table('applications')
            ->where('id', $data['application_id'])
            ->update([
                'step_programs_completed' => true,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /* =====================================================
       STEP 7 – DECLARATION & SUBMIT
    ====================================================== */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'confirm_information' => 'required|boolean',
            'accept_terms' => 'required|boolean',
            'confirm_documents' => 'required|boolean',
            'allow_data_sharing' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $this->upsert('application_declarations', 'application_id', $data['application_id'], [
                ...$data,
                'declared_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('applications')
                ->where('id', $data['application_id'])
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'step_declaration_completed' => true,
                    'updated_at' => now(),
                ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Submission failed'
            ], 500);
        }
    }

    /* =====================================================
       NECTA LOOKUP (API SAFE)
    ====================================================== */
    public function fetchNectaResults(Request $request)
    {
        $request->validate([
            'index_number' => 'required|string'
        ]);

        return response()->json(
            $this->nectaService->getResults($request->index_number)
        );
    }

    /* =====================================================
       HELPERS
    ====================================================== */
    private function upsert(string $table, string $key, $value, array $data): void
    {
        $data[$key] = $value;
        DB::table($table)->updateOrInsert([$key => $value], $data);
    }

    private function calculateGradePoints(string $grade): int
    {
        return match (strtoupper($grade)) {
            'A' => 1,
            'B' => 2,
            'C' => 3,
            'D' => 4,
            'E' => 5,
            'S' => 6,
            default => 7,
        };
    }

   /* =====================================================
       NECTA LOOKUP (END)
    ====================================================== */


public function show($id)
{
    $application = DB::table('applications')
        ->where('applications.id', $id)
        ->where('applications.user_id', Auth::id())
        ->firstOrFail();

    $personal = DB::table('application_personal_infos')
        ->where('application_id', $id)
        ->first();

    $contact = DB::table('application_contacts')
        ->where('application_id', $id)
        ->first();

    $kin = DB::table('application_next_of_kins')
        ->where('application_id', $id)
        ->first();

    $academic = DB::table('application_academics')
        ->where('application_id', $id)
        ->first();

    $subjects = $academic
        ? DB::table('application_olevel_subjects')
            ->where('application_academic_id', $academic->id)
            ->get()
        : collect();

    $program = DB::table('application_program_choices')
        ->where('application_id', $id)
        ->first();

    return view('applications.show', compact(
        'application',
        'personal',
        'contact',
        'kin',
        'academic',
        'subjects',
        'program'
    ));
}


// ADMISSION
/**
 * Create new application for Admission Officer
 */
public function createForAdmission()
{
    // Get active academic years
    $activeYear = DB::table('academic_years')
        ->where('is_active', 1)
        ->first()
        ?? DB::table('academic_years')->latest('id')->first();

    if (!$activeYear) {
        $id = DB::table('academic_years')->insertGetId([
            'name' => now()->year . '/' . (now()->year + 1),
            'status' => 'active',
            'is_active' => 1,
            'start_date' => now()->year . '-09-01',
            'end_date' => (now()->year + 1) . '-08-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $activeYear = DB::table('academic_years')->find($id);
    }

    // Create a draft application for admission officer
    $application = DB::table('applications')
        ->where('user_id', Auth::id())
        ->where('status', 'draft')
        ->first();

    if (!$application) {
        $id = DB::table('applications')->insertGetId([
            'user_id' => Auth::id(),
            'application_number' => 'APP-' . now()->year . '-' . Str::upper(Str::random(6)),
            'academic_year_id' => $activeYear->id,
            'intake' => 'March',
            'entry_level' => 'CSEE',
            'status' => 'draft',
            'is_free_application' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $application = DB::table('applications')->find($id);
    }

    // Get programs for selection - using correct column names
    $programs = DB::table('programmes')
        ->select('id', 'code', 'name', 'study_mode')
        ->where('is_active', 1)
        ->get();

    // Get academic years
    $academicYears = DB::table('academic_years')
        ->where('is_active', 1)
        ->get();

    return view('admission.applications.create', compact(
        'application',
        'programs',
        'academicYears'
    ));
}
/**
 * Display all applications
 */
public function allApplications(Request $request)
{
    $query = DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
           
            'application_contacts.phone',
            'academic_years.name as academic_year',
            // ALWAYS use selected_program if it exists
            'programmes_selected.name as program_name',
            'programmes_selected.code as program_code',
            // Keep these for comparison if needed
            'applications.selected_program_id',
            'programmes_first.name as first_choice_name',
            'programmes_first.code as first_choice_code'
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('application_contacts', 'applications.id', '=', 'application_contacts.application_id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->leftJoin('application_program_choices', 'applications.id', '=', 'application_program_choices.application_id')
        // MOST IMPORTANT: JOIN with selected_program_id
        ->leftJoin('programmes as programmes_selected', 'applications.selected_program_id', '=', 'programmes_selected.id')
        // Also join with first choice for reference
        ->leftJoin('programmes as programmes_first', 'application_program_choices.first_choice_program_id', '=', 'programmes_first.id')
        ->where('applications.status', '!=', 'draft');

    // Search
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('applications.application_number', 'LIKE', "%{$search}%")
              ->orWhere('application_personal_infos.first_name', 'LIKE', "%{$search}%")
              ->orWhere('application_personal_infos.last_name', 'LIKE', "%{$search}%")
              
              ->orWhere('application_contacts.phone', 'LIKE', "%{$search}%"); // Changed this line
        });
    }

    // Status filter
    if ($request->has('status') && $request->status) {
        $query->where('applications.status', $request->status);
    }

    // Date range filter
    if ($request->has('date_from') && $request->date_from) {
        $query->whereDate('applications.created_at', '>=', $request->date_from);
    }
    
    if ($request->has('date_to') && $request->date_to) {
        $query->whereDate('applications.created_at', '<=', $request->date_to);
    }

    $applications = $query->orderBy('applications.created_at', 'desc')
        ->paginate(20);

    // Get stats
    $stats = [
        'total' => DB::table('applications')->where('status', '!=', 'draft')->count(),
        'pending' => DB::table('applications')->where('status', 'submitted')->count(),
        'approved' => DB::table('applications')->where('status', 'approved')->count(),
        'rejected' => DB::table('applications')->where('status', 'rejected')->count(),
        'waitlisted' => DB::table('applications')->where('status', 'waitlisted')->count(),
    ];

    return view('admission.applications.index', compact('applications', 'stats'));
}
/**
 * Display pending review applications
 */
public function pendingReview(Request $request)
{
    $applications = DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
            'application_contacts.phone',
            'academic_years.name as academic_year'
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('application_contacts', 'applications.id', '=', 'application_contacts.application_id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.status', 'submitted')
        ->orderBy('applications.created_at', 'asc')
        ->paginate(20);

    // Convert string dates to Carbon objects
    $applications->getCollection()->transform(function ($app) {
        if ($app->created_at && is_string($app->created_at)) {
            $app->created_at = \Carbon\Carbon::parse($app->created_at);
        }
        if ($app->updated_at && is_string($app->updated_at)) {
            $app->updated_at = \Carbon\Carbon::parse($app->updated_at);
        }
        return $app;
    });

    return view('admission.applicants.pending-review', compact('applications'));
}

/**
 * Display under review applications
 */
public function underReview(Request $request)
{
    $applications = DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
            
            'academic_years.name as academic_year'
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.status', 'under_review')
        ->orderBy('applications.updated_at', 'desc')
        ->paginate(20);

    return view('admission.applicants.under-review', compact('applications'));
}

/**
 * Display approved applications
 */
public function approvedApplications(Request $request)
{
    $applications = DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
            'academic_years.name as academic_year',
            // CHANGE HERE: Use selected_program instead of program
            'selected_program.name as program_name',
            'selected_program.code as program_code'
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        // CHANGE HERE: Join with selected_program_id
        ->leftJoin('programmes as selected_program', 'applications.selected_program_id', '=', 'selected_program.id')
        ->where('applications.status', 'approved')
        ->orderBy('applications.approved_at', 'desc')
        ->paginate(20);

    // Convert dates to Carbon objects
    $applications->getCollection()->transform(function ($app) {
        // Convert all date fields to Carbon objects
        $dateFields = ['created_at', 'updated_at', 'approved_at', 'rejected_at', 'waitlisted_at'];
        
        foreach ($dateFields as $field) {
            if ($app->$field && is_string($app->$field)) {
                try {
                    $app->$field = \Carbon\Carbon::parse($app->$field);
                } catch (\Exception $e) {
                    $app->$field = null;
                }
            }
        }
        
        return $app;
    });

    return view('admission.applicants.approved', compact('applications'));
}
/**
 * Display rejected applications
 */
public function rejectedApplications(Request $request)
{
    $applications = DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
            
            'academic_years.name as academic_year'
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.status', 'rejected')
        ->orderBy('applications.rejected_at', 'desc')
        ->paginate(20);

    return view('admission.applicants.rejected', compact('applications'));
}

/**
 * Display waitlisted applications
 */
public function waitlistedApplications(Request $request)
{
    $applications = DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
           
            'academic_years.name as academic_year'
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.status', 'waitlisted')
        ->orderBy('applications.updated_at', 'desc')
        ->paginate(20);

    return view('admission.applicants.waitlisted', compact('applications'));
}

/**
 * View single application
 */
/**
 * View single application
 */
public function viewApplication($id)
{
    $application = DB::table('applications')
        ->select('applications.*', 'academic_years.name as academic_year')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.id', $id)
        ->first();

    if (!$application) {
        abort(404);
    }

    // Get all related data
    $personal = DB::table('application_personal_infos')
        ->where('application_id', $id)
        ->first();

    $contact = DB::table('application_contacts')
        ->where('application_id', $id)
        ->first();

    $kin = DB::table('application_next_of_kins')
        ->where('application_id', $id)
        ->first();

    $academic = DB::table('application_academics')
        ->where('application_id', $id)
        ->first();

    $subjects = $academic
        ? DB::table('application_olevel_subjects')
            ->where('application_academic_id', $academic->id)
            ->get()
        : collect();

    $programChoice = DB::table('application_program_choices')
        ->where('application_id', $id)
        ->first();

    // Get program for first choice (for backward compatibility)
    $program = $programChoice && $programChoice->first_choice_program_id
        ? DB::table('programmes')->where('id', $programChoice->first_choice_program_id)->first()
        : null;

    // Get audit logs
    $auditLogs = collect();
    try {
        if (DB::getSchemaBuilder()->hasTable('application_audit_logs')) {
            $auditLogs = DB::table('application_audit_logs')
                ->where('application_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
    } catch (\Exception $e) {
        // Table doesn't exist or other error
    }

    return view('admission.applicants.show', compact(
        'application',
        'personal',
        'contact',
        'kin',
        'academic',
        'subjects',
        'programChoice',
        'program',
        'auditLogs'
    ));
}
/**
 * Approve application
 */
/**
 * Approve application - SIMPLE WORKING VERSION
 */
public function approveApplication($id, Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'selected_program_id' => 'required|exists:programmes,id',
                'notes' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            // Check if application exists
            $application = DB::table('applications')->where('id', $id)->first();
            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }

            // Update application
            $updateData = [
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'selected_program_id' => $validated['selected_program_id'],
                'approval_notes' => $validated['notes'] ?? null,
                'updated_at' => now(),
            ];

            DB::table('applications')
                ->where('id', $id)
                ->update($updateData);

            // Update program choices if exists
            $programChoice = DB::table('application_program_choices')
                ->where('application_id', $id)
                ->first();
                
            if ($programChoice) {
                DB::table('application_program_choices')
                    ->where('application_id', $id)
                    ->update([
                        'selected_program_id' => $validated['selected_program_id'],
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully',
                'application_id' => $id,
                'selected_program' => $validated['selected_program_id']
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Approve error: ' . $e->getMessage(), [
                'application_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application: ' . $e->getMessage()
            ], 500);
        }
    }
/**
 * Reject application
 */
public function rejectApplication($id, Request $request)
{
    $request->validate([
        'reason' => 'required|string|max:500',
    ]);

    DB::beginTransaction();
    
    try {
        DB::table('applications')
            ->where('id', $id)
            ->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $request->reason,
                'updated_at' => now(),
            ]);

        // Log the action
        DB::table('application_audit_logs')->insert([
            'application_id' => $id,
            'action' => 'rejected',
            'performed_by' => Auth::id(),
            'notes' => $request->reason,
            'created_at' => now(),
        ]);

        DB::commit();

        return redirect()->route('admission.applicants.show', $id)
            ->with('success', 'Application rejected successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to reject application: ' . $e->getMessage());
    }
}

/**
 * Waitlist application
 */
public function waitlistApplication($id, Request $request)
{
    $request->validate([
        'notes' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    
    try {
        DB::table('applications')
            ->where('id', $id)
            ->update([
                'status' => 'waitlisted',
                'waitlisted_at' => now(),
                'waitlisted_by' => Auth::id(),
                'waitlist_notes' => $request->notes,
                'updated_at' => now(),
            ]);

        // Log the action
        DB::table('application_audit_logs')->insert([
            'application_id' => $id,
            'action' => 'waitlisted',
            'performed_by' => Auth::id(),
            'notes' => $request->notes,
            'created_at' => now(),
        ]);

        DB::commit();

        return redirect()->route('admission.applicants.show', $id)
            ->with('success', 'Application waitlisted successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to waitlist application: ' . $e->getMessage());
    }
}

/**
 * Bulk actions on applications
 */
/**
 * Bulk actions on applications
 */
public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,waitlist,delete',
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
            'notes' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:500',
            'bulk_data' => 'nullable|array', // For storing program selections
        ]);

        $applicationIds = $request->application_ids;
        $action = $request->action;
        $notes = $request->notes;
        $reason = $request->reason;
        $bulkData = $request->bulk_data ?? [];

        DB::beginTransaction();
        
        try {
            $count = 0;
            foreach ($applicationIds as $id) {
                if ($action === 'approve') {
                    $selectedProgramId = null;
                    
                    // Check if we have bulk_data with program selections
                    if (!empty($bulkData)) {
                        foreach ($bulkData as $data) {
                            if (isset($data['application_id']) && $data['application_id'] == $id && isset($data['selected_program_id'])) {
                                $selectedProgramId = $data['selected_program_id'];
                                break;
                            }
                        }
                    }
                    
                    // If no bulk_data, try to get first choice
                    if (!$selectedProgramId) {
                        $programChoice = DB::table('application_program_choices')
                            ->where('application_id', $id)
                            ->first();
                        if ($programChoice) {
                            $selectedProgramId = $programChoice->first_choice_program_id;
                        }
                    }
                    
                    $updateData = [
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::id(),
                        'approval_notes' => $notes,
                        'updated_at' => now(),
                    ];
                    
                    // Add selected program if available
                    if ($selectedProgramId) {
                        $updateData['selected_program_id'] = $selectedProgramId;
                        
                        // Update program choices table
                        DB::table('application_program_choices')
                            ->where('application_id', $id)
                            ->update([
                                'selected_program_id' => $selectedProgramId,
                                'updated_at' => now(),
                            ]);
                    }
                    
                    DB::table('applications')
                        ->where('id', $id)
                        ->update($updateData);
                    $count++;

                } elseif ($action === 'reject') {
                    DB::table('applications')
                        ->where('id', $id)
                        ->update([
                            'status' => 'rejected',
                            'rejected_at' => now(),
                            'rejected_by' => Auth::id(),
                            'rejection_reason' => $reason,
                            'updated_at' => now(),
                        ]);
                    $count++;

                } elseif ($action === 'waitlist') {
                    DB::table('applications')
                        ->where('id', $id)
                        ->update([
                            'status' => 'waitlisted',
                            'waitlisted_at' => now(),
                            'waitlisted_by' => Auth::id(),
                            'waitlist_notes' => $notes,
                            'updated_at' => now(),
                        ]);
                    $count++;

                } elseif ($action === 'delete') {
                    DB::table('applications')->where('id', $id)->delete();
                    $count++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk action completed successfully.',
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Bulk action failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }
/**
 * Get applicant's program choices
 */
/**
 * Get applicant's program choices - UPDATED FOR YOUR SCHEMA
 */
/**
 * Get applicant's program choices - UPDATED VERSION (without description)
 */
 public function getProgramChoices($id)
    {
        try {
            // First, verify application exists
            $application = DB::table('applications')
                ->where('id', $id)
                ->first();
                
            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }
            
            // Get program choices
            $programChoice = DB::table('application_program_choices')
                ->where('application_id', $id)
                ->first();

            if (!$programChoice) {
                return response()->json([
                    'success' => true,
                    'choices' => [],
                    'message' => 'No program choices found for this application'
                ]);
            }

            $choices = [];
            
            // Get first choice program details
            if (!empty($programChoice->first_choice_program_id)) {
                $firstChoice = DB::table('programmes')
                    ->where('id', $programChoice->first_choice_program_id)
                    ->select('id', 'code', 'name')
                    ->first();
                    
                if ($firstChoice) {
                    $choices['first_choice'] = $firstChoice;
                }
            }
            
            // Get second choice program details
            if (!empty($programChoice->second_choice_program_id)) {
                $secondChoice = DB::table('programmes')
                    ->where('id', $programChoice->second_choice_program_id)
                    ->select('id', 'code', 'name')
                    ->first();
                    
                if ($secondChoice) {
                    $choices['second_choice'] = $secondChoice;
                }
            }
            
            // Get third choice program details
            if (!empty($programChoice->third_choice_program_id)) {
                $thirdChoice = DB::table('programmes')
                    ->where('id', $programChoice->third_choice_program_id)
                    ->select('id', 'code', 'name')
                    ->first();
                    
                if ($thirdChoice) {
                    $choices['third_choice'] = $thirdChoice;
                }
            }

            return response()->json([
                'success' => true,
                'choices' => $choices
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get program choices: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'application_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get program choices: ' . $e->getMessage(),
                'error_details' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function showSendLetterForm($id)
{
    // Get application with relations
    $application = DB::table('applications')
        ->select(
            'applications.*',
            'programmes.name as program_name',
            'programmes.code as program_code',
            
            'academic_years.name as academic_year_name'
        )
        ->leftJoin('programmes', 'applications.selected_program_id', '=', 'programmes.id')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.id', $id)
        ->where('applications.status', 'approved')
        ->first();
        
    if (!$application) {
        abort(404, 'Application not found or not approved');
    }
    
    // Get applicant personal info
    $applicant = DB::table('application_personal_infos')
        ->where('application_id', $id)
        ->first();
        
    $contact = DB::table('application_contacts')
        ->where('application_id', $id)
        ->first();
        
    $program = DB::table('programmes')
        ->where('id', $application->selected_program_id)
        ->first();
        
    $academicYear = DB::table('academic_years')
        ->where('id', $application->academic_year_id)
        ->first();
    
    return view('admission.send-admission-letter', compact(
        'application',
        'applicant',
        'contact',
        'program',
        'academicYear'
    ));
}


}

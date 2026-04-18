<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdmissionApplicantController extends Controller
{
    /**
     * Display all applications
     */
    /**
 * Display all applications
 */
public function allApplications(Request $request)
{
    $query = $this->buildApplicationQuery();
    
    // Search filter
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('applications.application_number', 'LIKE', "%{$search}%")
              ->orWhere('application_personal_infos.first_name', 'LIKE', "%{$search}%")
              ->orWhere('application_personal_infos.last_name', 'LIKE', "%{$search}%")
              ->orWhere('application_contacts.phone', 'LIKE', "%{$search}%") // ✅ Sasa phone ipo
              ->orWhere('application_contacts.email', 'LIKE', "%{$search}%"); // ✅ Search email pia
              // ❌ Futa national_id - haipo
        });
    }
    
    // Status filter
    if ($request->has('status') && $request->status) {
        $query->where('applications.status', $request->status);
    }
    
    // Program filter
    if ($request->has('program_id') && $request->program_id) {
        $query->where('application_program_choices.first_choice_program_id', $request->program_id)
              ->orWhere('application_program_choices.second_choice_program_id', $request->program_id)
              ->orWhere('application_program_choices.third_choice_program_id', $request->program_id);
    }
    
    // Academic year filter
    if ($request->has('academic_year_id') && $request->academic_year_id) {
        $query->where('applications.academic_year_id', $request->academic_year_id);
    }
    
    $applications = $query->paginate(20);
    $programs = DB::table('programmes')->where('is_active', 1)->get();
    $academicYears = DB::table('academic_years')->where('is_active', 1)->get();
    
    return view('admission.applicants.all', compact('applications', 'programs', 'academicYears'));
}
    /**
     * Display pending review applications
     */
    public function pendingReview(Request $request)
    {
        $query = $this->buildApplicationQuery()
            ->where('applications.status', 'submitted');
        
        $applications = $query->paginate(20);
        
        return view('admission.applicants.pending-review', [
            'applications' => $applications,
            'title' => 'Pending Review Applications'
        ]);
    }
    
    /**
     * Display under review applications
     */
    public function underReview(Request $request)
    {
        $query = $this->buildApplicationQuery()
            ->where('applications.status', 'under_review');
        
        $applications = $query->paginate(20);
        
        return view('admission.applicants.under-review', [
            'applications' => $applications,
            'title' => 'Applications Under Review'
        ]);
    }
    
    /**
     * Display approved applications
     */
    public function approved(Request $request)
    {
        $query = $this->buildApplicationQuery()
            ->where('applications.status', 'approved');
        
        $applications = $query->paginate(20);
        
        return view('admission.applicants.approved', [
            'applications' => $applications,
            'title' => 'Approved Applications'
        ]);
    }
    
    /**
     * Display rejected applications
     */
    public function rejected(Request $request)
    {
        $query = $this->buildApplicationQuery()
            ->where('applications.status', 'rejected');
        
        $applications = $query->paginate(20);
        
        return view('admission.applicants.rejected', [
            'applications' => $applications,
            'title' => 'Rejected Applications'
        ]);
    }
    
    /**
     * Display waitlisted applications
     */
    public function waitlisted(Request $request)
    {
        $query = $this->buildApplicationQuery()
            ->where('applications.status', 'waitlisted');
        
        $applications = $query->paginate(20);
        
        return view('admission.applicants.waitlisted', [
            'applications' => $applications,
            'title' => 'Waitlisted Applications'
        ]);
    }
    
    /**
     * View single application details
     */
    public function view($id)
{
    $application = DB::table('applications')
        ->select('applications.*', 'academic_years.name as academic_year_name')
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.id', $id)
        ->first();
    
    if (!$application) {
        abort(404);
    }
    
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
    
    // Get program names
    $firstProgram = $programChoice && $programChoice->first_choice_program_id
        ? DB::table('programmes')->where('id', $programChoice->first_choice_program_id)->first()
        : null;
    
    $secondProgram = $programChoice && $programChoice->second_choice_program_id
        ? DB::table('programmes')->where('id', $programChoice->second_choice_program_id)->first()
        : null;
    
    $thirdProgram = $programChoice && $programChoice->third_choice_program_id
        ? DB::table('programmes')->where('id', $programChoice->third_choice_program_id)->first()
        : null;
    
    return view('admission.applicants.view', compact(
        'application',
        'personal',
        'contact',
        'kin',
        'academic',
        'subjects',
        'programChoice',
        'firstProgram',
        'secondProgram',
        'thirdProgram'
    ));
}
    
    /**
     * Approve an application
     */
    public function approve($id, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            DB::table('applications')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'approval_notes' => $request->notes,
                    'updated_at' => now(),
                ]);
            
            // Log the action
            DB::table('application_audit_logs')->insert([
                'application_id' => $id,
                'action' => 'approved',
                'performed_by' => Auth::id(),
                'notes' => $request->notes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('admission.applicants.view', $id)
                ->with('success', 'Application approved successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve application: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject an application
     */
    public function reject($id, Request $request)
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
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('admission.applicants.view', $id)
                ->with('success', 'Application rejected successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject application: ' . $e->getMessage());
        }
    }
    
    /**
     * Waitlist an application
     */
    public function waitlist($id, Request $request)
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
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('admission.applicants.view', $id)
                ->with('success', 'Application waitlisted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to waitlist application: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk actions on applications
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,waitlist,delete',
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
        ]);
        
        $applicationIds = $request->application_ids;
        $action = $request->action;
        $notes = $request->notes;
        
        DB::beginTransaction();
        
        try {
            foreach ($applicationIds as $id) {
                if ($action === 'approve') {
                    DB::table('applications')
                        ->where('id', $id)
                        ->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                            'approval_notes' => $notes,
                            'updated_at' => now(),
                        ]);
                } elseif ($action === 'reject') {
                    DB::table('applications')
                        ->where('id', $id)
                        ->update([
                            'status' => 'rejected',
                            'rejected_at' => now(),
                            'rejected_by' => Auth::id(),
                            'rejection_reason' => $notes,
                            'updated_at' => now(),
                        ]);
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
                } elseif ($action === 'delete') {
                    DB::table('applications')->where('id', $id)->delete();
                }
                
                // Log each action
                DB::table('application_audit_logs')->insert([
                    'application_id' => $id,
                    'action' => $action,
                    'performed_by' => Auth::id(),
                    'notes' => $notes,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk action completed successfully.',
                'count' => count($applicationIds)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper method to build application query
     */
   private function buildApplicationQuery()
{
    return DB::table('applications')
        ->select(
            'applications.*',
            'application_personal_infos.first_name',
            'application_personal_infos.last_name',
            'application_personal_infos.gender',
            'application_contacts.phone', // ✅ Phone iko kwenye application_contacts
            'application_contacts.email', // ✅ Email pia
            'academic_years.name as academic_year',
            'programmes.name as first_choice_program',
            DB::raw("CONCAT(application_personal_infos.first_name, ' ', application_personal_infos.last_name) as full_name")
        )
        ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
        ->leftJoin('application_contacts', 'applications.id', '=', 'application_contacts.application_id') // ✅ Hii ndio ina phone na email
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->leftJoin('application_program_choices', 'applications.id', '=', 'application_program_choices.application_id')
        ->leftJoin('programmes', 'application_program_choices.first_choice_program_id', '=', 'programmes.id')
        ->orderBy('applications.created_at', 'desc');
}
}
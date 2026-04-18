<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show applicant dashboard in CBE style
     */
 public function index()
{
    $user = Auth::user();
    
    // Get applicant's personal info from submitted applications
    $personalInfo = DB::table('application_personal_infos')
        ->select('application_personal_infos.*')
        ->join('applications', 'application_personal_infos.application_id', '=', 'applications.id')
        ->where('applications.user_id', $user->id)
        ->where('applications.status', '!=', 'draft')
        ->orderBy('applications.created_at', 'desc')
        ->first();

    // Get latest submitted/approved application - TUMIA COLUMN ZILIZOPO TU
    $application = DB::table('applications')
        ->select([
            'applications.*',
            'academic_years.name as academic_year_name'
            // Tuachane na start_date, end_date, application_deadline kama hazipo
        ])
        ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
        ->where('applications.user_id', $user->id)
        ->where('applications.status', '!=', 'draft')
        ->orderBy('applications.created_at', 'desc')
        ->first();

    // If no application found, redirect to start
    if (!$application) {
        return redirect()->route('applicant.application.start')
            ->with('info', 'Please start your application process.');
    }

    // Get program details
    $programs = $this->getProgramDetails($application->id);
    
    // Get payment info
    $paymentInfo = $this->getPaymentInfo($application->id);
    
    // Get important dates - BADILISHA KUTUMIA DATA ILIYOPO
    $importantDates = $this->getImportantDates($application);
    
    // Get admission status
    $admissionStatus = $this->getAdmissionStatus($application);

    return view('applicant.dashboard', compact(
        'user',
        'personalInfo',
        'application',
        'programs',
        'paymentInfo',
        'importantDates',
        'admissionStatus'
    ));
}

    /**
     * Get program details for application - SASA HAIKUMTI duration
     */
  private function getProgramDetails($applicationId)
{
    $programs = [];
    
    // Get program choices
    $programChoice = DB::table('application_program_choices')
        ->where('application_id', $applicationId)
        ->first();
    
    if (!$programChoice) {
        return $programs;
    }
    
    // Counter for numbering
    $counter = 1;
    
    // Get first choice program
    if ($programChoice->first_choice_program_id) {
        $firstProgram = DB::table('programmes')
            ->where('id', $programChoice->first_choice_program_id)
            ->first();
        
        if ($firstProgram) {
            $programs[] = [
                'number' => $counter++,
                'name' => $firstProgram->name ?? 'N/A',
                'code' => $firstProgram->code ?? 'N/A',
                'study_mode' => $firstProgram->study_mode ?? 'Full Time',
                'status' => 'Selected'
            ];
        }
    }
    
    // Get second choice program
    if ($programChoice->second_choice_program_id) {
        $secondProgram = DB::table('programmes')
            ->where('id', $programChoice->second_choice_program_id)
            ->first();
        
        if ($secondProgram) {
            $programs[] = [
                'number' => $counter++,
                'name' => $secondProgram->name ?? 'N/A',
                'code' => $secondProgram->code ?? 'N/A',
                'study_mode' => $secondProgram->study_mode ?? 'Full Time',
                'status' => 'Alternative'
            ];
        }
    }
    
    // Get third choice program
    if ($programChoice->third_choice_program_id) {
        $thirdProgram = DB::table('programmes')
            ->where('id', $programChoice->third_choice_program_id)
            ->first();
        
        if ($thirdProgram) {
            $programs[] = [
                'number' => $counter++,
                'name' => $thirdProgram->name ?? 'N/A',
                'code' => $thirdProgram->code ?? 'N/A',
                'study_mode' => $thirdProgram->study_mode ?? 'Full Time',
                'status' => 'Alternative'
            ];
        }
    }
    
    return $programs;
}
/**
 * Format date for display
 */
private function formatDate($date, $default = 'N/A')
{
    try {
        if (!$date) return $default;
        return \Carbon\Carbon::parse($date)->format('d M, Y');
    } catch (\Exception $e) {
        return $default;
    }
}
   
    /**
     * Get payment information
     */
    private function getPaymentInfo($applicationId)
    {
        try {
            // Check if payments table exists before querying
            if (!$this->tableExists('payments')) {
                // Return default payment info if table doesn't exist
                return $this->getDefaultPaymentInfo();
            }
            
            // Check if payment exists
            $payment = DB::table('payments')
                ->where('application_id', $applicationId)
                ->first();

            if ($payment) {
                return [
                    'status' => $payment->status ?? 'pending',
                    'amount' => $payment->amount ?? 0,
                    'paid_at' => $payment->paid_at ?? null,
                    'reference' => $payment->reference_number ?? null,
                    'control_number' => $payment->control_number ?? null,
                ];
            }

            // Return default payment info
            return $this->getDefaultPaymentInfo();
            
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching payment info: ' . $e->getMessage());
            
            // Return default payment info on error
            return $this->getDefaultPaymentInfo();
        }
    }

    /**
     * Check if a table exists in the database
     */
    private function tableExists($tableName)
    {
        try {
            $result = DB::select("SHOW TABLES LIKE '{$tableName}'");
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get default payment info
     */
    private function getDefaultPaymentInfo()
    {
        return [
            'status' => 'pending',
            'amount' => 0,
            'message' => 'You will be given NHIF Control number after completing registration process (if you don\'t have Health Insurance Card).',
            'note' => 'isiwe na card wala quick maana card hazitakiwi applicant hizo zinafaa kwa admin',
        ];
    }

    /**
     * Get important dates
     */
   private function getImportantDates($application)
{
    // Return default dates kwa sasa
    return [
        'application_deadline' => '30 Mar, 2026',
        'registration_start' => '01 Sep, 2025',
        'registration_end' => '30 Sep, 2025',
    ];
}
   /**
 * Get admission status
 */
private function getAdmissionStatus($application)
{
    // TUMIA STATUS YA APPLICATION DIRECTLY
    $status = strtolower($application->status ?? 'draft');
    
    switch ($status) {
        case 'approved':
        case 'selected':
            // Pata jina la programu iliyochaguliwa
            $selectedProgramName = 'Not specified';
            $selectedProgramCode = '';
            
            // TAFUTA PROGRAM KUTOKA KWENYE applications.selected_program_id
            if ($application->selected_program_id) {
                $program = DB::table('programmes')
                    ->where('id', $application->selected_program_id)
                    ->first();
                
                if ($program) {
                    $selectedProgramName = $program->name ?? 'Not specified';
                    $selectedProgramCode = $program->code ?? '';
                }
            } else {
                // Fallback: Tafuta programu kutoka kwenye program choices
                $programChoice = DB::table('application_program_choices')
                    ->where('application_id', $application->id)
                    ->first();
                
                if ($programChoice && $programChoice->first_choice_program_id) {
                    $program = DB::table('programmes')
                        ->where('id', $programChoice->first_choice_program_id)
                        ->first();
                    
                    if ($program) {
                        $selectedProgramName = $program->name;
                        $selectedProgramCode = $program->code;
                    }
                }
            }
            
            return [
                'status' => 'Selected',
                'message' => 'You have been SELECTED to join our institution for ' . $selectedProgramName . ' (' . $selectedProgramCode . '). We congratulate you and wish you success in your academic endeavors.',
                'color' => 'success',
                'icon' => 'check-circle',
                'selected_program' => [
                    'name' => $selectedProgramName,
                    'code' => $selectedProgramCode
                ]
            ];
            
        case 'submitted':
            return [
                'status' => 'Submitted',
                'message' => 'Your application has been submitted successfully.',
                'color' => 'info',
                'icon' => 'clock',
            ];
            
        case 'under_review':
            return [
                'status' => 'Under Review',
                'message' => 'Your application is currently being reviewed.',
                'color' => 'warning',
                'icon' => 'search',
            ];
            
        case 'rejected':
        case 'cancelled':
            return [
                'status' => ucfirst($status),
                'message' => 'Your application has been ' . $status . '.',
                'color' => 'danger',
                'icon' => 'times-circle',
            ];
            
        default: // including 'draft'
            return [
                'status' => 'In Progress',
                'message' => 'Your application is still in draft mode.',
                'color' => 'secondary',
                'icon' => 'edit',
            ];
    }
}
    /**
     * Show application details
     */
    public function showApplication($id)
    {
        $user = Auth::user();
        
        $application = DB::table('applications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$application) {
            abort(404, 'Application not found');
        }

        // Get all application data
        $sections = $this->getApplicationSections($id);

        return view('applicant.application-details-cbe', array_merge(
            ['application' => $application],
            $sections
        ));
    }

    /**
     * Download admission letter
     */
    public function downloadAdmissionLetter($id)
    {
        $user = Auth::user();
        
        $application = DB::table('applications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereIn('applications.status', ['approved', 'selected'])
            ->first();

        if (!$application) {
            abort(404, 'Admission letter not found or application not approved');
        }

        // Generate PDF admission letter
        // This is a placeholder - implement your PDF generation logic here
        
        return response()->streamDownload(function () use ($application, $user) {
            echo "ADMISSION LETTER\n\n";
            echo "This is to certify that {$user->first_name} {$user->last_name}\n";
            echo "has been admitted to our institution.\n";
            echo "Application Number: {$application->application_number}\n";
            echo "Date: " . now()->format('d/m/Y');
        }, "admission-letter-{$application->application_number}.txt");
    }

    /**
     * Restore cancelled admission
     */
    public function restoreAdmission(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id'
        ]);

        $application = DB::table('applications')
            ->where('id', $request->application_id)
            ->where('user_id', Auth::id())
            ->whereIn('applications.status', ['cancelled', 'rejected'])
            ->first();

        if (!$application) {
            return back()->with('error', 'No cancelled/rejected admission found to restore.');
        }

        // Update status back to approved
        DB::table('applications')
            ->where('id', $request->application_id)
            ->update([
                'status' => 'approved',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Admission restored successfully!');
    }

    /**
     * Cancel admission
     */
    public function cancelAdmission(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'reason' => 'required|string|max:500',
            'confirm' => 'required|accepted',
        ]);

        $application = DB::table('applications')
            ->where('id', $request->application_id)
            ->where('user_id', Auth::id())
            ->whereIn('applications.status', ['approved', 'selected', 'submitted'])
            ->first();

        if (!$application) {
            return back()->with('error', 'Application not found or cannot be cancelled.');
        }

        // Update status to cancelled
        DB::table('applications')
            ->where('id', $request->application_id)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
                'updated_at' => now(),
            ]);

        return back()->with('warning', 'Admission cancelled successfully. WARNING: After cancelling your admission you won\'t be recognized as a student.');
    }

    /**
     * Get all application sections data
     */
    private function getApplicationSections($applicationId)
    {
        // Get personal info
        $personal = DB::table('application_personal_infos')
            ->where('application_id', $applicationId)
            ->first();

        // Get contact info
        $contact = DB::table('application_contacts')
            ->where('application_id', $applicationId)
            ->first();

        // Get next of kin
        $kin = DB::table('application_next_of_kins')
            ->where('application_id', $applicationId)
            ->first();

        // Get academic info
        $academic = DB::table('application_academics')
            ->where('application_id', $applicationId)
            ->first();

        // Get subjects
        $subjects = $academic
            ? DB::table('application_olevel_subjects')
                ->where('application_academic_id', $academic->id)
                ->orderBy('subject_name')
                ->get()
            : collect();

        // Get program choices
        $programChoice = DB::table('application_program_choices')
            ->where('application_id', $applicationId)
            ->first();

        return compact(
            'personal',
            'contact',
            'kin',
            'academic',
            'subjects',
            'programChoice'
        );
    }

     /**
     * Show Personal Information Form with data
     */
    public function personalInfo()
    {
        $user = Auth::user();
        
        // Get latest application
        $application = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$application) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'No application found. Please start a new application.');
        }
        
        // Get personal info from database
        $personalInfo = DB::table('application_personal_infos')
            ->where('application_id', $application->id)
            ->first();
        
        return view('applicant.personal-info', compact('application', 'personalInfo'));
    }
    
    /**
     * Show Contact Information Form with data
     */
    public function contactInfo()
    {
        $user = Auth::user();
        
        // Get latest application
        $application = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$application) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'No application found. Please start a new application.');
        }
        
        // Get contact info from database
        $contactInfo = DB::table('application_contacts')
            ->where('application_id', $application->id)
            ->first();
        
        return view('applicant.contacts-info', compact('application', 'contactInfo'));
    }
    
    /**
     * Show Next of Kin Form with data
     */
    public function nextOfKin()
    {
        $user = Auth::user();
        
        // Get latest application
        $application = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$application) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'No application found. Please start a new application.');
        }
        
        // Get next of kin info from database
        $nextOfKin = DB::table('application_next_of_kins')
            ->where('application_id', $application->id)
            ->first();
        
        return view('applicant.next-of-kin', compact('application', 'nextOfKin'));
    }
    
    /**
     * Show Academic Information Form with data
     */
    public function academicInfo()
    {
        $user = Auth::user();
        
        // Get latest application
        $application = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$application) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'No application found. Please start a new application.');
        }
        
        // Get academic info from database
        $academicInfo = DB::table('application_academics')
            ->where('application_id', $application->id)
            ->first();
        
        // Get subjects if academic info exists
        $subjects = $academicInfo 
            ? DB::table('application_olevel_subjects')
                ->where('application_academic_id', $academicInfo->id)
                ->get()
            : collect();
        
        return view('applicant.academic-info', compact('application', 'academicInfo', 'subjects'));
    }
    
    /**
     * Show Program Information Form with data
     */
    public function programInfo()
    {
        $user = Auth::user();
        
        // Get latest application
        $application = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$application) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'No application found. Please start a new application.');
        }
        
        // Get program choices from database
        $programChoice = DB::table('application_program_choices')
            ->where('application_id', $application->id)
            ->first();
        
        // Get all active programs for dropdown
        $programs = DB::table('programmes')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        // Get selected program details
        $firstProgram = $programChoice && $programChoice->first_choice_program_id
            ? DB::table('programmes')->find($programChoice->first_choice_program_id)
            : null;
        
        $secondProgram = $programChoice && $programChoice->second_choice_program_id
            ? DB::table('programmes')->find($programChoice->second_choice_program_id)
            : null;
        
        $thirdProgram = $programChoice && $programChoice->third_choice_program_id
            ? DB::table('programmes')->find($programChoice->third_choice_program_id)
            : null;
        
        return view('applicant.program-info', compact(
            'application', 
            'programChoice', 
            'programs',
            'firstProgram',
            'secondProgram',
            'thirdProgram'
        ));
    }
    
    /**
     * Show Preview & Submit page with all data
     */
    public function previewSubmit()
    {
        $user = Auth::user();
        
        // Get latest application
        $application = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$application) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'No application found. Please start a new application.');
        }
        
        // Get all application data
        $personalInfo = DB::table('application_personal_infos')
            ->where('application_id', $application->id)
            ->first();
        
        $contactInfo = DB::table('application_contacts')
            ->where('application_id', $application->id)
            ->first();
        
        $nextOfKin = DB::table('application_next_of_kins')
            ->where('application_id', $application->id)
            ->first();
        
        $academicInfo = DB::table('application_academics')
            ->where('application_id', $application->id)
            ->first();
        
        $subjects = $academicInfo 
            ? DB::table('application_olevel_subjects')
                ->where('application_academic_id', $academicInfo->id)
                ->get()
            : collect();
        
        $programChoice = DB::table('application_program_choices')
            ->where('application_id', $application->id)
            ->first();
        
        // Get program names
        $firstProgram = $programChoice && $programChoice->first_choice_program_id
            ? DB::table('programmes')->find($programChoice->first_choice_program_id)
            : null;
        
        $secondProgram = $programChoice && $programChoice->second_choice_program_id
            ? DB::table('programmes')->find($programChoice->second_choice_program_id)
            : null;
        
        $thirdProgram = $programChoice && $programChoice->third_choice_program_id
            ? DB::table('programmes')->find($programChoice->third_choice_program_id)
            : null;
        
        return view('applicant.preview-submit', compact(
            'application',
            'personalInfo',
            'contactInfo',
            'nextOfKin',
            'academicInfo',
            'subjects',
            'programChoice',
            'firstProgram',
            'secondProgram',
            'thirdProgram'
        ));
    }
    
}
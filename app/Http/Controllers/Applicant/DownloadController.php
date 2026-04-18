<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DownloadController extends Controller
{
    /**
     * Show download form page
     */
    public function show($id = null)
    {
        $user = Auth::user();
        
        // If no ID provided, get latest submitted application
        if (!$id) {
            $application = DB::table('applications')
                ->where('user_id', $user->id)
                ->where('status', '!=', 'draft')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($application) {
                $id = $application->id;
            } else {
                return redirect()->route('applicant.dashboard')
                    ->with('warning', 'You have no submitted applications to download.');
            }
        }
        
        // Verify ownership
        $application = DB::table('applications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$application) {
            abort(404, 'Application not found');
        }
        
        // Get all application data
        $data = $this->getApplicationData($id);
        
        return view('applicant.download-form', array_merge(['application' => $application], $data));
    }
    
    /**
     * Generate PDF of application form
     */
    public function generatePDF($id)
    {
        $user = Auth::user();
        
        // Verify ownership
        $application = DB::table('applications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$application) {
            abort(404, 'Application not found');
        }
        
        // Get all application data
        $data = $this->getApplicationData($id);
        
        // Generate PDF
        $pdf = PDF::loadView('applicant.pdf.application-form', array_merge(['application' => $application], $data));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Application-Form-' . $application->application_number . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    /**
     * Preview PDF before download
     */
    public function preview($id)
    {
        $user = Auth::user();
        
        // Verify ownership
        $application = DB::table('applications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$application) {
            abort(404, 'Application not found');
        }
        
        // Get all application data
        $data = $this->getApplicationData($id);
        
        // Generate PDF
        $pdf = PDF::loadView('applicant.pdf.application-form', array_merge(['application' => $application], $data));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Return PDF preview in browser
        return $pdf->stream('Application-Form-' . $application->application_number . '.pdf');
    }
    
    /**
     * Get all application data for display
     */
    private function getApplicationData($applicationId)
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
                ->get()
            : collect();
        
        // Get program choices
        $programChoice = DB::table('application_program_choices')
            ->where('application_id', $applicationId)
            ->first();
        
        // Get academic year
        $academicYear = DB::table('academic_years')
            ->where('id', function($query) use ($applicationId) {
                $query->select('academic_year_id')
                      ->from('applications')
                      ->where('id', $applicationId);
            })
            ->first();
        
        // Get program details
        $firstProgram = $programChoice && $programChoice->first_choice_program_id
            ? DB::table('programmes')->find($programChoice->first_choice_program_id)
            : null;
        
        $secondProgram = $programChoice && $programChoice->second_choice_program_id
            ? DB::table('programmes')->find($programChoice->second_choice_program_id)
            : null;
        
        $thirdProgram = $programChoice && $programChoice->third_choice_program_id
            ? DB::table('programmes')->find($programChoice->third_choice_program_id)
            : null;
        
        // Get selected program
        $selectedProgram = DB::table('applications')
            ->where('id', $applicationId)
            ->value('selected_program_id');
            
        $selectedProgramDetails = $selectedProgram
            ? DB::table('programmes')->find($selectedProgram)
            : null;
        
        return compact(
            'personal',
            'contact',
            'kin',
            'academic',
            'subjects',
            'programChoice',
            'academicYear',
            'firstProgram',
            'secondProgram',
            'thirdProgram',
            'selectedProgramDetails'
        );
    }
}
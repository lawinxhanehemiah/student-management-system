<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionLetterController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // Check if user has approved application
        $application = DB::table('applications')
            ->select(
                'applications.*',
                'academic_years.name as academic_year',
                'programmes.name as program_name',
                'programmes.code as program_code',
                'programmes.duration as program_duration'
            )
            ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('programmes', 'applications.selected_program_id', '=', 'programmes.id')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('admission_letter_sent_at')
            ->first();
            
        if (!$application) {
            return view('applicant.no-admission-letter');
        }
        
        // Get applicant personal info
        $personal = DB::table('application_personal_infos')
            ->where('application_id', $application->id)
            ->first();
            
        // Get contact info
        $contact = DB::table('application_contacts')
            ->where('application_id', $application->id)
            ->first();
            
        return view('applicant.admission-letter', compact(
            'application',
            'personal',
            'contact'
        ));
    }
    
    public function download()
    {
        $user = Auth::user();
        
        $application = DB::table('applications')
            ->select(
                'applications.*',
                'academic_years.name as academic_year',
                'programmes.name as program_name',
                'programmes.code as program_code',
                'programmes.duration as program_duration',
                'programmes.fees as program_fees'
            )
            ->leftJoin('academic_years', 'applications.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('programmes', 'applications.selected_program_id', '=', 'programmes.id')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('admission_letter_sent_at')
            ->first();
            
        if (!$application) {
            abort(404, 'Admission letter not available');
        }
        
        $personal = DB::table('application_personal_infos')
            ->where('application_id', $application->id)
            ->first();
            
        $contact = DB::table('application_contacts')
            ->where('application_id', $application->id)
            ->first();
            
        // Generate PDF
        $pdf = PDF::loadView('pdf.admission-letter', [
            'application' => $application,
            'personal' => $personal,
            'contact' => $contact,
            'download' => true
        ]);
        
        $filename = "Admission-Letter-{$application->application_number}.pdf";
        
        return $pdf->download($filename);
    }
}
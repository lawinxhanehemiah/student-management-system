<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OfferController extends Controller
{
    /**
     * Display offer letters management page
     */
    public function offerLetters(Request $request)
    {
        $programmes = DB::table('programmes')->where('is_active', 1)->where('status', 'active')->orderBy('name')->get();
        $selectedProgrammeId = $request->get('programme_id');
        $selectedIntake = $request->get('intake', 'March');
        $search = $request->get('search');
        $letterStatus = $request->get('letter_status');
        
        $query = DB::table('applications as a')
            ->select(
                'a.id', 
                'a.application_number', 
                'a.status', 
                'a.intake', 
                'a.study_mode',
                'a.selected_program_id', 
                'a.submitted_at', 
                'a.approved_at',
                'a.admission_letter_sent_at', 
                'a.admission_letter_sent_by', 
                'a.admission_status',
                'p.first_name', 
                'p.middle_name', 
                'p.last_name',
                'u.email as email', 
                'u.phone as phone',
                'prog.name as programme_name', 
                'prog.code as programme_code'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereIn('a.status', ['approved', 'registered'])
            ->where('a.intake', $selectedIntake);
        
        if ($selectedProgrammeId) {
            $query->where('a.selected_program_id', $selectedProgrammeId);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('a.application_number', 'like', "%{$search}%")
                  ->orWhere('p.first_name', 'like', "%{$search}%")
                  ->orWhere('p.last_name', 'like', "%{$search}%")
                  ->orWhere('u.phone', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
            });
        }
        
        if ($letterStatus === 'sent') {
            $query->whereNotNull('a.admission_letter_sent_at');
        } elseif ($letterStatus === 'pending') {
            $query->whereNull('a.admission_letter_sent_at');
        }
        
        $applications = $query->orderByDesc('a.approved_at')->paginate(20)->withQueryString();
        
        // FIX: Make sure statistics are calculated correctly
        $statistics = [
            'total_approved' => DB::table('applications')
                ->whereIn('status', ['approved', 'registered'])
                ->where('intake', $selectedIntake)
                ->count(),
            'letters_sent' => DB::table('applications')
                ->whereIn('status', ['approved', 'registered'])
                ->where('intake', $selectedIntake)
                ->whereNotNull('admission_letter_sent_at')
                ->count(),
            'letters_pending' => DB::table('applications')
                ->whereIn('status', ['approved', 'registered'])
                ->where('intake', $selectedIntake)
                ->whereNull('admission_letter_sent_at')
                ->count(),
        ];
        
        return view('admission.offers.letters', compact(
            'applications', 'programmes', 'selectedProgrammeId', 
            'selectedIntake', 'search', 'letterStatus', 'statistics'
        ));
    }
    
    /**
     * Send single offer letter via email and SMS
     */
    public function sendLetter(Request $request, $id)
    {
        try {
            // Get application details
            $application = DB::table('applications as a')
                ->select(
                    'a.*', 
                    'p.first_name', 
                    'p.middle_name', 
                    'p.last_name',
                    'prog.name as programme_name', 
                    'prog.code as programme_code', 
                    'u.email as email', 
                    'u.phone as phone'
                )
                ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
                ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
                ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
                ->where('a.id', $id)
                ->first();
            
            if (!$application) {
                return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
            }
            
            $email = $application->email;
            $phone = $application->phone;
            $fullName = trim($application->first_name . ' ' . $application->middle_name . ' ' . $application->last_name);
            
            // Check if letter already sent
            if ($application->admission_letter_sent_at) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Letter already sent on ' . Carbon::parse($application->admission_letter_sent_at)->format('d/m/Y H:i')
                ], 400);
            }
            
            $emailSent = false;
            $smsSent = false;
            $errors = [];
            
            // 1. Send Email
            if ($email) {
                try {
                    // Generate PDF
                    $pdf = PDF::loadView('admission.offers.letter-pdf', [
                        'application' => $application,
                        'date' => now()
                    ]);
                    
                    $pdfContent = $pdf->output();
                    $pdfFileName = 'Offer_Letter_' . $application->application_number . '.pdf';
                    
                    Mail::send('emails.admission-offer', [
                        'name' => $fullName,
                        'application_number' => $application->application_number,
                        'programme' => $application->programme_name,
                        'intake' => $application->intake ?? 'March',
                        'date' => now()->format('d F, Y')
                    ], function($message) use ($email, $fullName, $pdfContent, $pdfFileName) {
                        $message->to($email, $fullName)
                                ->subject('Admission Offer Letter - ' . $pdfFileName)
                                ->attachData($pdfContent, $pdfFileName, ['mime' => 'application/pdf']);
                    });
                    
                    $emailSent = true;
                    Log::info('Email sent to: ' . $email);
                } catch (\Exception $e) {
                    $errors[] = 'Email failed: ' . $e->getMessage();
                    Log::error('Email failed: ' . $e->getMessage());
                }
            } else {
                $errors[] = 'No email address';
            }
            
            // 2. Send SMS (Optional - uncomment when SMS is configured)
            if ($phone) {
                try {
                    $smsMessage = "Dear {$fullName}, Congratulations! You have been offered admission to {$application->programme_name} at St. Maximillian College. Your admission letter has been sent to your email. Regards, Admission Office.";
                    // $this->sendSMS($phone, $smsMessage); // Uncomment when SMS is configured
                    $smsSent = true;
                    Log::info('SMS would be sent to: ' . $phone);
                } catch (\Exception $e) {
                    $errors[] = 'SMS failed: ' . $e->getMessage();
                }
            }
            
            // Update application record
            DB::table('applications')
                ->where('id', $id)
                ->update([
                    'admission_letter_sent_at' => now(),
                    'admission_letter_sent_by' => auth()->id(),
                    'status' => 'offer_sent',
                    'updated_at' => now()
                ]);
            
            $message = "Offer letter sent successfully! ";
            if ($emailSent) $message .= "Email sent to {$email}. ";
            if ($smsSent) $message .= "SMS sent to {$phone}. ";
            if (!empty($errors)) $message .= " Warnings: " . implode('; ', $errors);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'email_sent' => $emailSent,
                'sms_sent' => $smsSent
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send letter: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to send letter: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk send offer letters
     */
    public function bulkSendLetters(Request $request)
    {
        try {
            $applicationIds = $request->input('application_ids', []);
            
            if (empty($applicationIds)) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'No applications selected.']);
                }
                return redirect()->back()->with('error', 'No applications selected.');
            }
            
            $sent = 0;
            $failed = 0;
            
            foreach ($applicationIds as $id) {
                try {
                    // Create a fake request to reuse sendLetter logic
                    $fakeRequest = new Request();
                    $result = $this->sendLetter($fakeRequest, $id);
                    $responseData = json_decode($result->getContent(), true);
                    
                    if ($responseData['success'] ?? false) {
                        $sent++;
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Bulk send failed for ID ' . $id . ': ' . $e->getMessage());
                }
            }
            
            $message = "{$sent} offer letters sent successfully.";
            if ($failed > 0) $message .= " {$failed} failed.";
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sent' => $sent,
                    'failed' => $failed
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Bulk send failed: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Bulk send failed: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Bulk send failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate offer letter (PDF)
     */
    public function generateLetter($id)
    {
        try {
            $application = DB::table('applications as a')
                ->select(
                    'a.*', 
                    'p.first_name', 
                    'p.middle_name', 
                    'p.last_name', 
                    'p.date_of_birth',
                    'prog.name as programme_name', 
                    'prog.code as programme_code',
                    'ay.name as academic_year_name'
                )
                ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
                ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
                ->leftJoin('academic_years as ay', 'a.academic_year_id', '=', 'ay.id')
                ->where('a.id', $id)
                ->first();
            
            if (!$application) {
                return redirect()->back()->with('error', 'Application not found.');
            }
            
            $pdf = PDF::loadView('admission.offers.letter-pdf', [
                'application' => $application,
                'date' => now()
            ]);
            
            return $pdf->download("Offer_Letter_{$application->application_number}.pdf");
            
        } catch (\Exception $e) {
            Log::error('Failed to generate PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate letter: ' . $e->getMessage());
        }
    }
    
    /**
     * Preview offer letter (HTML)
     */
    public function previewLetter($id)
    {
        try {
            $application = DB::table('applications as a')
                ->select(
                    'a.*', 
                    'p.first_name', 
                    'p.middle_name', 
                    'p.last_name', 
                    'p.date_of_birth',
                    'prog.name as programme_name', 
                    'prog.code as programme_code',
                    'ay.name as academic_year_name'
                )
                ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
                ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
                ->leftJoin('academic_years as ay', 'a.academic_year_id', '=', 'ay.id')
                ->where('a.id', $id)
                ->first();
            
            if (!$application) {
                return redirect()->back()->with('error', 'Application not found.');
            }
            
            return view('admission.offers.letter-preview', compact('application'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to preview letter: ' . $e->getMessage());
        }
    }
    
    /**
     * Display offer acceptance management page
     */
    public function offerAcceptance(Request $request)
    {
        $applications = DB::table('applications as a')
            ->select(
                'a.id', 
                'a.application_number', 
                'a.status', 
                'a.intake', 
                'a.selected_program_id', 
                'a.admission_letter_sent_at', 
                'a.admission_status', 
                'p.first_name', 
                'p.middle_name', 
                'p.last_name', 
                'u.phone as phone', 
                'u.email as email', 
                'prog.name as programme_name', 
                'prog.code as programme_code'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereIn('a.status', ['approved', 'registered'])
            ->whereNotNull('a.admission_letter_sent_at')
            ->orderByDesc('a.admission_letter_sent_at')
            ->paginate(20);
        
        $statistics = [
            'total_offers_sent' => DB::table('applications')->whereIn('status', ['approved', 'registered'])->whereNotNull('admission_letter_sent_at')->count(),
            'accepted' => DB::table('applications')->where('admission_status', 'admitted')->count(),
            'pending_response' => DB::table('applications')->whereIn('status', ['approved', 'registered'])->whereNotNull('admission_letter_sent_at')->whereNull('admission_status')->count(),
            'not_admitted' => DB::table('applications')->where('admission_status', 'not_admitted')->count()
        ];
        
        return view('admission.offers.acceptance', compact('applications', 'statistics'));
    }
    
    /**
     * Update offer acceptance status
     */
    public function updateAcceptance(Request $request, $id)
    {
        try {
            $request->validate([
                'admission_status' => 'required|in:admitted,not_admitted,pending_payment',
                'notes' => 'nullable|string'
            ]);
            
            DB::table('applications')->where('id', $id)->update([
                'admission_status' => $request->admission_status,
                'admission_date' => $request->admission_status === 'admitted' ? now() : null,
                'updated_at' => now()
            ]);
            
            return response()->json(['success' => true, 'message' => 'Acceptance status updated successfully.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Display enrollment management page
     */
    public function enrollment(Request $request)
    {
        $applications = DB::table('applications as a')
            ->select(
                'a.id', 
                'a.application_number', 
                'a.status', 
                'a.intake', 
                'a.selected_program_id', 
                'a.admission_status', 
                'a.admission_date', 
                'p.first_name', 
                'p.middle_name', 
                'p.last_name', 
                'u.phone as phone', 
                'u.email as email', 
                'prog.name as programme_name', 
                'prog.code as programme_code', 
                's.id as student_id', 
                's.registration_number'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->leftJoin('students as s', 'a.id', '=', 's.application_id')
            ->where('a.admission_status', 'admitted')
            ->whereNotIn('a.status', ['registered'])
            ->orderByDesc('a.admission_date')
            ->paginate(20);
        
        $statistics = [
            'total_admitted' => DB::table('applications')->where('admission_status', 'admitted')->count(),
            'enrolled' => DB::table('students')->count(),
            'pending_enrollment' => DB::table('applications')->where('admission_status', 'admitted')->whereNotIn('status', ['registered'])->count()
        ];
        
        return view('admission.offers.enrollment', compact('applications', 'statistics'));
    }
    
    /**
     * Mark as enrolled (create student record)
     */
    public function markEnrolled(Request $request, $id)
    {
        try {
            $application = DB::table('applications')->where('id', $id)->where('admission_status', 'admitted')->first();
            if (!$application) {
                return response()->json(['success' => false, 'message' => 'Application not found or not admitted.'], 404);
            }
            
            $existingStudent = DB::table('students')->where('application_id', $id)->first();
            if ($existingStudent) {
                return response()->json(['success' => false, 'message' => 'Student already enrolled with registration number: ' . $existingStudent->registration_number], 400);
            }
            
            $user = DB::table('users')->where('id', $application->user_id)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            
            $regNo = $this->generateRegistrationNumber($application->intake ?? 'March');
            
            $studentId = DB::table('students')->insertGetId([
                'user_id' => $user->id,
                'application_id' => $id,
                'registration_number' => $regNo,
                'programme_id' => $application->selected_program_id,
                'study_mode' => $application->study_mode ?? 'full_time',
                'intake' => $application->intake ?? 'March',
                'status' => 'active',
                'registered_by' => auth()->id(),
                'registration_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('applications')->where('id', $id)->update(['status' => 'registered', 'updated_at' => now()]);
            DB::table('users')->where('id', $user->id)->update(['registration_number' => $regNo, 'user_type' => 'student', 'updated_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Student enrolled successfully! Registration number: ' . $regNo,
                'registration_number' => $regNo,
                'student_id' => $studentId
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Enrollment failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Bulk enrollment
     */
    public function bulkEnroll(Request $request)
    {
        try {
            $applicationIds = $request->input('application_ids', []);
            if (empty($applicationIds)) {
                return redirect()->back()->with('error', 'No applications selected.');
            }
            
            $enrolled = 0;
            $failed = 0;
            
            foreach ($applicationIds as $id) {
                try {
                    $application = DB::table('applications')->where('id', $id)->where('admission_status', 'admitted')->first();
                    if ($application) {
                        $existingStudent = DB::table('students')->where('application_id', $id)->first();
                        if (!$existingStudent) {
                            $user = DB::table('users')->where('id', $application->user_id)->first();
                            if ($user) {
                                $regNo = $this->generateRegistrationNumber($application->intake ?? 'March');
                                DB::table('students')->insert([
                                    'user_id' => $user->id,
                                    'application_id' => $id,
                                    'registration_number' => $regNo,
                                    'programme_id' => $application->selected_program_id,
                                    'study_mode' => $application->study_mode ?? 'full_time',
                                    'intake' => $application->intake ?? 'March',
                                    'status' => 'active',
                                    'registered_by' => auth()->id(),
                                    'registration_date' => now(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                DB::table('applications')->where('id', $id)->update(['status' => 'registered', 'updated_at' => now()]);
                                DB::table('users')->where('id', $user->id)->update(['registration_number' => $regNo, 'user_type' => 'student', 'updated_at' => now()]);
                                $enrolled++;
                            } else {
                                $failed++;
                            }
                        } else {
                            $failed++;
                        }
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                }
            }
            
            return redirect()->back()->with('success', "{$enrolled} students enrolled successfully. {$failed} failed.");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Bulk enrollment failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate registration number
     */
    private function generateRegistrationNumber($intake = 'March')
    {
        $campus = '02';
        $year = date('Y');
        $intakeCode = $intake === 'September' ? '09' : '03';
        $count = DB::table('students')->whereYear('created_at', $year)->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "{$campus}.{$sequence}.{$intakeCode}.{$year}";
    }
}
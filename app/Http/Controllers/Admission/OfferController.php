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
                'c.phone as phone',
                'c.email as email',
                'c.phone_alternative',
                'prog.name as programme_name', 
                'prog.code as programme_code'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('application_contacts as c', 'a.id', '=', 'c.application_id')
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
                  ->orWhere('c.phone', 'like', "%{$search}%")
                  ->orWhere('c.email', 'like', "%{$search}%");
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
 * Send single offer letter via email and SMS - UPDATED SMS TEMPLATE
 */
public function sendLetter(Request $request, $id)
{
    try {
        Log::info('========== SEND LETTER STARTED ==========');
        Log::info('Application ID: ' . $id);
        
        // Get application details
        $application = DB::table('applications as a')
            ->select(
                'a.*', 
                'p.first_name', 
                'p.middle_name', 
                'p.last_name',
                'prog.name as programme_name', 
                'prog.code as programme_code',
                'c.email as email',
                'c.phone as phone',
                'c.phone_alternative'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->leftJoin('application_contacts as c', 'a.id', '=', 'c.application_id')
            ->where('a.id', $id)
            ->first();
        
        if (!$application) {
            Log::error('Application not found: ' . $id);
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
        }
        
        $email = $application->email;
        $phone = $application->phone;
        $fullName = trim($application->first_name . ' ' . $application->middle_name . ' ' . $application->last_name);
        
        // Generate confirmation code (can be application number or custom)
        $confirmationCode = $this->generateConfirmationCode($application);
        
        Log::info('Application data:', [
            'name' => $fullName,
            'email' => $email ?? 'NULL',
            'phone' => $phone ?? 'NULL',
            'confirmation_code' => $confirmationCode
        ]);
        
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
        if ($email && !empty($email)) {
            try {
                Log::info('Preparing to send email to: ' . $email);
                
                Mail::send('emails.admission-offer', [
                    'name' => $fullName,
                    'application_number' => $application->application_number,
                    'programme' => $application->programme_name,
                    'intake' => $application->intake ?? 'March',
                    'confirmation_code' => $confirmationCode,
                    
                    'date' => now()->format('d F, Y')
                ], function($message) use ($email, $fullName) {
                    $message->to($email, $fullName)
                            ->subject('Admission Offer Letter - St. Maximillian College');
                });
                
                $emailSent = true;
                Log::info('Email sent successfully to: ' . $email);
                
            } catch (\Exception $e) {
                $errorMsg = 'Email failed: ' . $e->getMessage();
                $errors[] = $errorMsg;
                Log::error($errorMsg);
            }
        } else {
            $errors[] = 'No email address found';
            Log::warning('No email address for application ID: ' . $id);
        }
        
        // 2. Send SMS - WITH NEW TEMPLATE
        if ($phone && !empty($phone)) {
            try {
                // Generate SMS using the new template
                $smsMessage = $this->formatSMSMessage($fullName, $application->programme_name, $confirmationCode);
                
                $smsResult = $this->sendSMS($phone, $smsMessage);
                
                if ($smsResult['success']) {
                    $smsSent = true;
                    Log::info('SMS sent to: ' . $phone);
                    Log::info('SMS content: ' . $smsMessage);
                } else {
                    $errors[] = 'SMS failed: ' . $smsResult['message'];
                    Log::error('SMS failed to ' . $phone . ': ' . $smsResult['message']);
                }
            } catch (\Exception $e) {
                $errors[] = 'SMS failed: ' . $e->getMessage();
                Log::error('SMS failed: ' . $e->getMessage());
            }
        } else {
            // Try alternative phone number
            if ($application->phone_alternative && !empty($application->phone_alternative)) {
                try {
                    $smsMessage = $this->formatSMSMessage($fullName, $application->programme_name, $confirmationCode);
                    
                    $smsResult = $this->sendSMS($application->phone_alternative, $smsMessage);
                    
                    if ($smsResult['success']) {
                        $smsSent = true;
                        Log::info('SMS sent to alternative phone: ' . $application->phone_alternative);
                    } else {
                        $errors[] = 'SMS failed to alternative number: ' . $smsResult['message'];
                    }
                } catch (\Exception $e) {
                    $errors[] = 'SMS failed to alternative number: ' . $e->getMessage();
                }
            } else {
                Log::warning('No phone number for ID: ' . $id);
            }
        }
        
        // Update application record
        DB::table('applications')
            ->where('id', $id)
            ->update([
                'admission_letter_sent_at' => now(),
                'admission_letter_sent_by' => auth()->id(),
                'workflow_stage' => 'offer_sent',
                'confirmation_code' => $confirmationCode, // Add this column to applications table
                'current_stage_updated_at' => now(),
                'updated_at' => now()
            ]);
        
        $message = "Offer letter ";
        if ($emailSent || $smsSent) {
            $message .= "sent successfully! ";
        } else {
            $message .= "failed to send! ";
        }
        
        if ($emailSent) $message .= "Email sent to {$email}. ";
        if ($smsSent) $message .= "SMS sent. ";
        if (!empty($errors)) $message .= " Warnings: " . implode('; ', $errors);
        
        Log::info('Send letter completed: ' . $message);
        Log::info('========== SEND LETTER ENDED ==========');
        
        return response()->json([
            'success' => ($emailSent || $smsSent),
            'message' => $message,
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
            'confirmation_code' => $confirmationCode,
            'sms_content' => $smsMessage ?? null,
            'errors' => $errors
        ]);
        
    } catch (\Exception $e) {
        Log::error('Failed to send letter: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false, 
            'message' => 'Failed to send letter: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Format SMS message according to the required style
 */
private function formatSMSMessage($fullName, $programmeName, $confirmationCode)
{
    // Convert programme name to UPPERCASE for emphasis
    $programmeUpper = strtoupper($programmeName);
    
    // Format the message exactly as requested
    $message = "Hello! " . strtoupper($fullName) . " Congratulation you've been selected to join ST. MAXIMILLIAN COLLEGE for program of " . $programmeUpper . " your confirmation code is " . $confirmationCode . " to confirm visit our website for more info don't hesitate to call us via 0782278027";
    
    // Ensure message is not too long (SMS limit is 160 characters per segment)
    // Most SMS providers handle long messages automatically
    if (strlen($message) > 918) {
        // Truncate if too long (though unlikely with this template)
        $message = substr($message, 0, 915) . "...";
    }
    
    return $message;
}

/**
 * Generate unique confirmation code for the applicant
 * Format: 2 letters from name + 3 letters from programme + 4 random numbers
 * Example: 42RAS2655
 */
private function generateConfirmationCode($application)
{
    // Get first 2 letters of first name (or use RA if not available)
    $nameCode = '';
    if (!empty($application->first_name)) {
        $nameCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $application->first_name), 0, 2));
    }
    if (strlen($nameCode) < 2) {
        $nameCode = 'XX';
    }
    
    // Get first 3 letters of programme name
    $programmeCode = '';
    if (!empty($application->programme_name)) {
        $programmeCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $application->programme_name), 0, 3));
    }
    if (strlen($programmeCode) < 3) {
        $programmeCode = 'STD';
    }
    
    // Generate 4 random numbers
    $randomNumbers = rand(1000, 9999);
    
    // Alternative: Use application number suffix
    $appSuffix = substr($application->application_number, -4);
    
    // Combine: nameCode + programmeCode + randomNumbers
    // Example: "LA" + "DIP" + "2655" = "LADIP2655"
    $code = $nameCode . $programmeCode . $randomNumbers;
    
    // Ensure code is not too long (max 15 characters)
    if (strlen($code) > 15) {
        $code = substr($code, 0, 15);
    }
    
    return strtoupper($code);
}
    /**
     * Send SMS via API - Configure based on your SMS provider
     * 
     * Supported providers example:
     * - Africa's Talking
     * - Twilio
     * - Infobip
     * - Vonage (Nexmo)
     * - Local Tanzanian providers (Selcom, Tigo, etc.)
     */
    /**
 * Send SMS - Simplified version without database dependency
 */
private function sendSMS($phoneNumber, $message)
{
    // Clean phone number
    $phoneNumber = $this->formatPhoneNumber($phoneNumber);
    
    try {
        // For testing - log the SMS
        Log::info("========== SMS WOULD BE SENT ==========");
        Log::info("To: {$phoneNumber}");
        Log::info("Message: {$message}");
        Log::info("=======================================");
        
        // Log to file
        $smsLogFile = storage_path('logs/sms_attempts.log');
        $logEntry = date('Y-m-d H:i:s') . " | TO: {$phoneNumber} | MSG: " . $message . "\n";
        file_put_contents($smsLogFile, $logEntry, FILE_APPEND);
        
        return ['success' => true, 'message' => 'SMS logged (test mode)'];
        
        // When ready for production with Africa's Talking:
        /*
        $username = env('AFRICASTALKING_USERNAME', 'sandbox');
        $apiKey = env('AFRICASTALKING_API_KEY');
        
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'SMS API not configured'];
        }
        
        $at = new \AfricasTalking\SDK\AfricasTalking($username, $apiKey);
        $sms = $at->sms();
        
        $result = $sms->send([
            'to' => $phoneNumber,
            'message' => $message,
            'from' => env('SMS_SENDER_ID', 'STMAXCOLL')
        ]);
        
        if ($result['status'] === 'success') {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        } else {
            $error = $result['data']->SMSMessageData->Recipients[0]->status ?? 'Unknown error';
            return ['success' => false, 'message' => $error];
        }
        */
        
    } catch (\Exception $e) {
        Log::error('SMS error: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Format phone number to international format
 */
private function formatPhoneNumber($phone)
{
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove leading zero if exists
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    
    // Add Tanzania country code
    if (substr($phone, 0, 3) !== '255') {
        $phone = '255' . $phone;
    }
    
    // Add plus sign
    return '+' . $phone;
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
            $results = [];
            
            foreach ($applicationIds as $id) {
                try {
                    // Create a fake request to reuse sendLetter logic
                    $fakeRequest = new Request();
                    $result = $this->sendLetter($fakeRequest, $id);
                    $responseData = json_decode($result->getContent(), true);
                    
                    if ($responseData['success'] ?? false) {
                        $sent++;
                        $results[] = ['id' => $id, 'status' => 'success', 'message' => $responseData['message']];
                    } else {
                        $failed++;
                        $results[] = ['id' => $id, 'status' => 'failed', 'message' => $responseData['message'] ?? 'Unknown error'];
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $results[] = ['id' => $id, 'status' => 'failed', 'message' => $e->getMessage()];
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
                    'failed' => $failed,
                    'results' => $results
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
                'ay.name as academic_year_name',
                'c.email as email',
                'c.phone as phone'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->leftJoin('academic_years as ay', 'a.academic_year_id', '=', 'ay.id')
            ->leftJoin('application_contacts as c', 'a.id', '=', 'c.application_id')
            ->where('a.id', $id)
            ->first();
        
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found.');
        }
        
        $confirmationCode = $application->confirmation_code ?? $this->generateConfirmationCode($application);
        
        $pdf = PDF::loadView('admission.offers.letter-pdf', [
            'application' => $application,
            'confirmation_code' => $confirmationCode,
            'date' => now()
        ]);
        
        return $pdf->download("Offer_Letter_{$application->application_number}.pdf");
        
    } catch (\Exception $e) {
        Log::error('Failed to generate PDF: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to generate letter: ' . $e->getMessage());
    }
}
    
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
                'ay.name as academic_year_name',
                'c.email as email',
                'c.phone as phone'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->leftJoin('academic_years as ay', 'a.academic_year_id', '=', 'ay.id')
            ->leftJoin('application_contacts as c', 'a.id', '=', 'c.application_id')
            ->where('a.id', $id)
            ->first();
        
        if (!$application) {
            return redirect()->back()->with('error', 'Application not found.');
        }
        
        // Generate confirmation code
        $confirmationCode = $application->confirmation_code ?? $this->generateConfirmationCode($application);
        
        return view('admission.offers.letter-preview', compact('application', 'confirmationCode'));
        
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
                'c.phone as phone', 
                'c.email as email', 
                'prog.name as programme_name', 
                'prog.code as programme_code'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('application_contacts as c', 'a.id', '=', 'c.application_id')
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
                'c.phone as phone', 
                'c.email as email', 
                'prog.name as programme_name', 
                'prog.code as programme_code', 
                's.id as student_id', 
                's.registration_number'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('application_contacts as c', 'a.id', '=', 'c.application_id')
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
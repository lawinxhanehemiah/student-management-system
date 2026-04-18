<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Admission\AdmissionLetterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admission\StudentRegistrationController;
use App\Http\Controllers\Admission\OfferController;

// =====================
// PUBLIC/AUTH ROUTES
// =====================

// =====================
// COMMON USER ROUTES (for all authenticated users)
// =====================
Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/change-password', [UserController::class, 'showChangePassword'])->name('change-password');
    Route::post('/{user}/update-password', [UserController::class, 'updatePassword'])->name('update-password');
    
    // Only for SuperAdmin/Admission_Officer
    Route::middleware(['role:SuperAdmin|Admission_Officer'])->group(function () {
        Route::get('/students/create', [UserController::class, 'createStudent'])->name('create.student');
        Route::get('/staff/create', [UserController::class, 'createStaff'])->name('create.staff');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        
        // Student AJAX
        Route::get('/students/ajax', [UserController::class, 'getStudents'])->name('students.ajax');
    });
});



// =====================
// ADMISSION OFFICER ROUTES
// =====================
Route::middleware(['auth', 'role:Admission_Officer'])->group(function() {
    // Dashboard
    Route::get('/admission/dashboard', [AdmissionController::class, 'index'])
        ->name('admission.dashboard');
    
    // ==============================================
    // STUDENT REGISTRATION ROUTES - NEW SECTION
    // ==============================================
    Route::prefix('admission/students')->name('admission.students.')->controller(StudentRegistrationController::class)->group(function() {
        // List all registered students
        Route::get('/', 'index')->name('index');
        
        // Registration form (with application lookup)
        Route::get('/register', 'create')->name('register');
        
        // Walk-in registration (without application)
        Route::get('/walkin', 'createWalkIn')->name('walkin');
        
        // AJAX: Get applicant details by application ID
        Route::get('/get-applicant', 'getApplicant')->name('get-applicant');
        
        // Store new student
        Route::post('/', 'store')->name('store');
        
        // View student details
        Route::get('/{id}', 'show')->name('show');
        
        // Edit student (optional)
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        
        // Student invoices
        Route::get('/{id}/invoices', 'invoices')->name('invoices');
        
        // Generate invoice for existing student
        Route::post('/{id}/generate-invoice', 'generateInvoice')->name('generate-invoice');
        
        // Record payment
        Route::post('/{id}/record-payment', 'recordPayment')->name('record-payment');
        
        // Student statement
        Route::get('/{id}/statement/{academicYear?}', 'studentStatement')->name('statement');
        
        // Export students list
        Route::get('/export/{format}', 'export')->name('export');
    });

    // Selection Routes
Route::prefix('admission/selection')->name('admission.selection.')->middleware(['auth'])->group(function () {
    Route::get('/ranking', [App\Http\Controllers\Admission\SelectionController::class, 'ranking'])->name('ranking');
    Route::post('/ranking/calculate', [App\Http\Controllers\Admission\SelectionController::class, 'calculateRanking'])->name('ranking.calculate');
    Route::get('/selected', [App\Http\Controllers\Admission\SelectionController::class, 'selected'])->name('selected');
    Route::post('/select/{id}', [App\Http\Controllers\Admission\SelectionController::class, 'selectApplicant'])->name('select');
    Route::post('/bulk-select', [App\Http\Controllers\Admission\SelectionController::class, 'bulkSelect'])->name('bulk-select');
    Route::post('/waitlist/{id}', [App\Http\Controllers\Admission\SelectionController::class, 'moveToWaitlist'])->name('waitlist');
    Route::post('/auto-select', [App\Http\Controllers\Admission\SelectionController::class, 'autoSelect'])->name('auto-select');
    Route::get('/export-ranking', [App\Http\Controllers\Admission\SelectionController::class, 'exportRanking'])->name('export-ranking');
});

// Offer Letters Routes
Route::prefix('admission/offers')->name('admission.offers.')->middleware(['auth'])->group(function () {
    // Offer letters management
    Route::get('/letters', [OfferController::class, 'offerLetters'])->name('letters');
    Route::post('/letter/send/{id}', [OfferController::class, 'sendLetter'])->name('letter.send');
    Route::post('/letters/bulk-send', [OfferController::class, 'bulkSendLetters'])->name('letters.bulk-send');
    Route::get('/letter/generate/{id}', [OfferController::class, 'generateLetter'])->name('letter.generate');
    Route::get('/letter/preview/{id}', [OfferController::class, 'previewLetter'])->name('letter.preview');
    
    // Offer acceptance
    Route::get('/acceptance', [OfferController::class, 'offerAcceptance'])->name('acceptance');
    Route::post('/acceptance/{id}', [OfferController::class, 'updateAcceptance'])->name('acceptance.update');
    
    // Enrollment
    Route::get('/enrollment', [OfferController::class, 'enrollment'])->name('enrollment');
    Route::post('/enroll/{id}', [OfferController::class, 'markEnrolled'])->name('enroll');
    Route::post('/bulk-enroll', [OfferController::class, 'bulkEnroll'])->name('bulk-enroll');
});    
// ===========================================
// INQUIRIES ROUTES
// ===========================================
Route::prefix('admission/inquiries')->name('admission.inquiries.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admission\InquiryController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admission\InquiryController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admission\InquiryController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\Admission\InquiryController::class, 'show'])->name('show');
    Route::put('/{id}', [App\Http\Controllers\Admission\InquiryController::class, 'update'])->name('update');
    Route::post('/{id}/follow-up', [App\Http\Controllers\Admission\InquiryController::class, 'addFollowUp'])->name('follow-up');
    Route::get('/follow-ups/logs', [App\Http\Controllers\Admission\InquiryController::class, 'followUpLogs'])->name('follow-ups');
    Route::get('/export', [App\Http\Controllers\Admission\InquiryController::class, 'export'])->name('export');
});

// ===========================================
// DOCUMENTS ROUTES
// ===========================================
Route::prefix('admission/documents')->name('admission.documents.')->middleware(['auth'])->group(function () {
    // Admission Letters
    Route::get('/admission-letters', [App\Http\Controllers\Admission\DocumentController::class, 'admissionLetters'])->name('admission-letters');
    
    // ID Card Requests
    Route::get('/id-card-requests', [App\Http\Controllers\Admission\DocumentController::class, 'idCardRequests'])->name('id-card-requests');
    Route::post('/id-card-requests', [App\Http\Controllers\Admission\DocumentController::class, 'createIdCardRequest'])->name('id-card-requests.store');
    Route::put('/id-card-requests/{id}', [App\Http\Controllers\Admission\DocumentController::class, 'updateIdCardRequest'])->name('id-card-requests.update');
    
    // Document Archive
    Route::get('/archive', [App\Http\Controllers\Admission\DocumentController::class, 'documentArchive'])->name('archive');
    Route::post('/upload', [App\Http\Controllers\Admission\DocumentController::class, 'uploadDocument'])->name('upload');
    Route::get('/download/{id}', [App\Http\Controllers\Admission\DocumentController::class, 'downloadDocument'])->name('download');
    Route::delete('/{id}', [App\Http\Controllers\Admission\DocumentController::class, 'deleteDocument'])->name('delete');
});
// ===========================================
// REPORTS ROUTES
// ===========================================
Route::prefix('admission/reports')->name('admission.reports.')->middleware(['auth'])->group(function () {
    Route::get('/statistics', [App\Http\Controllers\Admission\ReportController::class, 'statistics'])->name('statistics');
    Route::get('/program-statistics', [App\Http\Controllers\Admission\ReportController::class, 'programStatistics'])->name('program-statistics');
    Route::get('/export/statistics-pdf', [App\Http\Controllers\Admission\ReportController::class, 'exportStatisticsPDF'])->name('export.statistics-pdf');
    Route::get('/export/program-pdf', [App\Http\Controllers\Admission\ReportController::class, 'exportProgramStatisticsPDF'])->name('export.program-pdf');
    Route::get('/export/{type}/csv', [App\Http\Controllers\Admission\ReportController::class, 'exportCSV'])->name('export.csv');
});

// ===========================================
// SETTINGS ROUTES
// ===========================================
Route::prefix('admission/settings')->name('admission.settings.')->middleware(['auth'])->group(function () {
    // Calendar
    Route::get('/calendar', [App\Http\Controllers\Admission\SettingsController::class, 'admissionCalendar'])->name('calendar');
    Route::post('/calendar/event', [App\Http\Controllers\Admission\SettingsController::class, 'storeCalendarEvent'])->name('calendar.event.store');
    Route::put('/calendar/event/{id}', [App\Http\Controllers\Admission\SettingsController::class, 'updateCalendarEvent'])->name('calendar.event.update');
    Route::delete('/calendar/event/{id}', [App\Http\Controllers\Admission\SettingsController::class, 'deleteCalendarEvent'])->name('calendar.event.delete');
    Route::get('/calendar/events/json', [App\Http\Controllers\Admission\SettingsController::class, 'getCalendarEvents'])->name('calendar.events.json');
    Route::post('/calendar/intake-settings', [App\Http\Controllers\Admission\SettingsController::class, 'updateIntakeSettings'])->name('calendar.intake-settings');
    
    // Workflow
    Route::get('/workflow', [App\Http\Controllers\Admission\SettingsController::class, 'workflow'])->name('workflow');
    Route::post('/workflow/stage', [App\Http\Controllers\Admission\SettingsController::class, 'storeWorkflowStage'])->name('workflow.stage.store');
    Route::put('/workflow/stage/{id}', [App\Http\Controllers\Admission\SettingsController::class, 'updateWorkflowStage'])->name('workflow.stage.update');
    Route::delete('/workflow/stage/{id}', [App\Http\Controllers\Admission\SettingsController::class, 'deleteWorkflowStage'])->name('workflow.stage.delete');
    Route::post('/workflow/selection-criteria', [App\Http\Controllers\Admission\SettingsController::class, 'updateSelectionCriteria'])->name('workflow.selection-criteria');
    Route::post('/workflow/notification-template', [App\Http\Controllers\Admission\SettingsController::class, 'storeNotificationTemplate'])->name('workflow.notification.store');
    Route::put('/workflow/notification-template/{id}', [App\Http\Controllers\Admission\SettingsController::class, 'updateNotificationTemplate'])->name('workflow.notification.update');
    Route::delete('/workflow/notification-template/{id}', [App\Http\Controllers\Admission\SettingsController::class, 'deleteNotificationTemplate'])->name('workflow.notification.delete');
});

// ===========================================
// SUPPORT ROUTES
// ===========================================
Route::prefix('admission/support')->name('admission.support.')->middleware(['auth'])->group(function () {
    Route::get('/help-center', [App\Http\Controllers\Admission\SupportController::class, 'helpCenter'])->name('help-center');
    Route::get('/help-center/article/{id}', [App\Http\Controllers\Admission\SupportController::class, 'showArticle'])->name('article');
    Route::get('/faq', [App\Http\Controllers\Admission\SupportController::class, 'faq'])->name('faq');
    Route::post('/ticket', [App\Http\Controllers\Admission\SupportController::class, 'submitTicket'])->name('ticket.submit');
    Route::get('/system-status', [App\Http\Controllers\Admission\SupportController::class, 'systemStatus'])->name('system-status');
});

// ============================================
// ADMISSION APPLICATIONS ROUTES
// ============================================
Route::prefix('admission/applications')->name('admission.applications.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admission\ApplicationController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admission\ApplicationController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admission\ApplicationController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\Admission\ApplicationController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\Admission\ApplicationController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\Admission\ApplicationController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\Admission\ApplicationController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/workflow', [App\Http\Controllers\Admission\ApplicationController::class, 'updateWorkflowStage'])->name('workflow.update');
    Route::get('/{id}/workflow-history', [App\Http\Controllers\Admission\ApplicationController::class, 'getWorkflowHistory'])->name('workflow.history');
    Route::post('/bulk-workflow', [App\Http\Controllers\Admission\ApplicationController::class, 'bulkUpdateWorkflow'])->name('workflow.bulk');
    
    });

// NECTA Routes
Route::prefix('admission/necta')->name('admission.necta.')->middleware(['auth'])->group(function () {
    Route::post('/csee/fetch', [App\Http\Controllers\Admission\NectaController::class, 'fetchCseeResults'])->name('csee.fetch');
    Route::post('/acsee/fetch', [App\Http\Controllers\Admission\NectaController::class, 'fetchAcseeResults'])->name('acsee.fetch');
    Route::get('/check', [App\Http\Controllers\Admission\NectaController::class, 'checkConnectivity'])->name('check');
    Route::get('/years', [App\Http\Controllers\Admission\NectaController::class, 'getAvailableYears'])->name('years');
   Route::post('/admission/necta/csee/fetch', [App\Http\Controllers\Admission\NectaController::class, 'fetchCseeResults'])->name('admission.necta.csee.fetch');
   
    });


// ============================================
// ELIGIBILITY ROUTES
// ============================================
Route::prefix('admission/eligibility')->name('admission.eligibility.')->middleware(['auth'])->group(function () {
    Route::get('/rules', [App\Http\Controllers\Admission\EligibilityController::class, 'rules'])->name('rules');
    Route::post('/rules', [App\Http\Controllers\Admission\EligibilityController::class, 'storeRule'])->name('rules.store');
    Route::put('/rules/{id}', [App\Http\Controllers\Admission\EligibilityController::class, 'updateRule'])->name('rules.update');
    Route::delete('/rules/{id}', [App\Http\Controllers\Admission\EligibilityController::class, 'deleteRule'])->name('rules.delete');
    Route::post('/check/{applicationId}', [App\Http\Controllers\Admission\EligibilityController::class, 'checkApplication'])->name('check');
    Route::post('/batch-check', [App\Http\Controllers\Admission\EligibilityController::class, 'batchCheck'])->name('batch-check');
    Route::get('/history/{applicationId}', [App\Http\Controllers\Admission\EligibilityController::class, 'getHistory'])->name('history');
});

// ============================================
// INTAKE ROUTES
// ============================================
Route::prefix('admission/intakes')->name('admission.intakes.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admission\IntakeController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admission\IntakeController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admission\IntakeController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\Admission\IntakeController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\Admission\IntakeController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\Admission\IntakeController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\Admission\IntakeController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/assign-programme', [App\Http\Controllers\Admission\IntakeController::class, 'assignProgramme'])->name('assign-programme');
    Route::delete('/{intakeId}/programme/{programmeId}', [App\Http\Controllers\Admission\IntakeController::class, 'removeProgramme'])->name('remove-programme');
    Route::get('/active/current', [App\Http\Controllers\Admission\IntakeController::class, 'getActiveIntake'])->name('active');
});

// ============================================
// DOCUMENTS ROUTES
// ============================================
Route::prefix('admission/documents')->name('admission.documents.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admission\DocumentController::class, 'index'])->name('index');
    Route::get('/verification-queue', [App\Http\Controllers\Admission\DocumentController::class, 'verificationQueue'])->name('verification-queue');
    Route::post('/upload', [App\Http\Controllers\Admission\DocumentController::class, 'upload'])->name('upload');
    Route::post('/bulk-verify', [App\Http\Controllers\Admission\DocumentController::class, 'bulkVerify'])->name('bulk-verify');
    Route::post('/{id}/verify', [App\Http\Controllers\Admission\DocumentController::class, 'verify'])->name('verify');
    Route::get('/download/{id}', [App\Http\Controllers\Admission\DocumentController::class, 'download'])->name('download');
    Route::delete('/{id}', [App\Http\Controllers\Admission\DocumentController::class, 'destroy'])->name('destroy');
    Route::get('/student/{studentId}', [App\Http\Controllers\Admission\DocumentController::class, 'getStudentDocuments'])->name('student-documents');
    Route::get('/programme-requirements/{programmeId}', [App\Http\Controllers\Admission\DocumentController::class, 'getProgrammeRequirements'])->name('programme-requirements');
Route::get('/search-applications', [App\Http\Controllers\Admission\DocumentController::class, 'searchApplications'])->name('search-applications');
    });
    // ==============================================
    // ALIAS ROUTES FOR EASY ACCESS (shorter URLs)
    // ==============================================
    // These provide alternative URLs that might be easier to remember
    
    // Alias for student registration
    Route::get('/admission/register-student', function() {
        return redirect()->route('admission.students.register');
    })->name('admission.register-student');
    
    // Alias for all students
    Route::get('/admission/all-students', function() {
        return redirect()->route('admission.students.index');
    })->name('admission.all-students');
    
    // ==============================================
    // APPLICATION MANAGEMENT
    // ==============================================
    
    // New Application for Admission Officer
    Route::get('/admission/applications/create', [ApplicationController::class, 'createForAdmission'])
        ->name('admission.applications.create');
    
    // Save steps routes
    Route::post('/admission/applications/save-step1', [ApplicationController::class, 'saveStep1'])
        ->name('admission.applications.save-step1');
    Route::post('/admission/applications/save-personal', [ApplicationController::class, 'savePersonal'])
        ->name('admission.applications.save-personal');
    Route::post('/admission/applications/save-contact', [ApplicationController::class, 'saveContact'])
        ->name('admission.applications.save-contact');
    Route::post('/admission/applications/save-next-of-kin', [ApplicationController::class, 'saveNextOfKin'])
        ->name('admission.applications.save-next-of-kin');
    Route::post('/admission/applications/save-academics', [ApplicationController::class, 'saveAcademics'])
        ->name('admission.applications.save-academics');
    Route::post('/admission/applications/save-programs', [ApplicationController::class, 'savePrograms'])
        ->name('admission.applications.save-programs');
    Route::post('/admission/applications/submit', [ApplicationController::class, 'submit'])
        ->name('admission.applications.submit');

    // All Applications
    Route::get('/admission/applicants', [ApplicationController::class, 'allApplications'])
        ->name('admission.applicants.index');
    
    // Pending Review
    Route::get('/admission/applicants/pending-review', [ApplicationController::class, 'pendingReview'])
        ->name('admission.applicants.pending-review');
    
    // Under Review
    Route::get('/admission/applicants/under-review', [ApplicationController::class, 'underReview'])
        ->name('admission.applicants.under-review');
    
    // Approved Applications
    Route::get('/admission/applicants/approved', [ApplicationController::class, 'approvedApplications'])
        ->name('admission.applicants.approved');
    
    // Rejected Applications
    Route::get('/admission/applicants/rejected', [ApplicationController::class, 'rejectedApplications'])
        ->name('admission.applicants.rejected');
    
    // Waitlisted
    Route::get('/admission/applicants/waitlisted', [ApplicationController::class, 'waitlistedApplications'])
        ->name('admission.applicants.waitlisted');
    
    // View single application
    Route::get('/admission/applicants/{id}', [ApplicationController::class, 'viewApplication'])
        ->name('admission.applicants.show');
    
    // Take action on application
    Route::post('/admission/applicants/{id}/approve', [ApplicationController::class, 'approveApplication'])
        ->name('admission.applicants.approve');
    Route::post('/admission/applicants/{id}/reject', [ApplicationController::class, 'rejectApplication'])
        ->name('admission.applicants.reject');
    Route::post('/admission/applicants/{id}/waitlist', [ApplicationController::class, 'waitlistApplication'])
        ->name('admission.applicants.waitlist');
    
    // Revoke approval (new)
    Route::post('/admission/applicants/{id}/revoke', [ApplicationController::class, 'revokeApproval'])
        ->name('admission.applicants.revoke');
    Route::post('/admission/applicants/bulk-revoke', [ApplicationController::class, 'bulkRevoke'])
        ->name('admission.applicants.bulk-revoke');
    
    // Bulk actions
    Route::post('/admission/applicants/bulk-actions', [ApplicationController::class, 'bulkActions'])
        ->name('admission.applicants.bulk-actions');

    // Program choices for approval
    Route::get('/admission/applicants/{id}/program-choices', [ApplicationController::class, 'getProgramChoices'])
        ->name('admission.applicants.program-choices');
    
    // NECTA fetch route for admission officer
    Route::post('/admission/applications/fetch-necta', [ApplicationController::class, 'fetchNectaResults'])
        ->name('admission.applications.fetch-necta');
    
    // ==============================================
    // ADMISSION LETTER ROUTES
    // ==============================================
    
    // Send admission letter to single applicant
    Route::post('/admission/applicants/{id}/send-letter', [AdmissionLetterController::class, 'sendLetter'])
        ->name('admission.applicants.send-letter');
    
    // Resend admission letter
    Route::post('/admission/applicants/{id}/resend-letter', [AdmissionLetterController::class, 'resendLetter'])
        ->name('admission.applicants.resend-letter');
    
    // Generate/Download admission letter (PDF)
    Route::get('/admission/applicants/{id}/generate-letter', [AdmissionLetterController::class, 'generateLetter'])
        ->name('admission.applicants.generate-letter');
    
    // Preview admission letter (HTML)
    Route::get('/admission/applicants/{id}/preview-letter', [AdmissionLetterController::class, 'previewLetter'])
        ->name('admission.applicants.preview-letter');
    
    // Send letters to multiple selected applicants
    Route::post('/admission/applicants/bulk-send-letters', [AdmissionLetterController::class, 'bulkSendLetters'])
        ->name('admission.applicants.bulk-send-letters');
    
    // Send letters to ALL approved applicants without letters
    Route::post('/admission/applicants/send-all-letters', [AdmissionLetterController::class, 'sendAllLetters'])
        ->name('admission.applicants.send-all-letters');
    
    // Export approved applications
    Route::get('/admission/applicants/approved/export/excel', [ApplicationController::class, 'exportApprovedExcel'])
        ->name('admission.applicants.export.approved.excel');
    
    Route::get('/admission/applicants/approved/export/pdf', [ApplicationController::class, 'exportApprovedPdf'])
        ->name('admission.applicants.export.approved.pdf');
    
    // ==============================================
    // ADDITIONAL USEFUL ROUTES
    // ==============================================
    
    // Admission statistics
    Route::get('/admission/statistics', [AdmissionController::class, 'statistics'])
        ->name('admission.statistics');
    
    // Admission reports
    Route::get('/admission/reports', [AdmissionController::class, 'reports'])
        ->name('admission.reports');
    
    // Manage programs
    Route::get('/admission/programs', [AdmissionController::class, 'programs'])
        ->name('admission.programs');
    
    // Settings
    Route::get('/admission/settings', [AdmissionController::class, 'settings'])
        ->name('admission.settings');
    
    // Search applications
    Route::get('/admission/applicants/search', [ApplicationController::class, 'searchApplications'])
        ->name('admission.applicants.search');

    // Send admission letter form (GET request)
    Route::get('/admission/applicants/{id}/send-letter-form', [ApplicationController::class, 'showSendLetterForm'])
        ->name('admission.applicants.send-letter-form');
});
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DpAcademicsController;

// =====================
// AUTH ROUTES (FOR STAFF/ADMIN)
// =====================

// Show login form
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        switch ($user->user_type ?? $user->role) {
            case 'SuperAdmin':
                return redirect()->route('superadmin.dashboard');
            case 'Director':
                return redirect()->route('director.dashboard');
            case 'Principal':
                return redirect()->route('principal.dashboard');
            case 'Deputy_Principal_Academics':
                return redirect()->route('dp-academics.dashboard');
            case 'Deputy_Principal_Administration':
                return redirect()->route('dp-admin.dashboard');
            case 'Head_of_Department':
                return redirect()->route('hod.dashboard');
            case 'Tutor':
                return redirect()->route('tutor.dashboard');
            case 'Examination_Officer':
                return redirect()->route('examination.dashboard');
            case 'Dean_of_Students':
                return redirect()->route('dean-students.dashboard');
            case 'Admission_Officer':
                return redirect()->route('admission.dashboard');
            case 'Records_Officer':
                return redirect()->route('records.dashboard');
            case 'Secretary':
                return redirect()->route('secretary.dashboard');
            case 'Financial_Controller':
                return redirect()->route('finance.dashboard');
            case 'Accountant':
                return redirect()->route('accountant.dashboard');
            case 'Procurement_Officer':
                return redirect()->route('procurement.dashboard');
            case 'ICT_Manager':
                return redirect()->route('ict.dashboard');
            case 'HR_Manager':
                return redirect()->route('hr.dashboard');
            case 'Librarian':
                return redirect()->route('library.dashboard');
            case 'Estate_Manager':
                return redirect()->route('estate.dashboard');
            case 'PR_Marketing_Officer':
                return redirect()->route('pr.dashboard');
            case 'Quality_Assurance_Manager':
                return redirect()->route('qa.dashboard');
            case 'Student':
                return redirect()->route('student.dashboard');
            case 'Applicant':
                return redirect()->route('applicant.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login');
        }
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [PasswordController::class, 'showChangeForm'])
        ->name('password.change.form');
    Route::post('/change-password', [PasswordController::class, 'updatePassword'])
        ->name('password.change.update');
});

// =====================
// ROLE DASHBOARDS
// =====================
Route::middleware(['auth','role:SuperAdmin'])
    ->get('/superadmin/dashboard', fn() => view('dashboards.superadmin'))
    ->name('superadmin.dashboard');

Route::middleware(['auth','role:Director'])
    ->get('/director/dashboard', fn() => view('dashboards.director'))
    ->name('director.dashboard');

Route::middleware(['auth','role:Principal'])
    ->get('/principal/dashboard', fn() => view('dashboards.principal'))
    ->name('principal.dashboard');

Route::middleware(['auth','role:Deputy_Principal_Academics'])
    ->get('/dp-academics/dashboard', fn() => view('dashboards.dp_academics'))
    ->name('dp-academics.dashboard');

Route::middleware(['auth','role:Deputy_Principal_Administration'])
    ->get('/dp-admin/dashboard', fn() => view('dashboards.dp_admin'))
    ->name('dp-admin.dashboard');

Route::middleware(['auth','role:Head_of_Department'])
    ->get('/hod/dashboard', fn() => view('dashboards.hod'))
    ->name('hod.dashboard');

Route::middleware(['auth','role:Tutor'])
    ->get('/tutor/dashboard', fn() => view('dashboards.tutor'))
    ->name('tutor.dashboard');

Route::middleware(['auth','role:Examination_Officer'])
    ->get('/examination/dashboard', fn() => view('dashboards.examination'))
    ->name('examination.dashboard');

Route::middleware(['auth','role:Dean_of_Students'])
    ->get('/dean-students/dashboard', fn() => view('dashboards.dean_students'))
    ->name('dean-students.dashboard');

// Include admission routes from separate file
require_once __DIR__.'/admission.php';

Route::middleware(['auth','role:Records_Officer'])
    ->get('/records/dashboard', fn() => view('dashboards.records'))
    ->name('records.dashboard');

Route::middleware(['auth','role:Secretary'])
    ->get('/secretary/dashboard', fn() => view('dashboards.secretary'))
    ->name('secretary.dashboard');

Route::middleware(['auth','role:Financial_Controller'])
    ->get('/finance/dashboard', fn() => view('dashboards.finance'))
    ->name('finance.dashboard');

Route::middleware(['auth','role:Accountant'])
    ->get('/accountant/dashboard', fn() => view('dashboards.accountant'))
    ->name('accountant.dashboard');

Route::middleware(['auth','role:Procurement_Officer'])
    ->get('/procurement/dashboard', fn() => view('dashboards.procurement'))
    ->name('procurement.dashboard');

Route::middleware(['auth','role:ICT_Manager'])
    ->get('/ict/dashboard', fn() => view('dashboards.ict'))
    ->name('ict.dashboard');

Route::middleware(['auth','role:HR_Manager'])
    ->get('/hr/dashboard', fn() => view('dashboards.hr'))
    ->name('hr.dashboard');

Route::middleware(['auth','role:Librarian'])
    ->get('/library/dashboard', fn() => view('dashboards.library'))
    ->name('library.dashboard');

Route::middleware(['auth','role:Estate_Manager'])
    ->get('/estate/dashboard', fn() => view('dashboards.estate'))
    ->name('estate.dashboard');

Route::middleware(['auth','role:PR_Marketing_Officer'])
    ->get('/pr/dashboard', fn() => view('dashboards.pr'))
    ->name('pr.dashboard');

Route::middleware(['auth','role:Quality_Assurance_Manager'])
    ->get('/qa/dashboard', fn() => view('dashboards.qa'))
    ->name('qa.dashboard');

Route::middleware(['auth','role:Student'])
    ->get('/student/dashboard', fn() => view('dashboards.student'))
    ->name('student.dashboard');

// =====================
// INCLUDE SUPERADMIN ROUTES
// =====================
require_once __DIR__.'/superadmin.php';

// =====================
// GENERAL ROUTES
// =====================
Route::get('/home', function () {
    return view('dashboard');
})->name('home');

// NECTA Routes
Route::get('/mock-necta-test', [ApplicationController::class, 'nectaMock']);
Route::get('/test/necta', function () {
    return app(ApplicationController::class)
        ->fetchNectaResults(
            request()->merge([
                'index_number' => 'S0123/4567/2022'
            ])
        );
});

Route::middleware(['auth'])->group(function () {
    Route::post('/necta/results', [ApplicationController::class, 'fetchNectaResults'])
        ->name('necta.results');
});

// =====================
// PAYMENT ROUTES - LARAVEL 11 STYLE
// =====================
Route::prefix('payments')->name('payments.')->group(function () {
    
    // Public routes (no auth required)
    Route::get('/public', [PaymentController::class, 'publicPaymentPage'])->name('public');
    Route::get('/invoice/{invoice}', [PaymentController::class, 'showPaymentPage'])->name('page');
    Route::get('/status/{controlNumber}', [PaymentController::class, 'checkPaymentStatus'])->name('status');
    
    // Authenticated routes with middleware applied here (not in controller)
    Route::middleware(['auth'])->group(function () {
        
        // Payment actions with throttle
        Route::middleware(['throttle:10,1'])->group(function () {
            Route::post('/invoice/{invoice}/control-number', [PaymentController::class, 'generateControlNumber'])
                ->name('generate-control-number');
            Route::post('/invoice/{invoice}/mobile-initiate', [PaymentController::class, 'initiateMobilePayment'])
                ->name('mobile-initiate');
        });
        
        // Manual payment (different throttle maybe)
        Route::middleware(['throttle:10,1'])->post('/invoice/{invoice}/manual', [PaymentController::class, 'processManualPayment'])
            ->name('manual');
        
        // Payment verification (requires permission)
        Route::middleware(['can:verify-payments'])->group(function () {
            Route::post('/verify/{payment}', [PaymentController::class, 'verifyManualPayment'])
                ->name('verify');
        });
        
        // Payment history and details (no throttle needed)
        Route::get('/history', [PaymentController::class, 'paymentHistory'])->name('history');
        Route::get('/{payment}', [PaymentController::class, 'showPayment'])->name('show');
        Route::get('/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('receipt');
        
        // Statistics (requires permission)
        Route::middleware(['can:view-payment-statistics'])->group(function () {
            Route::get('/statistics/data', [PaymentController::class, 'getStatistics'])->name('statistics.data');
        });
        
        // Export (requires permission)
        Route::middleware(['can:export-payments'])->group(function () {
            Route::post('/export', [PaymentController::class, 'exportReport'])->name('export');
        });
    });
});

// Webhook - NO AUTH, CSRF EXCLUDED (already in bootstrap/app.php)
Route::post('/webhooks/nmb/payment', [PaymentController::class, 'handleNMBWebhook'])
    ->name('webhooks.nmb.payment');

// Test route (remove after testing)
Route::get('/test-webhook', function() {
    try {
        $controller = app()->make(\App\Http\Controllers\PaymentController::class);
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'control_number' => '260217001252',
            'amount' => 600000,
            'transaction_id' => 'TXN_' . time(),
            'transaction_reference' => 'REF_' . time(),
            'payment_type' => 'bank',
            'notification_id' => 'NOTIF_' . time()
        ]);
        
        $request->headers->set('Content-Type', 'application/json');
        
        $result = $controller->handleNMBWebhook($request);
        return response()->json(['success' => true, 'data' => $result]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// ==============================================
// ADMISSION OFFICER APPLICATION MANAGEMENT ROUTES
// ==============================================
// Hizi routes zinampa Admission Officer uwezo wa ku-create, edit, na kumalizia draft applications

Route::middleware(['auth', 'role:Admission_Officer'])->prefix('admission-officer')->name('admission.officer.')->group(function () {
    
 Route::get('/applicants/search', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'searchApplicant'])
        ->name('applicants.search');
    // Dashboard for application management
    Route::get('/dashboard', function() {
        return redirect()->route('admission.officer.applications.index');
    })->name('dashboard');
    
    // Main application management
    Route::prefix('applications')->name('applications.')->group(function () {
        
        // List all applications
        Route::get('/', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'index'])
            ->name('index');
        
        // List draft applications only
        Route::get('/drafts', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'drafts'])
            ->name('drafts');
        
        // Create new application
        Route::get('/create', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'create'])
            ->name('create');
        
        Route::post('/', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'store'])
            ->name('store');
        
        // Edit application
        Route::get('/{id}/edit', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'edit'])
            ->name('edit');
        
        Route::put('/{id}', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'update'])
            ->name('update');
        
        // View application details
        Route::get('/{id}', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'show'])
            ->name('show');
        
        // Delete application (draft only)
        Route::delete('/{id}', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'destroy'])
            ->name('destroy');
        
        // Submit application on behalf of applicant
        Route::post('/{id}/submit', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'submit'])
            ->name('submit');
        
        // AJAX: Save individual step
        Route::post('/save-step', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'saveStep'])
            ->name('save-step');
        
        // AJAX: Fetch NECTA results
        Route::post('/fetch-necta', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'fetchNectaResults'])
            ->name('fetch-necta');
        
        // AJAX: Get eligible programmes
        Route::get('/{id}/eligible-programmes', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'getEligibleProgrammes'])
            ->name('eligible-programmes');
        
        // AJAX: Get application review summary
        Route::get('/{id}/review-summary', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'getReviewSummary'])
            ->name('review-summary');
        
        // Export applications
        Route::get('/export/{format}', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'export'])
            ->name('export');
        
        // Bulk actions
        Route::post('/bulk-delete', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'bulkDelete'])
            ->name('bulk-delete');
        
        Route::post('/bulk-submit', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'bulkSubmit'])
            ->name('bulk-submit');
            // Kwenye routes/web.php, kwenye group ya admission.officer
Route::get('/applicants/search', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'searchApplicant'])
    ->name('applicants.search');
    });
    
    // Get applicants list for dropdown (AJAX)
    Route::get('/applicants/list', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'getApplicantsList'])
        ->name('applicants.list');
    
    // Get academic years list (AJAX)
    Route::get('/academic-years', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'getAcademicYears'])
        ->name('academic-years');
    
    // Get programmes list (AJAX)
    Route::get('/programmes', [App\Http\Controllers\Admission\ApplicationManagementController::class, 'getProgrammes'])
        ->name('programmes');
});

// ==============================================
// ALTERNATIVE: Simple routes if you want to reuse Applicant's ApplicationController
// Hizi ni njia rahisi kama hutaki kuunda controller mpya
// ==============================================

/*
Route::middleware(['auth', 'role:Admission_Officer'])->prefix('admission/officer')->name('admission.officer.')->group(function () {
    
    // Reuse existing ApplicationController but with admission officer permissions
    Route::get('/applications', [App\Http\Controllers\Applicant\ApplicationController::class, 'allApplications'])
        ->name('applications.index');
    
    Route::get('/applications/drafts', [App\Http\Controllers\Applicant\ApplicationController::class, 'draftApplications'])
        ->name('applications.drafts');
    
    Route::get('/applications/create', [App\Http\Controllers\Applicant\ApplicationController::class, 'createForApplicant'])
        ->name('applications.create');
    
    Route::post('/applications', [App\Http\Controllers\Applicant\ApplicationController::class, 'storeForApplicant'])
        ->name('applications.store');
    
    Route::get('/applications/{id}/edit', [App\Http\Controllers\Applicant\ApplicationController::class, 'editForOfficer'])
        ->name('applications.edit');
    
    Route::put('/applications/{id}', [App\Http\Controllers\Applicant\ApplicationController::class, 'updateForOfficer'])
        ->name('applications.update');
    
    Route::post('/applications/{id}/submit', [App\Http\Controllers\Applicant\ApplicationController::class, 'submitForOfficer'])
        ->name('applications.submit');
    
    Route::delete('/applications/{id}', [App\Http\Controllers\Applicant\ApplicationController::class, 'destroyForOfficer'])
        ->name('applications.destroy');
    
    // Step save routes (reuse existing)
    Route::post('/applications/save-step1', [App\Http\Controllers\Applicant\ApplicationController::class, 'saveStep1'])
        ->name('applications.save-step1');
    Route::post('/applications/save-personal', [App\Http\Controllers\Applicant\ApplicationController::class, 'savePersonal'])
        ->name('applications.save-personal');
    Route::post('/applications/save-contact', [App\Http\Controllers\Applicant\ApplicationController::class, 'saveContact'])
        ->name('applications.save-contact');
    Route::post('/applications/save-next-of-kin', [App\Http\Controllers\Applicant\ApplicationController::class, 'saveNextOfKin'])
        ->name('applications.save-next-of-kin');
    Route::post('/applications/save-academics', [App\Http\Controllers\Applicant\ApplicationController::class, 'saveAcademics'])
        ->name('applications.save-academics');
    Route::post('/applications/save-programs', [App\Http\Controllers\Applicant\ApplicationController::class, 'savePrograms'])
        ->name('applications.save-programs');
    Route::post('/applications/save-documents', [App\Http\Controllers\Applicant\ApplicationController::class, 'saveDocuments'])
        ->name('applications.save-documents');
    
    // NECTA fetch
    Route::post('/applications/fetch-necta', [App\Http\Controllers\Applicant\ApplicationController::class, 'fetchNectaResults'])
        ->name('applications.fetch-necta');
    
    // Eligible programmes
    Route::get('/applications/{id}/eligible-programmes', [App\Http\Controllers\Applicant\ApplicationController::class, 'getEligibleProgrammesAjax'])
        ->name('applications.eligible-programmes');
});
*/
// Add this route before other application routes
Route::post('/admission/applications/init', [App\Http\Controllers\Admission\ApplicationController::class, 'initApplication'])->name('admission.applications.init');
Route::get('/dp/academics/dashboard', [DpAcademicsController::class, 'index'])
    ->name('dp.academics.dashboard')
    ->middleware(['auth', 'role:Deputy_Principal_Academics']);

    Route::post('/admission/applications/save-step', [App\Http\Controllers\Admission\ApplicationController::class, 'saveStep'])->name('admission.applications.save.step');


// =====================
// INCLUDE OTHER ROUTES
// =====================
require_once __DIR__.'/finance.php';
require_once __DIR__.'/applicant.php';
require_once __DIR__.'/admission.php';
require_once __DIR__.'/superadmin.php';
require_once __DIR__.'/api.php';
require_once __DIR__.'/hod.php';
require_once __DIR__.'/tutor.php';
require_once __DIR__.'/dpacademic.php';
require_once __DIR__.'/student.php';
require_once __DIR__.'/principal.php';
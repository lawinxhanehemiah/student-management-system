<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\AuthController;
use App\Http\Controllers\Applicant\DashboardController;
use App\Http\Controllers\Applicant\ApplicationController;
use App\Http\Controllers\Applicant\AdmissionLetterController;

use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Applicant\DownloadController;

// PUBLIC APPLICANT ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/applicant/login', [AuthController::class, 'showLogin'])->name('applicant.login');
    Route::post('/applicant/login', [AuthController::class, 'login'])->name('applicant.login.submit');
    
    Route::get('/applicant/register', [AuthController::class, 'showRegister'])->name('applicant.register');
    Route::post('/applicant/register', [AuthController::class, 'register'])->name('applicant.register.submit');
});

// PROTECTED APPLICANT ROUTES
Route::middleware(['auth'])->group(function () {
    // Dashboard and menu pages
    Route::prefix('applicant')->name('applicant.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Menu Pages
        Route::get('/personal-info', [DashboardController::class, 'personalInfo'])->name('personal.info');
        Route::get('/contact-info', [DashboardController::class, 'contactInfo'])->name('contact.info');
        Route::get('/next-of-kin', [DashboardController::class, 'nextOfKin'])->name('next.of.kin');
        Route::get('/academic-info', [DashboardController::class, 'academicInfo'])->name('academic.info');
        Route::get('/program-info', [DashboardController::class, 'programInfo'])->name('program.info');
        Route::get('/preview-submit', [DashboardController::class, 'previewSubmit'])->name('preview.submit');
        
        // ADMISSION LETTER ROUTES
        Route::get('/admission-letter', [AdmissionLetterController::class, 'show'])
            ->name('admission-letter');
        Route::get('/admission-letter/download', [AdmissionLetterController::class, 'download'])
            ->name('admission-letter.download');
        Route::post('/admission-letter/request', [AdmissionLetterController::class, 'requestLetter'])
            ->name('admission-letter.request');
        
        // APPLICATION STATUS
        Route::get('/application-status', [DashboardController::class, 'applicationStatus'])
            ->name('application.status');
        
       

             // Download Form routes
    Route::get('/download-form/{id?}', [\App\Http\Controllers\Applicant\DownloadController::class, 'show'])
        ->name('download.form');
    Route::get('/download-form/{id}/generate-pdf', [\App\Http\Controllers\Applicant\DownloadController::class, 'generatePDF'])
        ->name('download.generate-pdf');
    Route::get('/download-form/{id}/preview', [\App\Http\Controllers\Applicant\DownloadController::class, 'preview'])
        ->name('download.preview');
        
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
    
    // Application routes
    Route::prefix('applicant/application')->name('applicant.application.')->group(function () {
        // Start and form display
        Route::get('/start', [ApplicationController::class, 'start'])->name('start');
        Route::get('/form/{id?}', [ApplicationController::class, 'showForm'])->name('form');
        
        // Save steps
        Route::post('/save-step1', [ApplicationController::class, 'saveStep1'])->name('save.step1');
        Route::post('/save-personal', [ApplicationController::class, 'savePersonal'])->name('save.personal');
        Route::post('/save-contact', [ApplicationController::class, 'saveContact'])->name('save.contact');
        Route::post('/save-next-of-kin', [ApplicationController::class, 'saveNextOfKin'])->name('save.next-of-kin');
        Route::post('/save-academics', [ApplicationController::class, 'saveAcademics'])->name('save.academics');
        Route::post('/save-programs', [ApplicationController::class, 'savePrograms'])->name('save.programs');
        Route::post('/save-documents', [ApplicationController::class, 'saveDocuments'])->name('save.documents');
        Route::post('/submit', [ApplicationController::class, 'submit'])->name('submit'); // FINAL SUBMISSION
        
        // Other routes
        Route::get('/{id}/review', [ApplicationController::class, 'getReviewSummary'])->name('review');
        Route::get('/draft/{id}/cancel', [ApplicationController::class, 'cancelDraft'])->name('cancel.draft');
        Route::post('/necta-lookup', [ApplicationController::class, 'fetchNectaResults'])->name('necta.lookup');
        Route::get('/{id}/details', [ApplicationController::class, 'show'])->name('details');
        
        

         // Route kwa admission letter
    Route::get('/applicant/admission-letter', function () {
        return view('applicant.admission-letter-show');
    })->name('applicant.admission-letter.show');
    
    Route::get('/applicant/admission-letter/download/{id}', [\App\Http\Controllers\Applicant\DashboardController::class, 'downloadAdmissionLetter'])
        ->name('applicant.admission-letter.download');
    });
    Route::get('/application/{application_id}/eligible-programmes', [ApplicationController::class, 'getEligibleProgrammesAjax'])
        ->name('applicant.application.eligible-programmes');
    
});


// ==========================================================
// TEMPORARY MIGRATION ROUTE (REMOVE AFTER USE)
// ==========================================================
Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    Route::get('/admin/migrate-control-numbers', function() {
        // Run migration script here or call controller
        $controller = new \App\Http\Controllers\SuperAdmin\PaymentController();
        return $controller->migrateControlNumbers();
    })->name('admin.migrate.control-numbers');
});
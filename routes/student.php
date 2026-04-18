<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ModuleController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\TimetableController;
use App\Http\Controllers\Student\ClearanceController;
use App\Http\Controllers\Student\PaymentInfoController;


// =====================
// STUDENT ROUTES
// =====================
Route::middleware(['auth', 'role:Student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // =====================
        // DASHBOARD ROUTES
        // =====================
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index');
            Route::get('/overview', [DashboardController::class, 'overview'])->name('overview');
            Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
        });

        // =====================
        // REGISTERED MODULES
        // =====================
        Route::prefix('modules')->name('modules.')->group(function () {
            Route::get('/registered', [ModuleController::class, 'registeredModules'])->name('registered');
            Route::get('/current', [ModuleController::class, 'currentModules'])->name('current');
            Route::get('/past', [ModuleController::class, 'pastModules'])->name('past');
        });

        // Alternative simple route for registered modules (as requested)
        Route::get('/registered-modules', [ModuleController::class, 'registeredModules'])->name('registered-modules');

        // =====================
        // RESULTS MANAGEMENT
        // =====================
        Route::prefix('results')->name('results.')->group(function () {
            Route::get('/', [ResultController::class, 'index'])->name('index');
            Route::get('/semester', [ResultController::class, 'semesterResults'])->name('semester');
            Route::get('/year', [ResultController::class, 'yearResults'])->name('year');
            Route::get('/transcript', [ResultController::class, 'transcript'])->name('transcript');
            Route::get('/transcript/download', [ResultController::class, 'downloadTranscript'])->name('transcript-download');
            Route::get('/supplementary', [ResultController::class, 'supplementaryResults'])->name('supplementary');
        });

        // =====================
        // ACADEMIC (Timetable, Calendar)
        // =====================
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable');
            Route::get('/timetable/weekly', [TimetableController::class, 'weekly'])->name('timetable-weekly');
            Route::get('/calendar', [TimetableController::class, 'calendar'])->name('calendar');
            Route::get('/exams', [TimetableController::class, 'examSchedule'])->name('exam-schedule');
        });

        // =====================
// FINANCE & PAYMENTS
// =====================
Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/', [PaymentInfoController::class, 'index'])->name('index');
    Route::get('/statement', [PaymentInfoController::class, 'statement'])->name('statement');
    Route::get('/outstanding', [PaymentInfoController::class, 'outstandingBalance'])->name('outstanding');
    Route::get('/print', [PaymentInfoController::class, 'printStatement'])->name('print');
    Route::get('/download', [PaymentInfoController::class, 'downloadPDF'])->name('download');
});

        // =====================
        // CLEARANCE
        // =====================
        Route::prefix('clearance')->name('clearance.')->group(function () {
            Route::get('/', [ClearanceController::class, 'index'])->name('index');
            Route::post('/apply', [ClearanceController::class, 'apply'])->name('apply');
            Route::get('/status', [ClearanceController::class, 'status'])->name('status');
        });

       // =====================
// PROFILE MANAGEMENT
// =====================
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
    Route::post('/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('upload-photo');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
});

// Registration Form (alias for profile edit)
Route::get('/registration-form', [ProfileController::class, 'edit'])->name('registration-form');

        // =====================
        // NOTIFICATIONS
        // =====================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [DashboardController::class, 'notifications'])->name('all');
            Route::post('/{id}/read', [DashboardController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [DashboardController::class, 'markAllRead'])->name('mark-all-read');
        });

        // =====================
        // API ROUTES (AJAX)
        // =====================
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/modules', [ModuleController::class, 'apiModules'])->name('modules');
            Route::get('/results/summary', [ResultController::class, 'apiResultSummary'])->name('results-summary');
            Route::get('/fee-balance', [PaymentController::class, 'apiFeeBalance'])->name('fee-balance');
        });

        // =====================
        // LEGACY/COMPATIBILITY ROUTES
        // =====================
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/home', [DashboardController::class, 'index'])->name('home');
    });
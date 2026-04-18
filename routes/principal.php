<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\PaymentAdjustmentRequestController;

// =====================
// PRINCIPAL MODULE ROUTES
// =====================
Route::middleware(['auth', 'role:Principal,SuperAdmin'])
    ->prefix('principal')
    ->name('principal.')
    ->group(function () {
        
        // ============ DASHBOARD ============
        // Route::get('/dashboard', [PrincipalController::class, 'dashboard'])->name('dashboard');
        
        // ============ PAYMENT ADJUSTMENT REQUESTS ============
        Route::prefix('payment-adjustments')->name('payment-adjustments.')->group(function () {
            // Orodha ya pending requests
            Route::get('/pending', [PaymentAdjustmentRequestController::class, 'pendingRequests'])
                ->name('pending');
            
            // Onyesha ombi moja
            Route::get('/{id}', [PaymentAdjustmentRequestController::class, 'show'])
                ->name('show');
            
            // Idhinisha ombi
            Route::post('/{id}/approve', [PaymentAdjustmentRequestController::class, 'approve'])
                ->name('approve');
            
            // Kataa ombi
            Route::post('/{id}/reject', [PaymentAdjustmentRequestController::class, 'reject'])
                ->name('reject');
        });
        
        // ============ STUDENT STATEMENTS (Principal View) ============
        Route::prefix('student-statements')->name('student-statements.')->group(function () {
            Route::get('/', [App\Http\Controllers\Finance\StudentStatementController::class, 'index'])->name('index');
            Route::post('/search', [App\Http\Controllers\Finance\StudentStatementController::class, 'search'])->name('search');
            Route::get('/{studentId}', [App\Http\Controllers\Finance\StudentStatementController::class, 'show'])->name('show');
            Route::get('/{studentId}/print', [App\Http\Controllers\Finance\StudentStatementController::class, 'print'])->name('print');
        });
        
        // ============ FINANCIAL REPORTS (Principal View Only) ============
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [App\Http\Controllers\Finance\FinanceController::class, 'revenueReport'])->name('revenue');
            Route::get('/outstanding', [App\Http\Controllers\Finance\FinanceController::class, 'outstandingReport'])->name('outstanding');
            Route::get('/collections', [App\Http\Controllers\Finance\FinanceController::class, 'collectionsByProgramme'])->name('collections');
        });
        
        // ============ PROFILE & SETTINGS ============
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [App\Http\Controllers\Finance\FinanceController::class, 'profile'])->name('index');
            Route::put('/', [App\Http\Controllers\Finance\FinanceController::class, 'updateProfile'])->name('update');
            Route::get('/password', [App\Http\Controllers\Finance\FinanceController::class, 'passwordForm'])->name('password');
            Route::post('/password', [App\Http\Controllers\Finance\FinanceController::class, 'updatePassword'])->name('password.update');
        });
        
    });
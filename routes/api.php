<?php
// routes/api.php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentResultController;

// M-Pesa routes
Route::prefix('mpesa')->group(function () {
    Route::post('initiate/{invoice}', [PaymentController::class, 'initiateMPesaPayment']);
    Route::post('callback', [PaymentController::class, 'mpesaCallback'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('status/{checkoutId}', [PaymentController::class, 'checkMPesaStatus']);
});

// Debug route (weka comment baada ya kumaliza debugging)
Route::get('mpesa-debug', function() {
    $service = app(\App\Services\MPesaPaymentService::class);
    return response()->json($service->debugConnection());
});

Route::middleware(['auth:sanctum'])->prefix('student-results')->group(function () {
    
    // Main CRUD
    Route::get('/', [StudentResultController::class, 'index']);
    Route::get('/filter-options', [StudentResultController::class, 'filterOptions']);
    Route::get('/{id}', [StudentResultController::class, 'show']);
    Route::post('/', [StudentResultController::class, 'store']);
    Route::put('/{id}', [StudentResultController::class, 'update']);
    Route::delete('/{id}', [StudentResultController::class, 'destroy']);
    
    // Workflow management
    Route::patch('/{id}/workflow-status', [StudentResultController::class, 'updateWorkflowStatus']);
    Route::post('/batches/{batchId}/approve', [StudentResultController::class, 'approveBatch']);
    
    // Bulk operations
    Route::post('/bulk-upload', [StudentResultController::class, 'bulkUpload']);
    
    // Transcript
    Route::get('/transcript/{studentId}', [StudentResultController::class, 'transcript']);
});
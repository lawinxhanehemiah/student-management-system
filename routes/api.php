<?php
// routes/api.php

use App\Http\Controllers\PaymentController;

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
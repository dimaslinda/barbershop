<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController; // Pastikan ini di-import

Route::get('/', function () {
    return view('pos');
});

// Ini sudah ada di PaymentController
// Route::get('/payment/success/{invoice_number}', [PaymentController::class, 'paymentSuccess']);
// Route::get('/payment/failed/{invoice_number}', [PaymentController::class, 'paymentFailed']);
// Route::get('/payment/pending/{invoice_number}', [PaymentController::class, 'paymentPending']);

// ... rute Filament admin
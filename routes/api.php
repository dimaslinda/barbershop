<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MidtransWebhookController;
use App\Models\Service;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rute untuk membuat transaksi QRIS
Route::post('/create-qris-transaction', [PaymentController::class, 'createTransaction']);

// Rute untuk callback (akan digunakan sebagai landing page setelah pembayaran)
Route::get('/payment/success/{invoice_number}', [PaymentController::class, 'paymentSuccess']);
Route::get('/payment/failed/{invoice_number}', [PaymentController::class, 'paymentFailed']);
Route::get('/payment/pending/{invoice_number}', [PaymentController::class, 'paymentPending']);

// Rute untuk membuat transaksi QRIS
Route::post('/create-qris-transaction', [PaymentController::class, 'createTransaction']);

// Rute Webhook Midtrans
Route::post('/midtrans-webhook', [MidtransWebhookController::class, 'handler']);

// Rute API untuk mendapatkan daftar layanan
Route::get('/services', function () {
    return response()->json(Service::where('is_active', true)->get());
});

// --- Rute API Baru untuk Polling Status Transaksi ---
Route::get('/transaction-status/{invoice_number}', [PaymentController::class, 'getTransactionStatus']);

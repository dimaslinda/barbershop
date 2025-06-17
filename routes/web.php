<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Service; // Pastikan ini ada jika digunakan di rute API

require __DIR__ . '/auth.php'; // Memuat rute autentikasi Laravel Breeze

// --- GRUP RUTE UNTUK WEB YANG MEMBUTUHKAN AUTENTIKASI SESI ---
// Pengguna harus login (melalui Breeze) untuk mengakses halaman ini.
Route::middleware(['auth'])->group(function () {

    // Ini adalah rute default Breeze setelah login.
    // Kita arahkan semua user yang landas di sini ke pos.home.
    // Pemisahan admin/non-admin ditangani oleh getLoginRedirectUrl() di AdminPanelProvider.
    Route::get('/dashboard', function () {
        return redirect()->route('pos.home');
    })->name('dashboard');

    // Rute utama aplikasi POS
    Route::get('/', function () {
        $user = Auth::user();

        // --- PERBAIKAN: Seluruh blok validasi `if (!$user->branch)` DIHAPUS DARI SINI ---
        // Dengan ini, semua user yang login akan bisa mengakses halaman POS.
        // Penanganan user tanpa cabang (misal admin pusat atau user yang tidak terhubung)
        // akan dilakukan di view pos.blade.php dan di PaymentController untuk aksi pembuatan transaksi.

        return view('pos', [
            // selectedBranchId dan selectedBranchCode akan null jika $user->branch null
            'selectedBranchId' => $user->branch ? $user->branch->id : null,
            'selectedBranchCode' => $user->branch ? $user->branch->code : 'UNASSIGNED', // Placeholder jika tidak ada cabang
        ]);
    })->name('pos.home');
});

// --- Rute Callback Midtrans (Tidak Membutuhkan Autentikasi) ---
Route::get('/payment/success/{invoice_number}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/failed/{invoice_number}', [PaymentController::class, 'paymentFailed'])->name('payment.failed');
Route::get('/payment/pending/{invoice_number}', [PaymentController::class, 'paymentPending'])->name('payment.pending');

// --- Rute API (Membutuhkan Autentikasi Web/Sesi) ---
Route::middleware(['auth:web'])->group(function () {
    Route::post('/api/create-qris-transaction', [PaymentController::class, 'createTransaction']);
    Route::get('/api/services', function () {
        return response()->json(Service::where('is_active', true)->get());
    });
    Route::get('/api/transaction-status/{invoice_number}', [App\Http\Controllers\PaymentController::class, 'getTransactionStatus']);
});

// Rute admin Filament secara otomatis terdaftar oleh AdminPanelProvider.
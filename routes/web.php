<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Rute utama aplikasi POS (hanya bisa diakses jika sudah login)
Route::middleware(['auth:sanctum', 'verified'])->group(function () { // Atau middleware 'auth' Laravel biasa
    Route::get('/', function () {
        // Ambil data cabang dari user yang sedang login
        $userBranch = Auth::user()->branch;

        if (!$userBranch && Auth::user()->email !== 'admin@admin.com') {
            // Jika user bukan admin dan tidak terhubung ke cabang,
            // mungkin arahkan ke halaman error atau info
            return redirect('/admin')->with('error', 'Akun Anda tidak terhubung ke cabang.');
        }

        return view('pos', [
            'selectedBranchId' => $userBranch ? $userBranch->id : null,
            'selectedBranchCode' => $userBranch ? $userBranch->code : 'GLOBAL', // Atau kode default untuk admin
        ]);
    })->name('pos.home');
});

// Ini sudah ada di PaymentController
// Route::get('/payment/success/{invoice_number}', [PaymentController::class, 'paymentSuccess']);
// Route::get('/payment/failed/{invoice_number}', [PaymentController::class, 'paymentFailed']);
// Route::get('/payment/pending/{invoice_number}', [PaymentController::class, 'paymentPending']);

// ... rute Filament admin
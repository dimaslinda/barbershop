<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
// use App\Models\User; // Tidak perlu diimport secara eksplisit jika hanya digunakan untuk type-hinting Auth::user()

class Dashboard extends BaseDashboard
{
    /**
     * Metode ini menentukan apakah halaman Dashboard bisa diakses.
     * Harus dideklarasikan sebagai STATIC.
     * Mengembalikan TRUE hanya jika pengguna adalah admin.
     */
    public static function canAccess(): bool
    {
        // Pastikan Anda telah MENGHAPUS/mengomentari semua dd() atau log debugging yang ada di metode ini.

        // Hanya izinkan user yang adalah admin untuk mengakses halaman dashboard ini.
        // Jika Auth::user() adalah non-admin, isAdmin() akan FALSE, sehingga akses ditolak.
        return Auth::user()->isAdmin();
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth; // Import untuk Auth::user()
use App\Models\User; // Opsional: Untuk type-hinting jika diperlukan, tapi tidak wajib untuk Auth::user()

class Dashboard extends BaseDashboard
{
    /**
     * Metode ini menentukan apakah halaman ini bisa diakses.
     * Harus dideklarasikan sebagai STATIC agar kompatibel dengan Filament\Pages\Page.
     *
     * @return bool
     */
    public static function canAccess(): bool // <-- PERUBAHAN DI SINI: TAMBAHKAN 'static'
    {
        /** @var User|null $user */
        // Auth::user() dapat diakses di metode static
        $user = Auth::user();

        // Jika tidak ada user yang terautentikasi (seharusnya tidak terjadi setelah login), tolak akses.
        if (!$user) {
            return false;
        }

        // Hanya izinkan pengguna yang adalah admin untuk mengakses halaman dashboard ini.
        // Asumsi metode isAdmin() ada di model App\Models\User.php
        return $user->isAdmin();
    }
}

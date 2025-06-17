<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
// use App\Models\User; // Tidak perlu diimport secara eksplisit jika hanya digunakan untuk type-hinting Auth::user()

class Dashboard extends BaseDashboard
{
    /**
     * Metode ini menentukan apakah halaman Dashboard bisa diakses.
     * Hanya admin yang diizinkan mengakses dashboard Filament.
     */
    public static function canAccess(): bool
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return false;
        }

        // Pastikan user adalah admin
        if (!Auth::user()->isAdmin()) {
            return false;
        }

        return true;
    }
}

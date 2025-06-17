<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        // Hapus 'email_verified_at' => 'datetime', jika Anda sudah menghapus kolomnya dari DB.
        // Jika kolomnya masih ada tapi tidak digunakan untuk verifikasi, biarkan saja.
        // Jika sudah hapus dari DB, pastikan baris ini dihapus juga.
        'password' => 'hashed',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Ini adalah metode yang menentukan apakah user bisa mengakses panel Filament (Login Berhasil).
     *
     * Opsi yang disarankan:
     * - Izinkan semua user yang memiliki branch_id ATAU user admin untuk login ke panel.
     * Ini agar akun cabang bisa login ke admin panel Filament untuk diarahkan ke POS.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Izinkan user login ke panel Filament jika dia admin ATAU jika dia terhubung ke cabang.
        return $this->isAdmin() || ($this->branch_id !== null);
    }

    /**
     * Metode ini mengidentifikasi user sebagai admin pusat.
     */
    public function isAdmin(): bool
    {
        // Logika ini sudah benar untuk identifikasi admin pusat
        return $this->email === 'admin@admin.com';

        // Pastikan Anda telah MENGHAPUS/mengomentari semua dd() atau log debugging yang ada di metode ini.
    }
}

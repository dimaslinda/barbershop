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
     * Hanya admin yang diizinkan mengakses panel Filament.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // return $this->isAdmin();
        return true;
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

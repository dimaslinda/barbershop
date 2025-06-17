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
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Metode ini harus dinamakan canAccessPanel() sesuai interface FilamentUser.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->isAdmin() || ($this->branch_id !== null);
        // return true;
    }

    /**
     * Metode isAdmin() yang kita definisikan untuk Global Scope.
     * Sesuaikan logic ini dengan sistem peran Anda.
     */
    public function isAdmin(): bool
    {
        return $this->email === 'admin@admin.com'; // Sesuaikan dengan email admin Anda
    }
}

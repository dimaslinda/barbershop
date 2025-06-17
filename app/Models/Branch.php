<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
    ];

    /**
     * Get the transactions for the branch.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the services for the branch (jika setiap cabang memiliki daftar layanan sendiri)
     * Untuk project ini, kita asumsikan layanan bersifat global dulu,
     * tapi ini bisa dikembangkan jika ada kebutuhan.
     */
    // public function services(): HasMany
    // {
    //     return $this->hasMany(Service::class);
    // }
}

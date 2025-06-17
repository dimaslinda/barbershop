<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\BranchScope;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'invoice_number',
        'total_amount',
        'payment_method',
        'payment_status',
        'midtrans_transaction_id',
        'midtrans_order_id',
        'midtrans_qr_code_url',
    ];

    // Menerapkan Global Scope
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Get the branch that owns the transaction.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

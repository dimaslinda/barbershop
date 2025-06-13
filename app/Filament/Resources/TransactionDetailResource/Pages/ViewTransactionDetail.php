<?php

namespace App\Filament\Resources\TransactionDetailResource\Pages;

use App\Filament\Resources\TransactionDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransactionDetail extends ViewRecord
{
    protected static string $resource = TransactionDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(), // Detail transaksi umumnya tidak diedit
        ];
    }
}

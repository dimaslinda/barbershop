<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionDetailsRelationManager extends RelationManager
{
    // Nama relasi di model Transaction.php
    protected static string $relationship = 'transactionDetails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->label('Layanan')
                    ->disabled() // <-- PERBAIKAN DI SINI
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->disabled() // <-- PERBAIKAN DI SINI
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled() // <-- PERBAIKAN DI SINI
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled() // <-- PERBAIKAN DI SINI
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // Title untuk setiap record, ambil dari nama layanan
            ->recordTitleAttribute('service.name')
            ->columns([
                Tables\Columns\TextColumn::make('service.name') // Menampilkan nama layanan dari relasi Service
                    ->label('Layanan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->money('IDR') // Format sebagai mata uang Rupiah
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR') // Format sebagai mata uang Rupiah
                    ->sortable(),
            ])
            ->filters([
                // Kamu bisa menambahkan filter jika diperlukan, misalnya berdasarkan layanan
            ])
            ->headerActions([
                // Kita tidak akan menambahkan 'CreateAction' di sini karena detail dibuat saat transaksi
            ])
            ->actions([
                // Kita tidak akan menambahkan 'EditAction' atau 'DeleteAction' di sini
                // karena detail transaksi seharusnya tidak diedit/dihapus secara langsung dari relasi
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}

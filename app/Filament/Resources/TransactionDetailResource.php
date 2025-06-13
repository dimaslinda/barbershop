<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionDetailResource\Pages;
use App\Filament\Resources\TransactionDetailResource\RelationManagers;
use App\Models\TransactionDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionDetailResource extends Resource
{
    protected static ?string $model = TransactionDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet'; // Icon untuk detail list
    protected static ?string $navigationGroup = 'Manajemen Penjualan';
    protected static ?string $modelLabel = 'Detail Transaksi';
    protected static ?int $navigationSort = 2; // Urutan di bawah Transaksi

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('transaction_id')
                    ->relationship('transaction', 'invoice_number') // Tampilkan nomor invoice
                    ->label('Transaksi (Invoice)')
                    ->disabled() // Tidak bisa diubah
                    ->required(),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name') // Tampilkan nama layanan
                    ->label('Layanan')
                    ->disabled() // Tidak bisa diubah
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->disabled() // Tidak bisa diubah
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled() // Tidak bisa diubah
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled() // Tidak bisa diubah
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->url(fn(TransactionDetail $record): string => TransactionResource::getUrl('view', ['record' => $record->transaction_id])), // Link ke halaman view transaksi
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Detail')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_id')
                    ->label('Filter Berdasarkan Transaksi')
                    ->relationship('transaction', 'invoice_number')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Filter Berdasarkan Layanan')
                    ->relationship('service', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(), // Umumnya tidak diedit
                // Tables\Actions\DeleteAction::make(), // Umumnya tidak dihapus
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(), // Dibuat otomatis saat transaksi
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionDetails::route('/'),
            'create' => Pages\CreateTransactionDetail::route('/create'),
            'view' => Pages\ViewTransactionDetail::route('/{record}'),
            'edit' => Pages\EditTransactionDetail::route('/{record}/edit'),
        ];
    }
}

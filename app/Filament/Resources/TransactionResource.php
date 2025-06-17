<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Manajemen Penjualan';
    protected static ?string $modelLabel = 'Transaksi';
    protected static ?string $pollingInterval = '5s'; // Pastikan ini static string
    protected ?string $heading = 'Ringkasan Transaksi'; // Pastikan ini non-static string

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id') // Tambahkan ini
                    ->relationship('branch', 'name') // Menampilkan nama cabang dari relasi
                    ->label('Cabang')
                    ->readOnly() // Cabang tidak bisa diedit setelah transaksi dibuat
                    ->required(),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->unique(ignoreRecord: true)
                    ->readOnly()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Jumlah Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly(),
                Forms\Components\TextInput::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->readOnly(),
                Forms\Components\Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Berhasil',
                        'failed' => 'Gagal',
                        'expire' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                        'challenge' => 'Tantangan',
                        'refunded' => 'Dikembalikan',
                    ])
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('midtrans_transaction_id')
                    ->label('ID Transaksi Midtrans')
                    ->maxLength(255)
                    ->readOnly()
                    ->helperText('ID unik transaksi dari Midtrans.'),
                Forms\Components\TextInput::make('midtrans_order_id')
                    ->label('Order ID Midtrans')
                    ->maxLength(255)
                    ->readOnly()
                    ->helperText('Order ID yang dikirim ke Midtrans.'),
                Forms\Components\TextInput::make('midtrans_qr_code_url')
                    ->label('URL QR Code')
                    ->maxLength(255)
                    ->url()
                    ->readOnly()
                    ->visibleOn('view')
                    ->helperText('URL untuk menampilkan QRIS.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.code') // Tampilkan kode cabang
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Jumlah Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Bayar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed', 'expire', 'cancelled' => 'danger',
                        'challenge' => 'info',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('midtrans_transaction_id')
                    ->label('ID Transaksi Midtrans')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('branch_id') // Filter berdasarkan cabang
                    ->label('Filter Cabang')
                    ->relationship('branch', 'name')
                    ->searchable(),
                SelectFilter::make('payment_status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Berhasil',
                        'failed' => 'Gagal',
                        'expire' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                        'challenge' => 'Tantangan',
                        'refunded' => 'Dikembalikan',
                    ]),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

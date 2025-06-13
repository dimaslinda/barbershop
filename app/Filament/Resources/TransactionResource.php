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
    protected static ?string $modelLabel = 'Transaksi'; // Label di navigasi

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->disabled() // Data ini dihasilkan oleh sistem
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Pembayaran')
                    ->prefix('Rp')
                    ->disabled(), // Data ini dihitung
                Forms\Components\TextInput::make('payment_method')
                    ->disabled(),
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
                    ->disabled() // Status diupdate oleh webhook
                    ->required(),
                Forms\Components\TextInput::make('midtrans_transaction_id')
                    ->label('ID Transaksi Midtrans')
                    ->disabled(),
                Forms\Components\TextInput::make('midtrans_order_id')
                    ->label('Order ID Midtrans')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('midtrans_qr_code_url')
                    ->label('URL QR Code')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                        default => 'secondary', // Untuk status lain yang tidak terdefinisi
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
                Tables\Actions\EditAction::make(), // Mungkin dibutuhkan untuk kasus khusus, tetapi umumnya tidak diedit
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Hati-hati dengan menghapus transaksi!
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(), // Transaksi dibuat dari sisi POS, bukan admin
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Ini akan menampilkan detail transaksi di halaman view Transaction
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

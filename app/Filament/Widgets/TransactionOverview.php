<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TransactionOverview extends BaseWidget
{
    // Ini yang akan membuat widget terupdate secara otomatis
    // '5s' berarti update setiap 5 detik. Anda bisa mengubahnya menjadi '10s' (10 detik), '1m' (1 menit), dll.
    protected static ?string $pollingInterval = '5s';

    // Properti $heading kita biarkan non-static, sesuai dengan error terakhir yang menunjukkan itu non-static di base class Anda.
    protected ?string $heading = 'Ringkasan Transaksi';

    protected function getStats(): array
    {
        // Mendapatkan data transaksi dari database
        $totalTransactions = Transaction::count();
        $pendingTransactions = Transaction::where('payment_status', 'pending')->count();
        $successfulTransactions = Transaction::where('payment_status', 'success')->count();
        $failedOrExpiredTransactions = Transaction::whereIn('payment_status', ['failed', 'expire', 'cancelled'])->count();

        // Menghitung total pendapatan (hanya dari transaksi sukses)
        $totalRevenue = Transaction::where('payment_status', 'success')->sum('total_amount');

        return [
            Stat::make('Total Transaksi', Number::format($totalTransactions))
                ->description('Jumlah semua transaksi')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('info'),

            Stat::make('Transaksi Pending', Number::format($pendingTransactions))
                ->description('Menunggu pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Transaksi Berhasil', Number::format($successfulTransactions))
                ->description('Pembayaran sukses')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Transaksi Gagal/Kadaluarsa', Number::format($failedOrExpiredTransactions))
                ->description('Pembayaran tidak selesai')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Total Pendapatan', 'Rp ' . Number::format($totalRevenue))
                ->description('Dari transaksi sukses')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}

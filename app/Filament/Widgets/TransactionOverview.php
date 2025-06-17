<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class TransactionOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';
    protected ?string $heading = 'Ringkasan Transaksi';

    protected function getStats(): array
    {
        // Karena global scope sudah diterapkan di model Transaction,
        // query di bawah ini akan secara otomatis memfilter berdasarkan cabang user yang login
        // kecuali user tersebut adalah admin (isAdmin() = true).

        $totalTransactions = Transaction::count();
        $pendingTransactions = Transaction::where('payment_status', 'pending')->count();
        $successfulTransactions = Transaction::where('payment_status', 'success')->count();
        $failedOrExpiredTransactions = Transaction::whereIn('payment_status', ['failed', 'expire', 'cancelled'])->count();

        $totalRevenue = Transaction::where('payment_status', 'success')->sum('total_amount');

        // Optional: Tambahkan informasi cabang di heading widget jika bukan admin pusat
        if (Auth::check() && Auth::user()->email !== 'admin@admin.com' && Auth::user()->branch) {
            $this->heading = 'Ringkasan Transaksi Cabang ' . Auth::user()->branch->name;
        } else if (Auth::check() && Auth::user()->email === 'admin@admin.com') {
            $this->heading = 'Ringkasan Transaksi Semua Cabang (Admin)';
        } else {
            $this->heading = 'Ringkasan Transaksi (Guest)'; // Fallback jika tidak ada user
        }


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

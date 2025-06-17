<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\CoreApi;
use Exception;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'required|exists:services,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
            // branch_id tidak perlu lagi dari frontend, ambil dari user
            // 'branch_id' => 'required|exists:branches,id',
        ]);

        $user = Auth::user();

        // Pastikan user terhubung ke cabang (jika bukan admin)
        if (!$user->branch && $user->email !== 'admin@admin.com') {
            return response()->json(['message' => 'Akun Anda tidak terhubung ke cabang. Tidak dapat membuat transaksi.'], 403);
        }

        $branchId = $user->branch ? $user->branch->id : null; // ID cabang user
        $branchCode = $user->branch ? $user->branch->code : 'ADM'; // Kode cabang user, atau 'ADM' untuk admin

        try {
            $transaction_id = Str::uuid()->toString();
            // Buat invoice number dengan prefix kode cabang user yang login
            $invoice_number = strtoupper($branchCode) . '-' . time() . '-' . Str::random(5);
            $totalAmount = 0;
            $transactionDetails = [];

            foreach ($request->service_ids as $key => $serviceId) {
                $service = Service::find($serviceId);
                if (!$service) {
                    throw new Exception("Layanan dengan ID {$serviceId} tidak ditemukan.");
                }
                $quantity = $request->quantities[$key];
                $subtotal = $service->price * $quantity;
                $totalAmount += $subtotal;

                $transactionDetails[] = [
                    'service_id' => $service->id,
                    'quantity' => $quantity,
                    'price' => $service->price,
                    'subtotal' => $subtotal,
                ];
            }

            $transaction = Transaction::create([
                'branch_id' => $branchId, // Simpan branch_id dari user yang login
                'invoice_number' => $invoice_number,
                'total_amount' => $totalAmount,
                'payment_method' => 'QRIS',
                'payment_status' => 'pending',
                'midtrans_order_id' => $transaction_id,
            ]);

            foreach ($transactionDetails as $detail) {
                $transaction->transactionDetails()->create($detail);
            }

            $midtrans_params = [
                'transaction_details' => [
                    'order_id' => $transaction->midtrans_order_id,
                    'gross_amount' => $totalAmount,
                ],
                'item_details' => array_map(function ($detail) {
                    $service = Service::find($detail['service_id']);
                    return [
                        'id' => (string) $service->id,
                        'price' => (int) $service->price,
                        'quantity' => (int) $detail['quantity'],
                        'name' => (string) $service->name,
                    ];
                }, $transactionDetails),
                'customer_details' => [
                    'first_name' => 'Pelanggan',
                    'last_name' => $user->name, // Bisa pakai nama user
                    'email' => $user->email, // Bisa pakai email user
                    'phone' => '081234567890', // Bisa disesuaikan nanti
                ],
                'callbacks' => [
                    'finish' => url('/payment/success/' . $transaction->invoice_number),
                    'error' => url('/payment/failed/' . $transaction->invoice_number),
                    'pending' => url('/payment/pending/' . $transaction->invoice_number),
                ],
                'payment_type' => 'qris',
                'qris' => [
                    'acquirer' => 'gopay',
                ]
            ];

            $midtrans_charge_response = CoreApi::charge($midtrans_params);

            if ($midtrans_charge_response && $midtrans_charge_response->status_code == '201') {
                $transaction->midtrans_transaction_id = $midtrans_charge_response->transaction_id;
                $transaction->payment_status = $midtrans_charge_response->transaction_status;
                $transaction->midtrans_qr_code_url = $midtrans_charge_response->actions[0]->url;
                $transaction->save();

                return response()->json([
                    'message' => 'Transaksi berhasil dibuat, menunggu pembayaran QRIS.',
                    'invoice_number' => $invoice_number,
                    'total_amount' => $totalAmount,
                    'qr_code_url' => $midtrans_charge_response->actions[0]->url,
                    'transaction_status' => $midtrans_charge_response->transaction_status,
                ], 201);
            } else {
                Log::error('Midtrans QRIS Charge Failed: ' . json_encode($midtrans_charge_response));
                $transaction->payment_status = 'failed';
                $transaction->save();
                return response()->json(['message' => 'Gagal membuat transaksi QRIS dengan Midtrans.', 'details' => $midtrans_charge_response], 500);
            }
        } catch (Exception $e) {
            Log::error('Error creating transaction: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat membuat transaksi.', 'error' => $e->getMessage()], 500);
        }
    }

    // ... (metode paymentSuccess, paymentFailed, paymentPending, getTransactionStatus)
}

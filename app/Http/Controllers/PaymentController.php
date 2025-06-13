<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Service;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap; // Jika pakai Snap, tapi kita akan pakai Core API untuk QRIS
use Midtrans\CoreApi; // Untuk direct API call seperti QRIS
use Exception;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
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
        ]);

        try {
            $transaction_id = Str::uuid()->toString(); // Unique ID for our transaction
            $invoice_number = 'INV-' . time() . '-' . Str::random(5);
            $totalAmount = 0;
            $transactionDetails = [];

            // Get services and calculate total amount
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

            // Simpan transaksi awal ke database
            $transaction = Transaction::create([
                'invoice_number' => $invoice_number,
                'total_amount' => $totalAmount,
                'payment_method' => 'QRIS',
                'payment_status' => 'pending',
                'midtrans_order_id' => $transaction_id, // Gunakan ID transaksi kita sebagai order_id
            ]);

            // Simpan detail transaksi
            foreach ($transactionDetails as $detail) {
                $transaction->transactionDetails()->create($detail);
            }

            // Buat parameter transaksi untuk Midtrans
            $midtrans_params = [
                'transaction_details' => [
                    'order_id' => $transaction->midtrans_order_id, // Ini harus unik per transaksi
                    'gross_amount' => $totalAmount,
                ],
                'item_details' => array_map(function ($detail) {
                    $service = Service::find($detail['service_id']);
                    return [
                        'id' => $service->id,
                        'price' => (int) $service->price,
                        'quantity' => (int) $detail['quantity'],
                        'name' => $service->name,
                    ];
                }, $transactionDetails),
                'customer_details' => [
                    // Ini bisa diisi dengan data pelanggan barbershop jika ada
                    'first_name' => 'Pelanggan',
                    'last_name' => 'Barbershop',
                    'email' => 'customer@example.com',
                    'phone' => '081234567890',
                ],
                'callbacks' => [
                    'finish' => url('/payment/success/' . $transaction->invoice_number), // URL saat pembayaran selesai
                    'error' => url('/payment/failed/' . $transaction->invoice_number),   // URL saat pembayaran gagal
                    'pending' => url('/payment/pending/' . $transaction->invoice_number), // URL saat pembayaran pending
                ],
                // Khusus untuk QRIS (dengan Core API)
                'payment_type' => 'qris',
                'qris' => [
                    'acquirer' => 'gopay', // Bisa 'gopay' atau 'shopeepay'. 'gopay' biasanya default untuk QRIS
                    'expiration_time' => 24, // Waktu kadaluarsa dalam jam
                    'reusable' => false, // QRIS tidak bisa digunakan ulang
                ]
            ];

            // dd($midtrans_params);

            // Panggil API Midtrans untuk mendapatkan QRIS
            $midtrans_charge_response = CoreApi::charge($midtrans_params);

            if ($midtrans_charge_response && $midtrans_charge_response->status_code == '201') { // 201 Created for pending transaction
                $transaction->midtrans_transaction_id = $midtrans_charge_response->transaction_id;
                $transaction->payment_status = $midtrans_charge_response->transaction_status;

                // Validasi dan bersihkan URL QR code
                $qrCodeUrl = $midtrans_charge_response->actions[0]->url ?? null;
                if ($qrCodeUrl && filter_var($qrCodeUrl, FILTER_VALIDATE_URL)) {
                    $transaction->midtrans_qr_code_url = $qrCodeUrl;
                } else {
                    Log::warning('Invalid QR code URL received from Midtrans: ' . json_encode($midtrans_charge_response->actions));
                    $transaction->midtrans_qr_code_url = null;
                }

                $transaction->save();

                return response()->json([
                    'message' => 'Transaksi berhasil dibuat, menunggu pembayaran QRIS.',
                    'invoice_number' => $invoice_number,
                    'total_amount' => $totalAmount,
                    'qr_code_url' => $transaction->midtrans_qr_code_url,
                    'transaction_status' => $midtrans_charge_response->transaction_status,
                ], 201);
            } else {
                // Log error lebih detail jika perlu
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

    /**
     * Mengambil status transaksi berdasarkan invoice number untuk polling.
     */
    public function getTransactionStatus($invoice_number)
    {
        $transaction = Transaction::where('invoice_number', $invoice_number)->first();

        if (!$transaction) {
            return response()->json(['status' => 'not_found', 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => $transaction->payment_status,
            'invoice_number' => $transaction->invoice_number,
            'payment_status' => $transaction->payment_status,
            'total_amount' => $transaction->total_amount,
            'qr_code_url' => $transaction->midtrans_qr_code_url, // Mungkin masih perlu ditampilkan jika status pending
        ]);
    }

    // Metode untuk callback setelah pembayaran selesai (akan dijelaskan lebih lanjut di bagian Webhook)
    public function paymentSuccess($invoice_number)
    {
        $transaction = Transaction::where('invoice_number', $invoice_number)->first();
        if ($transaction) {
            return view('payment_status', ['status' => 'success', 'transaction' => $transaction]);
        }
        return redirect('/')->with('error', 'Transaksi tidak ditemukan.');
    }

    public function paymentFailed($invoice_number)
    {
        $transaction = Transaction::where('invoice_number', $invoice_number)->first();
        if ($transaction) {
            return view('payment_status', ['status' => 'failed', 'transaction' => $transaction]);
        }
        return redirect('/')->with('error', 'Transaksi tidak ditemukan.');
    }

    public function paymentPending($invoice_number)
    {
        $transaction = Transaction::where('invoice_number', $invoice_number)->first();
        if ($transaction) {
            return view('payment_status', ['status' => 'pending', 'transaction' => $transaction]);
        }
        return redirect('/')->with('error', 'Transaksi tidak ditemukan.');
    }
}

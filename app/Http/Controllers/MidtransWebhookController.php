<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Exception;

class MidtransWebhookController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function handler(Request $request)
    {
        try {
            $notif = new Notification(); // Ini akan otomatis memvalidasi notifikasi dari Midtrans

            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;
            $orderId = $notif->order_id; // Ini adalah midtrans_order_id yang kita kirim sebelumnya

            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$transaction) {
                // Log error jika transaksi tidak ditemukan
                Log::warning('Midtrans Notification: Transaction not found for order ID: ' . $orderId);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $transaction->payment_status = 'challenge';
                } else if ($fraudStatus == 'accept') {
                    $transaction->payment_status = 'success';
                }
            } else if ($transactionStatus == 'settlement') {
                $transaction->payment_status = 'success';
            } else if ($transactionStatus == 'pending') {
                $transaction->payment_status = 'pending';
            } else if ($transactionStatus == 'deny') {
                $transaction->payment_status = 'failed';
            } else if ($transactionStatus == 'expire') {
                $transaction->payment_status = 'expire';
            } else if ($transactionStatus == 'cancel') {
                $transaction->payment_status = 'cancelled';
            } else if ($transactionStatus == 'refund' || $transactionStatus == 'partial_refund') {
                $transaction->payment_status = 'refunded';
            }

            $transaction->save();

            Log::info('Midtrans Notification Processed for Order ID: ' . $orderId . ' Status: ' . $transaction->payment_status);

            return response()->json(['message' => 'OK'], 200);
        } catch (Exception $e) {
            // Log error jika ada masalah dalam memproses notifikasi
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing webhook: ' . $e->getMessage()], 500);
        }
    }
}

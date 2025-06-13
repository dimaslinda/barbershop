<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        @if ($status == 'success')
            <h1 class="text-3xl font-bold text-green-600 mb-4">Pembayaran Berhasil!</h1>
            <p class="text-gray-700 mb-2">Invoice: {{ $transaction->invoice_number }}</p>
            <p class="text-gray-700 mb-4">Total: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500">Terima kasih telah bertransaksi.</p>
        @elseif($status == 'pending')
            <h1 class="text-3xl font-bold text-yellow-600 mb-4">Pembayaran Menunggu!</h1>
            <p class="text-gray-700 mb-2">Invoice: {{ $transaction->invoice_number }}</p>
            <p class="text-gray-700 mb-4">Total: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500">Silakan selesaikan pembayaran QRIS Anda.</p>
            @if ($transaction->midtrans_qr_code_url)
                <img src="{{ $transaction->midtrans_qr_code_url }}" alt="QRIS Code"
                    class="mx-auto w-48 h-48 border border-gray-300 rounded-md mt-4">
                <p class="text-sm text-gray-600 mt-2">Scan QRIS ini untuk pembayaran.</p>
            @endif
        @else
            <h1 class="text-3xl font-bold text-red-600 mb-4">Pembayaran Gagal/Kadaluarsa!</h1>
            <p class="text-gray-700 mb-2">Invoice: {{ $transaction->invoice_number }}</p>
            <p class="text-gray-700 mb-4">Total: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500">Mohon coba lagi atau hubungi admin.</p>
        @endif

        <a href="/"
            class="mt-6 inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Kembali ke POS</a>
    </div>
</body>

</html>

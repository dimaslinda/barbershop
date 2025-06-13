<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbershop POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Gaya tambahan untuk feedback tombol */
        .add-to-cart-btn {
            transition: background-color 0.2s ease-in-out, opacity 0.2s ease-in-out;
        }

        /* Gaya untuk tombol yang sudah ada di keranjang (tidak bisa ditambahkan lagi) */
        .add-to-cart-btn.added-to-cart {
            background-color: #d1d5db;
            /* Warna abu-abu */
            color: #4b5563;
            /* Warna teks gelap */
            cursor: not-allowed;
            opacity: 0.7;
        }

        .add-to-cart-btn.added-to-cart:hover {
            background-color: #d1d5db;
            /* Pastikan hover juga abu-abu */
        }
    </style>
</head>

<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Barbershop POS</h1>

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Layanan Tersedia</h2>
        <div id="services-list" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p>Loading services...</p>
        </div>

        <hr class="my-6">

        <h2 class="text-2xl font-semibold mb-4">Keranjang Belanja</h2>
        <div id="cart-items" class="mb-4">
            <p>Keranjang kosong.</p>
        </div>

        <div class="text-right text-xl font-bold mb-4">
            Total: <span id="cart-total">Rp 0</span>
        </div>

        <button id="pay-button"
            class="w-full bg-blue-500 text-white py-3 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            Bayar Sekarang (QRIS)
        </button>

        <div id="payment-result"
            class="mt-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-md hidden">
            <p id="result-message" class="font-semibold"></p>
            <p id="result-invoice"></p>
            <p id="result-total"></p>
            <p id="result-status"></p>
            <div id="qr-code-display" class="mt-4 text-center">
                <img id="qr-image" src="" alt="QRIS Code"
                    class="mx-auto w-48 h-48 border border-gray-300 rounded-md">
                <p class="text-sm text-gray-600 mt-2">Scan QRIS ini untuk pembayaran.</p>
            </div>
            <p class="text-sm text-gray-500 mt-2">Status pembayaran akan diperbarui secara otomatis.</p>
            <a href="/" class="block mt-4 text-center text-blue-600 hover:underline">Buat Transaksi Baru</a>
        </div>
    </div>

    <script>
        let services = [];
        let cart = {}; // {service_id: quantity} - meskipun quantity akan selalu 1, ini tetap struktur yang konsisten
        let paymentPollingInterval;
        let currentInvoiceNumber = '';

        document.addEventListener('DOMContentLoaded', async () => {
            await fetchServices();
            updateCartDisplay();
        });

        async function fetchServices() {
            try {
                const response = await fetch('/api/services');
                const data = await response.json();
                services = data;
                renderServices();
            } catch (error) {
                console.error('Error fetching services:', error);
                document.getElementById('services-list').innerHTML =
                    '<p class="text-red-500">Gagal memuat layanan. Pastikan API services berfungsi.</p>';
            }
        }

        function renderServices() {
            const servicesList = document.getElementById('services-list');
            servicesList.innerHTML = '';
            if (services.length === 0) {
                servicesList.innerHTML =
                    '<p>Belum ada layanan yang ditambahkan. Silakan tambahkan melalui panel admin.</p>';
                return;
            }

            services.forEach(service => {
                const serviceCard = document.createElement('div');
                serviceCard.className =
                    'bg-white p-4 border border-gray-200 rounded-md shadow-sm flex justify-between items-center';
                serviceCard.innerHTML = `
                    <div>
                        <h3 class="text-lg font-semibold">${service.name}</h3>
                        <p class="text-gray-600">Rp ${parseFloat(service.price).toLocaleString('id-ID')}</p>
                    </div>
                    <button class="add-to-cart-btn bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600" data-id="${service.id}" data-name="${service.name}" data-price="${service.price}">
                        Tambahkan
                    </button>
                `;
                servicesList.appendChild(serviceCard);
            });

            attachServiceButtonListeners();
            updateServiceButtonStates(); // Memastikan status tombol diperbarui saat rendering awal
        }

        function attachServiceButtonListeners() {
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.removeEventListener('click', handleAddServiceToCart);
                button.addEventListener('click', handleAddServiceToCart);
            });
        }

        function handleAddServiceToCart(e) {
            const id = e.target.dataset.id;
            const button = e.target;

            // --- PERUBAHAN UTAMA DI SINI ---
            if (cart[id]) {
                // Jika layanan sudah ada, JANGAN LAKUKAN APA-APA (kuantitas tetap 1)
                // Beri feedback ke user
                alert('Layanan ini sudah ada di keranjang.');
                return; // Hentikan fungsi
            } else {
                // Jika layanan belum ada, tambahkan dengan kuantitas 1
                cart[id] = 1;
            }
            // --- AKHIR PERUBAHAN UTAMA ---

            updateCartDisplay();
            updateServiceButtonStates(); // Perbarui status tombol layanan
        }

        function updateCartDisplay() {
            const cartItemsDiv = document.getElementById('cart-items');
            const cartTotalSpan = document.getElementById('cart-total');
            let total = 0;
            cartItemsDiv.innerHTML = '';

            if (Object.keys(cart).length === 0) {
                cartItemsDiv.innerHTML = '<p>Keranjang kosong.</p>';
                cartTotalSpan.textContent = 'Rp 0';
                updateServiceButtonStates(); // Jika keranjang kosong, update status semua tombol
                return;
            }

            for (const serviceId in cart) {
                // Karena kuantitas selalu 1, kita bisa tampilkan langsung
                const quantity = 1; // Selalu 1
                const service = services.find(s => s.id == serviceId);
                if (service) {
                    const itemTotal = service.price * quantity; // Harga * 1
                    total += itemTotal;

                    const cartItemDiv = document.createElement('div');
                    cartItemDiv.className = 'flex justify-between items-center bg-gray-50 p-2 rounded-md mb-2';
                    cartItemDiv.innerHTML = `
                        <span>${service.name}</span> <div>
                            <span>Rp ${parseFloat(itemTotal).toLocaleString('id-ID')}</span>
                            <button class="remove-from-cart-btn text-red-500 hover:text-red-700 ml-4" data-id="${service.id}">Hapus</button>
                        </div>
                    `;
                    cartItemsDiv.appendChild(cartItemDiv);
                }
            }
            cartTotalSpan.textContent = `Rp ${parseFloat(total).toLocaleString('id-ID')}`;

            document.querySelectorAll('.remove-from-cart-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const id = e.target.dataset.id;
                    if (cart[id]) {
                        delete cart[id];
                    }
                    updateCartDisplay();
                    updateServiceButtonStates(); // Perbarui status tombol layanan
                });
            });
        }

        // --- Fungsi updateServiceButtonStates untuk skenario 1 kuantitas per layanan ---
        function updateServiceButtonStates() {
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                const serviceId = button.dataset.id;

                // Reset semua gaya default
                button.textContent = 'Tambahkan';
                button.classList.remove('added-to-cart');
                button.classList.remove('bg-yellow-500', 'hover:bg-yellow-600', 'text-black'); // Hapus gaya lama
                button.classList.add('bg-green-500', 'hover:bg-green-600', 'text-white'); // Kembali ke hijau

                if (cart[serviceId]) {
                    // Jika item sudah ada di keranjang, nonaktifkan tombol dan ubah tampilannya
                    button.textContent = 'Ditambahkan';
                    button.classList.add('added-to-cart');
                    button.disabled = true; // Nonaktifkan tombol
                } else {
                    button.disabled = false; // Aktifkan tombol jika tidak ada di keranjang
                }
            });
        }
        // --- Akhir Fungsi updateServiceButtonStates ---


        async function checkPaymentStatus() {
            if (!currentInvoiceNumber) return;

            try {
                const response = await fetch(`/api/transaction-status/${currentInvoiceNumber}`);
                const data = await response.json();

                if (data.status === 'success') {
                    document.getElementById('result-status').textContent = `Status: ${data.payment_status}`;
                    const paymentResultDiv = document.getElementById('payment-result');
                    paymentResultDiv.classList.remove('bg-yellow-100', 'border-yellow-400', 'text-yellow-700',
                        'bg-red-100', 'border-red-400', 'text-red-700');
                    paymentResultDiv.classList.add('bg-green-100', 'border-green-400', 'text-green-700');
                    document.getElementById('qr-code-display').style.display = 'none';
                    clearInterval(paymentPollingInterval);
                    alert('Pembayaran Berhasil!');
                } else if (data.status === 'expire' || data.status === 'failed' || data.status === 'cancelled') {
                    document.getElementById('result-status').textContent = `Status: ${data.payment_status}`;
                    const paymentResultDiv = document.getElementById('payment-result');
                    paymentResultDiv.classList.remove('bg-yellow-100', 'border-yellow-400', 'text-yellow-700',
                        'bg-green-100', 'border-green-400', 'text-green-700');
                    paymentResultDiv.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                    document.getElementById('qr-code-display').style.display = 'none';
                    clearInterval(paymentPollingInterval);
                    alert('Pembayaran Gagal atau Kadaluarsa!');
                } else {
                    document.getElementById('result-status').textContent = `Status: ${data.payment_status}`;
                }
            } catch (error) {
                console.error('Error polling payment status:', error);
            }
        }

        document.getElementById('pay-button').addEventListener('click', async () => {
            const serviceIds = [];
            const quantities = [];

            for (const serviceId in cart) {
                serviceIds.push(parseInt(serviceId));
                quantities.push(cart[serviceId]); // Kuantitas akan selalu 1 di sini
            }

            if (serviceIds.length === 0) {
                alert('Keranjang belanja kosong. Silakan tambahkan layanan terlebih dahulu.');
                return;
            }

            if (paymentPollingInterval) {
                clearInterval(paymentPollingInterval);
            }

            document.getElementById('pay-button').disabled = true;
            document.getElementById('pay-button').textContent = 'Memproses Pembayaran...';

            try {
                const response = await fetch('/api/create-qris-transaction', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        service_ids: serviceIds,
                        quantities: quantities
                    })
                });

                const result = await response.json();
                const paymentResultDiv = document.getElementById('payment-result');

                if (response.ok) {
                    currentInvoiceNumber = result.invoice_number;
                    document.getElementById('result-message').textContent = result.message;
                    document.getElementById('result-invoice').textContent =
                        `Nomor Invoice: ${result.invoice_number}`;
                    document.getElementById('result-total').textContent =
                        `Jumlah Total: Rp ${parseFloat(result.total_amount).toLocaleString('id-ID')}`;
                    document.getElementById('result-status').textContent =
                        `Status: ${result.transaction_status}`;
                    document.getElementById('qr-image').src = result.qr_code_url;
                    document.getElementById('qr-code-display').style.display = 'block';
                    paymentResultDiv.classList.remove('hidden', 'bg-yellow-100', 'border-yellow-400',
                        'text-yellow-700');
                    paymentResultDiv.classList.add('bg-green-100', 'border-green-400', 'text-green-700');
                    cart = {};
                    updateCartDisplay();

                    paymentPollingInterval = setInterval(checkPaymentStatus, 5000);

                } else {
                    document.getElementById('result-message').textContent =
                        `Gagal membuat transaksi: ${result.message || 'Terjadi kesalahan'}`;
                    document.getElementById('qr-code-display').style.display = 'none';
                    paymentResultDiv.classList.remove('hidden', 'bg-green-100', 'border-green-400',
                        'text-green-700');
                    paymentResultDiv.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                }
                paymentResultDiv.classList.remove('hidden');

            } catch (error) {
                console.error('Error:', error);
                const paymentResultDiv = document.getElementById('payment-result');
                document.getElementById('result-message').textContent =
                    `Terjadi kesalahan jaringan: ${error.message}`;
                document.getElementById('qr-code-display').style.display = 'none';
                paymentResultDiv.classList.remove('hidden', 'bg-green-100', 'border-green-400',
                    'text-green-700');
                paymentResultDiv.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
            } finally {
                document.getElementById('pay-button').disabled = false;
                document.getElementById('pay-button').textContent = 'Bayar Sekarang (QRIS)';
            }
        });
    </script>
</body>

</html>

# Aplikasi Barbershop

Aplikasi manajemen barbershop dengan fitur pembayaran QRIS menggunakan Midtrans.

## Fitur Utama

-   🪒 Manajemen Layanan Barbershop
-   💳 Pembayaran QRIS (GoPay, ShopeePay, dll)
-   📱 Dashboard Admin dengan Filament
-   📊 Laporan Transaksi
-   🔔 Notifikasi Status Pembayaran

## Teknologi yang Digunakan

-   Laravel 10.x
-   Filament Admin Panel
-   Midtrans Payment Gateway
-   MySQL Database
-   TailwindCSS

## Persyaratan Sistem

-   PHP >= 8.1
-   Composer
-   MySQL >= 8.0
-   Node.js & NPM
-   Midtrans Account

## Instalasi

1. Clone repository

```bash
git clone https://github.com/username/barbershop.git
cd barbershop
```

2. Install dependencies

```bash
composer install
npm install
```

3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

4. Konfigurasi database di `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barbershop
DB_USERNAME=root
DB_PASSWORD=
```

5. Konfigurasi Midtrans di `.env`

```env
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
```

6. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

7. Build assets

```bash
npm run build
```

8. Jalankan server

```bash
php artisan serve
```

## Penggunaan

### Admin Panel

-   Akses admin panel di `/admin`
-   Login dengan kredensial default:
    -   Email: admin@barbershop.com
    -   Password: password

### Pembayaran QRIS

1. Pilih layanan yang diinginkan
2. Masukkan jumlah layanan
3. Scan QR code yang muncul
4. Lakukan pembayaran melalui aplikasi e-wallet
5. Status pembayaran akan diperbarui secara otomatis

## API Endpoints

### Transaksi

-   `POST /api/transactions` - Buat transaksi baru
-   `GET /api/transactions/{invoice_number}` - Cek status transaksi

### Webhook Midtrans

-   `POST /api/midtrans/webhook` - Endpoint untuk notifikasi status pembayaran

## Pengembangan

### Menjalankan Tests

```bash
php artisan test
```

### Menjalankan Linter

```bash
composer run lint
```

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

## Lisensi

Dilisensikan di bawah [MIT License](LICENSE.md).

## Kontak

DimasLinda - [@dimaslinda](https://github.com/dimaslinda)

Project Link: [https://github.com/dimaslinda/barbershop](https://github.com/dimaslinda/barbershop)

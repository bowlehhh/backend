# Surya Duta Multindo Backend

Project ini memakai Laravel 13, Filament, DomPDF, dan Vite.

## Kebutuhan Server

- PHP `8.3+`
- Composer `2.x`
- MySQL / MariaDB
- Node.js `20+` dan npm jika asset akan dibuild di server
- Ekstensi PHP umum Laravel, plus yang dibutuhkan DomPDF dan PhpSpreadsheet

## Sebelum Push

- Jangan commit `.env`
- Jangan commit `vendor`, `node_modules`, `public/build`, atau `public/storage`
- Pastikan migrasi baru ikut ter-push kalau ada perubahan database
- Kalau ada upload gambar, server harus punya symlink `public/storage -> storage/app/public`

## Setup Pertama di Server

1. Clone repository.
2. Copy `.env.example` ke `.env`.
3. Isi konfigurasi production:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://domain-anda`
   - `DB_*`
   - `FILESYSTEM_DISK=public`
4. Jalankan:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci
npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Update Aman Setelah `git pull`

Pakai script deploy ini dari root project:

```bash
bash scripts/after-pull-update.sh
```

Untuk checklist singkat versi server live, lihat:

```bash
DEPLOY_CHECKLIST.md
```

Kalau butuh flow setup / deploy umum, bisa juga pakai:

```bash
bash scripts/deploy-production.sh
```

Script ini akan:

- masuk maintenance mode
- install dependency PHP production
- build asset jika `npm` tersedia
- refresh cache Laravel
- buat ulang `storage` symlink
- jalankan migrasi
- cache config, route, dan view
- keluar dari maintenance mode otomatis

## Alternatif Tanpa Script

Kalau mau manual:

```bash
php artisan down --retry=60
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci
npm run build
php artisan optimize:clear
php artisan storage:link --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## Catatan Penting

- Upload gambar produk memakai disk `public`, jadi `FILESYSTEM_DISK` sebaiknya `public`
- Folder berikut harus writable oleh web server:
  - `storage`
  - `bootstrap/cache`
- Jika server tidak build asset sendiri, jangan lupa siapkan proses CI/CD atau build di mesin lain lalu deploy hasilnya
- Setelah update package besar Filament/Laravel, cek ulang halaman admin sebelum aplikasi dipakai penuh

## Cek Cepat Setelah Deploy

```bash
php artisan about
php artisan migrate:status
php artisan route:list
```

Lalu cek manual:

- login admin
- halaman daftar stok
- halaman transaksi
- print invoice / rekap
- upload gambar produk

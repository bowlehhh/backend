# Deploy Checklist

Project ini sudah diuji lokal dengan:

- `php artisan test`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `npm run build`

## 1. Requirement Server

- PHP `8.3+` dengan ekstensi yang dibutuhkan Laravel dan `phpoffice/phpspreadsheet`
- Composer `2+`
- Node.js dan npm untuk build asset, atau upload folder `public/build` dari hasil build lokal
- Database yang sama strukturnya dengan lokal

## 2. Environment Production

Jangan salin `.env` lokal mentah ke server. Buat `.env` production dengan penyesuaian ini:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
LOG_LEVEL=error
```

Sesuaikan juga bagian ini dengan server:

- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_DRIVER`
- `CACHE_STORE`
- `QUEUE_CONNECTION`
- `FILESYSTEM_DISK`
- `MAIL_*`

Catatan penting untuk project ini:

- `.env.example` masih memakai `sqlite` untuk default contoh.
- Project ini memakai `SESSION_DRIVER=database`, `CACHE_STORE=database`, dan `QUEUE_CONNECTION=database`, jadi migrasi wajib jalan dengan benar di server.

## 3. Command Deploy

Urutan aman yang direkomendasikan:

```bash
composer install --no-dev --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan up
```

Jika asset dibuild di server:

```bash
npm ci
npm run build
```

## 4. Queue dan Worker

Karena `QUEUE_CONNECTION=database`, pastikan worker aktif setelah deploy:

```bash
php artisan queue:work --tries=1 --timeout=0
```

Di production sebaiknya dijalankan lewat Supervisor, systemd, atau process manager setara.

## 5. Hal yang Perlu Dicek Setelah Deploy

- Halaman login bisa diakses
- Login admin besar berhasil
- API POS hanya bisa diakses role yang benar
- Tambah/edit barang dari dashboard admin berhasil
- Checkout mengurangi stok dengan benar
- PDF/receipt bisa dibuka
- Folder `storage` dan `bootstrap/cache` writable
- File upload gambar produk tersimpan dan tampil

## 6. Konsistensi Lokal dan Server

Agar perilaku lokal tetap sama saat deploy:

- pakai migration terbaru yang sama
- pakai build asset dari commit yang sama
- jangan ubah flow role di server tanpa ikut update `.env` dan database
- kalau deploy ke MySQL/MariaDB, lock invoice akan aktif otomatis
- kalau lokal/testing memakai SQLite, aplikasi tetap jalan tanpa lock DB khusus

# Checklist Revisi Server Online

Pakai ini saat aplikasi sudah online dan kita hanya mau update revisi terbaru dari Git.

## 1. Masuk ke folder project

```bash
cd /path/ke/project
```

## 2. Pastikan branch benar

```bash
git branch
git status
```

Kalau ada file berubah langsung di server, jangan `pull` dulu sebelum dipastikan aman.

## 3. Ambil revisi terbaru

```bash
git pull origin main
```

Ganti `main` kalau branch production memakai nama lain.

## 4. Jalankan update aman

```bash
bash scripts/after-pull-update.sh
```

Script ini akan:

- maintenance mode
- `composer install --no-dev`
- build asset jika `npm` tersedia
- clear cache lama
- pastikan `storage:link`
- migrate database
- cache ulang config, route, dan view
- aktifkan aplikasi lagi

## 5. Cek cepat setelah update

```bash
php artisan about
php artisan migrate:status
```

Lalu buka:

- login admin
- daftar stok
- transaksi
- print invoice / rekap
- upload gambar produk

## 6. Kalau asset frontend tidak berubah

Script tetap aman dijalankan. Kalau `npm` tidak ada di server, build asset harus dilakukan dari pipeline / mesin build lain.

## 7. Kalau update gagal di tengah jalan

Karena script memakai `trap`, aplikasi akan otomatis dicoba `up` lagi saat script berhenti.

Tetap cek manual:

```bash
php artisan up
php artisan optimize:clear
```

# Long-Term Maintenance Guide

Dokumen ini menjelaskan konteks inti project POS supaya tim baru bisa cepat memahami "kode ini buat apa", serta area mana yang sensitif saat perubahan.

## 1. Gambaran Modul

- `Cashier Dashboard`:
  - Menampilkan produk aktif, keranjang, ringkasan omzet harian, dan checkout.
  - File utama: `app/Http/Controllers/Cashier/CashierDashboardController.php`, `resources/views/cashier/dashboard.blade.php`.
- `Cashier Transaction`:
  - Menangani tambah/update/hapus item keranjang, tunda ke draft, muat draft, checkout, riwayat, dan cetak nota.
  - File utama: `app/Http/Controllers/Cashier/CashierTransactionController.php`.
- `Checkout Engine`:
  - Menjamin transaksi atomik (stok + sale + sale_items) dengan DB transaction.
  - File utama: `app/Services/CheckoutService.php`.
- `Receipt / Nota`:
  - Template cetak final untuk kasir.
  - File utama: `resources/views/cashier/receipt.blade.php`.

## 2. Alur Bisnis Kritis

1. Kasir pilih produk -> item masuk session cart (`cashier_cart`).
2. Kasir bisa ubah `qty` dan `harga` item (harga custom per transaksi diperbolehkan).
3. Kasir bisa tunda transaksi -> cart dipindah ke tabel `cashier_drafts`.
4. Checkout:
  - Validasi stok.
  - Simpan `sales` + `sale_items`.
  - Kurangi stok batch.
  - Simpan `stock_histories`.
5. Nota bisa auto-print jika checkout dikirim dengan `print_receipt=1`.

## 3. Data Penting yang Disimpan di Sales

- `customer_name`
- `cashier_service_name`
- `cashier_phone`
- `payment_method`, `paid_amount`, `change_amount`
- `total`

Catatan: field `cashier_service_name` adalah nama pelayan yang diinput saat transaksi (bisa beda dari nama akun login).

## 4. Reset Omzet Harian

- Ringkasan "Penjualan Hari Ini" tidak pakai jam 00:00 kaku.
- Menggunakan window reset berdasarkan env:
  - `CASHIER_DAILY_RESET_HOUR` (0-23)
  - `APP_TIMEZONE`
- Lokasi logic: `CashierDashboardController`.

## 5. Checklist Sebelum Deploy Perubahan Kasir

1. Jalankan migration baru.
2. Uji skenario:
  - add/update/remove cart
  - hold draft -> resume draft
  - checkout cash/non-cash
  - print invoice
3. Verifikasi stok berkurang sesuai qty.
4. Verifikasi nilai di nota sama dengan data input form transaksi.

## 6. Area Risiko Tinggi

- `CheckoutService`: perubahan kecil bisa memengaruhi akurasi stok/total.
- `receipt.blade.php`: perubahan layout bisa merusak hasil print.
- Session cart shape (`cashier_cart`): jika struktur item diubah, pastikan sinkron ke checkout + draft + modal konfirmasi.

## 7. Konvensi Dokumentasi Kode

- Untuk logic bisnis kompleks: tambahkan komentar "why", bukan hanya "what".
- Untuk public method controller/service: gunakan PHPDoc singkat.
- Untuk perubahan perilaku user-facing: update dokumen ini + `docs/ui-alignment.md` bila perlu.

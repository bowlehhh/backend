# UI Alignment Notes

## Admin

- Dashboard admin sekarang di-handle oleh Filament page `App\Filament\Pages\AdminDashboard`.
- Tampilan ini mengikuti arah desain `StockFlow Pro`:
  - kartu statistik besar
  - chart stok masuk 7 hari
  - section riwayat barang masuk terbaru
  - aksen emerald, surface terang, dan tipografi Hanken Grotesk
- Manajemen barang sekarang di-handle oleh `App\Filament\Resources\ProductResource`.

## Cashier

Desain kasir yang kamu kirim cocoknya masuk ke frontend terpisah, bukan ke Filament:

- POS register:
  - target file `pos-frontend/src/pages/POSPage.jsx`
  - komponen utama `ProductSearch`, `ProductGrid`, `CartPanel`, `CheckoutDialog`
- Riwayat transaksi kasir:
  - target file `pos-frontend/src/pages/TodayTransactionsPage.jsx`
  - data dari endpoint `/api/pos/transactions/today`
- Dashboard kasir:
  - target file `pos-frontend/src/pages/CashierDashboardPage.jsx`
  - data dari endpoint `/api/pos/dashboard`

## Kenapa dipisah

- Admin memang cocok di Filament karena fokus CRUD dan operasional backoffice.
- Kasir butuh pengalaman transaksi yang lebih cepat, custom, dan fokus keyboard/mouse, jadi lebih cocok di React POS terpisah sesuai arsitektur project.

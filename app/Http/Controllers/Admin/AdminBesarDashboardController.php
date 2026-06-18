<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Sale;
use App\Models\SaleDeleteLog;
use App\Models\SaleEditLog;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminBesarDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('admin.admin-besar.dashboard', [
            'user' => $request->user(),
            'stats' => $this->getStats(),
            'companyRecap' => $this->getCompanyRecap(),
            'recentTransactions' => $this->getRecentTransactions(),
            'activityFeed' => $this->getActivityFeed(),
        ]);
    }

    private function getStats(): array
    {
        return Cache::remember(
            'admin-besar:dashboard:stats:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (! Schema::hasTable('sales')) {
                    return [
                        'total_transactions' => 0,
                        'today_transactions' => 0,
                        'month_transactions' => 0,
                        'year_transactions' => 0,
                        'total_value' => 'Rp 0',
                        'admin_actions_today' => 0,
                    ];
                }

                $baseQuery = Sale::query()->whereNull('deleted_at');
                $todayQuery = (clone $baseQuery)->whereDate('created_at', today());
                $monthQuery = (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                $yearQuery = (clone $baseQuery)->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);

                $adminActionsToday = 0;
                if (Schema::hasTable('stock_histories')) {
                    $adminActionsToday += StockHistory::query()
                        ->whereDate('created_at', today())
                        ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                        ->count();
                }
                if (Schema::hasTable('admin_activity_logs')) {
                    $adminActionsToday += AdminActivityLog::query()
                        ->whereDate('created_at', today())
                        ->whereHas('actor', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                        ->count();
                }
                if (Schema::hasTable('sale_edit_logs')) {
                    $adminActionsToday += SaleEditLog::query()
                        ->whereDate('created_at', today())
                        ->whereHas('editor', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                        ->count();
                }
                if (Schema::hasTable('sale_delete_logs')) {
                    $adminActionsToday += SaleDeleteLog::query()
                        ->whereDate('created_at', today())
                        ->whereHas('deleter', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                        ->count();
                }

                return [
                    'total_transactions' => $baseQuery->count(),
                    'today_transactions' => $todayQuery->count(),
                    'month_transactions' => $monthQuery->count(),
                    'year_transactions' => $yearQuery->count(),
                    'total_value' => 'Rp ' . number_format((float) $baseQuery->sum('total'), 0, ',', '.'),
                    'admin_actions_today' => $adminActionsToday,
                ];
            }
        );
    }

    private function getRecentTransactions(): array
    {
        return Cache::remember(
            'admin-besar:dashboard:recent-transactions:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (! Schema::hasTable('sales')) {
                    return [];
                }

                return Sale::query()
                    ->with(['user:id,name,role'])
                    ->withCount('items')
                    ->withSum('items as subtotal_amount', 'subtotal')
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->limit(12)
                    ->get()
                    ->map(function (Sale $sale): array {
                        $createdAt = $sale->created_at ? Carbon::parse($sale->created_at) : null;

                        return [
                            'id' => (int) $sale->id,
                            'invoice' => $sale->invoice_number ?: '-',
                            'cashier' => $sale->user?->name ?: '-',
                            'customer' => trim((string) ($sale->customer_name ?? '')) !== ''
                                ? trim((string) $sale->customer_name)
                                : 'Pembeli Umum',
                            'items_count' => (int) ($sale->items_count ?? 0),
                            'subtotal' => 'Rp ' . number_format((float) ($sale->subtotal_amount ?? $sale->total ?? 0), 0, ',', '.'),
                            'total' => 'Rp ' . number_format((float) $sale->total, 0, ',', '.'),
                            'payment_method' => $this->labelPaymentMethod((string) $sale->payment_method),
                            'created_at' => $createdAt?->format('d M Y H:i') ?? '-',
                            'created_ts' => $createdAt?->timestamp ?? 0,
                            'receipt_url' => route('admin.admin-besar.receipt', ['sale' => $sale->id]),
                        ];
                    })
                    ->toArray();
            }
        );
    }

    private function getCompanyRecap(): array
    {
        return Cache::remember(
            'admin-besar:dashboard:company-recap:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (
                    ! Schema::hasTable('sales')
                    || ! Schema::hasTable('sale_items')
                    || ! Schema::hasColumn('sales', 'customer_name')
                ) {
                    return [
                        'summary' => [
                            'company_count' => 0,
                            'invoice_count' => 0,
                            'credit_count' => 0,
                            'lunas_count' => 0,
                            'grand_total_value' => 0.0,
                            'grand_total' => 'Rp 0',
                        ],
                        'groups' => [],
                    ];
                }

                $itemsAgg = DB::table('sale_items')
                    ->selectRaw('sale_id, COUNT(*) as items_count, COALESCE(SUM(qty), 0) as qty, COALESCE(SUM(subtotal), 0) as subtotal')
                    ->groupBy('sale_id');

                $rows = DB::table('sales')
                    ->leftJoinSub($itemsAgg, 'sale_items_agg', function ($join): void {
                        $join->on('sale_items_agg.sale_id', '=', 'sales.id');
                    })
                    ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($query) => $query->whereNull('sales.deleted_at'))
                    ->whereNotNull('sales.customer_name')
                    ->whereRaw("TRIM(sales.customer_name) <> ''")
                    ->selectRaw('
                        sales.id as sale_id,
                        sales.invoice_number,
                        sales.customer_name,
                        sales.payment_method,
                        sales.total,
                        sales.credit_amount,
                        sales.credit_due_date,
                        sales.created_at,
                        COALESCE(sale_items_agg.items_count, 0) as items_count,
                        COALESCE(sale_items_agg.qty, 0) as qty,
                        COALESCE(sale_items_agg.subtotal, 0) as subtotal
                    ')
                    ->orderByDesc('sales.id')
                    ->get();

                $grouped = [];
                foreach ($rows as $row) {
                    $companyName = $this->normalizeCompanyName((string) ($row->customer_name ?? ''));
                    $createdAt = $row->created_at ? Carbon::parse($row->created_at) : null;
                    $method = strtoupper((string) ($row->payment_method ?? 'CASH'));
                    $creditAmount = Schema::hasColumn('sales', 'credit_amount') ? (float) ($row->credit_amount ?? 0) : 0.0;
                    $dueDate = Schema::hasColumn('sales', 'credit_due_date') && ! empty($row->credit_due_date)
                        ? Carbon::parse($row->credit_due_date)
                        : null;
                    $status = $this->resolveSaleStatus($method, $creditAmount, $dueDate);

                    if (! isset($grouped[$companyName])) {
                        $grouped[$companyName] = [
                            'company_name' => $companyName,
                            'company_key' => $companyName,
                            'invoice_count' => 0,
                            'credit_count' => 0,
                            'lunas_count' => 0,
                            'grand_total_value' => 0.0,
                            'last_transaction_ts' => 0,
                            'last_transaction_at' => '-',
                            'invoices' => [],
                        ];
                    }

                    $subtotal = (float) ($row->subtotal ?? 0);
                    $grouped[$companyName]['invoice_count']++;
                    $grouped[$companyName]['grand_total_value'] += $subtotal;
                    if ($status === 'LUNAS') {
                        $grouped[$companyName]['lunas_count']++;
                    } else {
                        $grouped[$companyName]['credit_count']++;
                    }

                    $createdTs = $createdAt?->timestamp ?? 0;
                    if ($createdTs > $grouped[$companyName]['last_transaction_ts']) {
                        $grouped[$companyName]['last_transaction_ts'] = $createdTs;
                        $grouped[$companyName]['last_transaction_at'] = $createdAt?->format('d M Y H:i') ?? '-';
                    }

                    $grouped[$companyName]['invoices'][] = [
                        'sale_id' => (int) $row->sale_id,
                        'invoice_number' => (string) ($row->invoice_number ?: '-'),
                        'created_at' => $createdAt?->format('d M Y H:i') ?? '-',
                        'created_ts' => $createdTs,
                        'qty' => (int) ($row->qty ?? 0),
                        'items_count' => (int) ($row->items_count ?? 0),
                        'subtotal_value' => $subtotal,
                        'subtotal' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                        'total' => 'Rp ' . number_format((float) ($row->total ?? 0), 0, ',', '.'),
                        'payment_method' => $this->labelPaymentMethod($method),
                        'credit_amount' => 'Rp ' . number_format($creditAmount, 0, ',', '.'),
                        'credit_amount_value' => $creditAmount,
                        'credit_due_date' => $dueDate ? $dueDate->format('d M Y') : '-',
                        'status' => $status,
                        'receipt_url' => route('admin.admin-besar.receipt', ['sale' => $row->sale_id]),
                    ];
                }

                $groups = collect($grouped)
                    ->map(function (array $group): array {
                        usort($group['invoices'], static function (array $left, array $right): int {
                            return ($left['created_ts'] ?? 0) <=> ($right['created_ts'] ?? 0);
                        });

                        $group['grand_total'] = 'Rp ' . number_format((float) $group['grand_total_value'], 0, ',', '.');
                        unset($group['last_transaction_ts'], $group['company_key']);

                        return $group;
                    })
                    ->sortByDesc('grand_total_value')
                    ->values()
                    ->all();

                $summary = [
                    'company_count' => count($groups),
                    'invoice_count' => array_sum(array_map(static fn (array $group): int => (int) ($group['invoice_count'] ?? 0), $groups)),
                    'credit_count' => array_sum(array_map(static fn (array $group): int => (int) ($group['credit_count'] ?? 0), $groups)),
                    'lunas_count' => array_sum(array_map(static fn (array $group): int => (int) ($group['lunas_count'] ?? 0), $groups)),
                    'grand_total_value' => array_sum(array_map(static fn (array $group): float => (float) ($group['grand_total_value'] ?? 0), $groups)),
                ];
                $summary['grand_total'] = 'Rp ' . number_format((float) $summary['grand_total_value'], 0, ',', '.');

                return [
                    'summary' => $summary,
                    'groups' => $groups,
                ];
            }
        );
    }

    private function getActivityFeed(): array
    {
        $items = collect();

        if (Schema::hasTable('stock_histories')) {
            $items = $items->merge(
                StockHistory::query()
                    ->with(['user:id,name,role', 'product:id,name', 'productBatch:id,batch_code'])
                    ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                    ->latest('id')
                    ->limit(15)
                    ->get()
                    ->map(function (StockHistory $history): array {
                        $createdAt = $history->created_at ? Carbon::parse($history->created_at) : null;
                        $typeLabel = match ($history->type) {
                            StockHistory::TYPE_IN => 'Stok Masuk',
                            StockHistory::TYPE_OUT => 'Stok Keluar',
                            StockHistory::TYPE_ADJUST => 'Stok Disesuaikan',
                            default => strtoupper((string) $history->type),
                        };

                        return [
                            'kind' => 'stock',
                            'type' => $history->type,
                            'title' => $typeLabel,
                            'actor' => $history->user?->name ?: '-',
                            'detail' => ($history->product?->name ?: '-') . ' · ' . ($history->productBatch?->display_inventory_code ?: ($history->reference ?: '-')),
                            'note' => trim((string) ($history->description ?? '')) !== '' ? $history->description : '-',
                            'value' => 'Qty ' . number_format((int) $history->qty, 0, ',', '.') . ' | Stok ' . (int) $history->stock_before . ' -> ' . (int) $history->stock_after,
                            'created_at' => $createdAt?->format('d M Y H:i') ?? '-',
                            'created_ts' => $createdAt?->timestamp ?? 0,
                            'url' => null,
                            'details' => [
                                ['label' => 'Barang', 'value' => $history->product?->name ?: '-'],
                                ['label' => 'Referensi', 'value' => $history->productBatch?->display_inventory_code ?: ($history->reference ?: '-')],
                                ['label' => 'Tipe', 'value' => $typeLabel],
                                ['label' => 'Qty', 'value' => number_format((int) $history->qty, 0, ',', '.')],
                                ['label' => 'Stok Sebelum', 'value' => number_format((int) $history->stock_before, 0, ',', '.')],
                                ['label' => 'Stok Sesudah', 'value' => number_format((int) $history->stock_after, 0, ',', '.')],
                                ['label' => 'Catatan Sistem', 'value' => trim((string) ($history->description ?? '')) !== '' ? $history->description : '-'],
                            ],
                            'line_items' => [],
                        ];
                    })
            );
        }

        if (Schema::hasTable('admin_activity_logs')) {
            $items = $items->merge(
                AdminActivityLog::query()
                    ->with(['actor:id,name,role'])
                    ->whereHas('actor', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                    ->latest('id')
                    ->limit(15)
                    ->get()
                    ->map(function (AdminActivityLog $log): array {
                        $createdAt = $log->created_at ? Carbon::parse($log->created_at) : null;

                        return [
                            'kind' => 'activity',
                            'type' => $log->action,
                            'title' => $log->title,
                            'actor' => $log->actor?->name ?: '-',
                            'detail' => (string) ($log->meta['product_name'] ?? $log->meta['name'] ?? $log->subject_type ?? '-'),
                            'note' => trim((string) ($log->description ?? '')) !== '' ? $log->description : '-',
                            'value' => $this->formatMetaSummary($log->meta ?? []),
                            'created_at' => $createdAt?->format('d M Y H:i') ?? '-',
                            'created_ts' => $createdAt?->timestamp ?? 0,
                            'url' => null,
                            'details' => $this->buildAdminActivityDetails($log),
                            'line_items' => $this->buildAdminActivityItems($log),
                        ];
                    })
            );
        }

        if (Schema::hasTable('sale_edit_logs')) {
            $items = $items->merge(
                SaleEditLog::query()
                    ->with(['editor:id,name,role', 'sale:id,invoice_number,total,payment_method'])
                    ->whereHas('editor', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                    ->latest('id')
                    ->limit(15)
                    ->get()
                    ->map(function (SaleEditLog $log): array {
                        $createdAt = $log->created_at ? Carbon::parse($log->created_at) : null;

                        return [
                            'kind' => 'sale_edit',
                            'type' => 'edit',
                            'title' => 'Edit Transaksi',
                            'actor' => $log->editor?->name ?: '-',
                            'detail' => $log->invoice_number ?: ('Sale #' . $log->sale_id),
                            'note' => trim((string) ($log->edit_note ?? '')) !== '' ? $log->edit_note : 'Ada perubahan pada item, qty, atau harga.',
                            'value' => is_array($log->changed_fields) && $log->changed_fields !== []
                                ? implode(', ', $log->changed_fields)
                                : '-',
                            'created_at' => $createdAt?->format('d M Y H:i') ?? '-',
                            'created_ts' => $createdAt?->timestamp ?? 0,
                            'url' => $log->sale_id ? route('admin.admin-besar.receipt', ['sale' => $log->sale_id]) : null,
                            'details' => $this->buildSaleEditDetails($log),
                            'line_items' => $this->buildSaleSnapshotItems((array) ($log->new_data['items'] ?? [])),
                        ];
                    })
            );
        }

        if (Schema::hasTable('sale_delete_logs')) {
            $items = $items->merge(
                SaleDeleteLog::query()
                    ->with(['deleter:id,name,role', 'sale:id,invoice_number,total,payment_method'])
                    ->whereHas('deleter', fn ($query) => $query->where('role', User::ROLE_ADMIN))
                    ->latest('id')
                    ->limit(15)
                    ->get()
                    ->map(function (SaleDeleteLog $log): array {
                        $createdAt = $log->created_at ? Carbon::parse($log->created_at) : null;

                        return [
                            'kind' => 'sale_delete',
                            'type' => 'delete',
                            'title' => 'Hapus Transaksi',
                            'actor' => $log->deleter?->name ?: '-',
                            'detail' => $log->invoice_number ?: ('Sale #' . $log->sale_id),
                            'note' => trim((string) ($log->delete_note ?? '')) !== '' ? $log->delete_note : 'Transaksi dihapus dari sistem.',
                            'value' => 'Total ' . 'Rp ' . number_format((float) $log->total, 0, ',', '.'),
                            'created_at' => $createdAt?->format('d M Y H:i') ?? '-',
                            'created_ts' => $createdAt?->timestamp ?? 0,
                            'url' => $log->sale_id ? route('admin.admin-besar.receipt', ['sale' => $log->sale_id]) : null,
                            'details' => $this->buildSaleDeleteDetails($log),
                            'line_items' => $this->buildSaleSnapshotItems((array) ($log->snapshot['items'] ?? [])),
                        ];
                    })
            );
        }

        return $items
            ->sortByDesc('created_ts')
            ->take(30)
            ->values()
            ->all();
    }

    private function labelPaymentMethod(string $paymentMethod): string
    {
        return match (strtolower($paymentMethod)) {
            'cash' => 'Cash',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'debit' => 'Debit',
            'credit' => 'Kredit',
            default => strtoupper($paymentMethod) ?: '-',
        };
    }

    private function normalizeCompanyName(string $name): string
    {
        $normalized = strtoupper(trim($name));

        return preg_replace('/\s+/', ' ', $normalized) ?: 'TANPA PT/CV';
    }

    private function resolveSaleStatus(string $paymentMethod, float $creditAmount, ?Carbon $dueDate = null): string
    {
        if ($paymentMethod !== 'CREDIT') {
            return 'LUNAS';
        }

        if ($creditAmount <= 0) {
            return 'LUNAS';
        }

        if ($dueDate && $dueDate->isPast()) {
            return 'JATUH TEMPO';
        }

        return 'BELUM LUNAS';
    }

    private function formatMetaSummary(array $meta): string
    {
        if ($meta === []) {
            return '-';
        }

        $parts = [];

        if (array_key_exists('stock_before', $meta) || array_key_exists('stock_after', $meta)) {
            $parts[] = 'Stok ' . (string) ($meta['stock_before'] ?? '-') . ' -> ' . (string) ($meta['stock_after'] ?? '-');
        }

        if (array_key_exists('stock', $meta)) {
            $parts[] = 'Stok ' . (string) $meta['stock'];
        }

        if (array_key_exists('role', $meta)) {
            $parts[] = 'Role ' . (string) $meta['role'];
        }

        if (array_key_exists('batch_code', $meta)) {
            $parts[] = 'INV ' . (string) $meta['batch_code'];
        }

        return $parts !== [] ? implode(' | ', $parts) : '-';
    }

    private function buildAdminActivityDetails(AdminActivityLog $log): array
    {
        $meta = (array) ($log->meta ?? []);
        $details = [
            ['label' => 'Aksi', 'value' => $log->title ?: '-'],
            ['label' => 'Barang', 'value' => (string) ($meta['product_name'] ?? $meta['name'] ?? '-')],
            ['label' => 'INV', 'value' => (string) ($meta['batch_code'] ?? '-')],
            ['label' => 'Supplier', 'value' => (string) ($meta['supplier_name'] ?? '-')],
        ];

        if (array_key_exists('stock_before', $meta) || array_key_exists('stock_after', $meta)) {
            $details[] = ['label' => 'Stok', 'value' => number_format((int) ($meta['stock_before'] ?? 0), 0, ',', '.') . ' -> ' . number_format((int) ($meta['stock_after'] ?? 0), 0, ',', '.')];
        } elseif (array_key_exists('stock', $meta)) {
            $details[] = ['label' => 'Stok Awal', 'value' => number_format((int) ($meta['stock'] ?? 0), 0, ',', '.')];
        }

        if (array_key_exists('purchase_price', $meta)) {
            $details[] = ['label' => 'Harga Beli', 'value' => 'Rp ' . number_format((float) ($meta['purchase_price'] ?? 0), 0, ',', '.')];
        }

        if (array_key_exists('selling_price', $meta)) {
            $details[] = ['label' => 'Harga Jual', 'value' => 'Rp ' . number_format((float) ($meta['selling_price'] ?? 0), 0, ',', '.')];
        }

        $details[] = ['label' => 'Catatan', 'value' => trim((string) ($log->description ?? '')) !== '' ? $log->description : '-'];

        return $details;
    }

    private function buildAdminActivityItems(AdminActivityLog $log): array
    {
        $meta = (array) ($log->meta ?? []);
        $productName = trim((string) ($meta['product_name'] ?? $meta['name'] ?? ''));

        if ($productName === '') {
            return [];
        }

        $inventory = trim((string) ($meta['batch_code'] ?? ''));
        $supplier = trim((string) ($meta['supplier_name'] ?? ''));

        return [[
            'title' => $productName,
            'meta' => collect([
                $inventory !== '' ? 'INV ' . $inventory : null,
                $supplier !== '' ? 'Supplier ' . $supplier : null,
            ])->filter()->implode(' | '),
        ]];
    }

    private function buildSaleEditDetails(SaleEditLog $log): array
    {
        $details = [
            ['label' => 'Invoice', 'value' => $log->invoice_number ?: ('Sale #' . $log->sale_id)],
            ['label' => 'Perubahan', 'value' => is_array($log->changed_fields) && $log->changed_fields !== [] ? implode(', ', array_map(fn ($field) => $this->humanizeFieldName((string) $field), $log->changed_fields)) : '-'],
            ['label' => 'Catatan Edit', 'value' => trim((string) ($log->edit_note ?? '')) !== '' ? $log->edit_note : '-'],
        ];

        $oldTotal = (float) (($log->old_data['sale']['total'] ?? 0));
        $newTotal = (float) (($log->new_data['sale']['total'] ?? 0));
        $details[] = ['label' => 'Total', 'value' => 'Rp ' . number_format($oldTotal, 0, ',', '.') . ' -> Rp ' . number_format($newTotal, 0, ',', '.')];

        return $details;
    }

    private function buildSaleDeleteDetails(SaleDeleteLog $log): array
    {
        return [
            ['label' => 'Invoice', 'value' => $log->invoice_number ?: ('Sale #' . $log->sale_id)],
            ['label' => 'Metode', 'value' => $this->labelPaymentMethod((string) $log->payment_method)],
            ['label' => 'Total', 'value' => 'Rp ' . number_format((float) $log->total, 0, ',', '.')],
            ['label' => 'Jumlah Item', 'value' => number_format((int) $log->items_count, 0, ',', '.')],
            ['label' => 'Catatan Hapus', 'value' => trim((string) ($log->delete_note ?? '')) !== '' ? $log->delete_note : '-'],
        ];
    }

    private function buildSaleSnapshotItems(array $items): array
    {
        return collect($items)
            ->map(function ($item): ?array {
                $productName = trim((string) ($item['product_name'] ?? ''));
                if ($productName === '') {
                    return null;
                }

                $qty = (int) ($item['qty'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $subtotal = (float) ($item['subtotal'] ?? ($qty * $price));
                $partNumber = trim((string) ($item['part_number'] ?? ''));

                return [
                    'title' => $productName,
                    'meta' => collect([
                        $partNumber !== '' ? 'Part No ' . $partNumber : null,
                        'Qty ' . number_format($qty, 0, ',', '.'),
                        'Harga Rp ' . number_format($price, 0, ',', '.'),
                        'Subtotal Rp ' . number_format($subtotal, 0, ',', '.'),
                    ])->filter()->implode(' | '),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function humanizeFieldName(string $field): string
    {
        return match ($field) {
            'customer_name' => 'Nama Pembeli',
            'customer_phone' => 'No. Telepon Pembeli',
            'po_number' => 'P.O. No',
            'site_name' => 'Site',
            'cashier_service_name' => 'Nama Admin',
            'cashier_phone' => 'No. Telepon Admin',
            'payment_method' => 'Metode Pembayaran',
            'paid_amount' => 'Jumlah Bayar',
            'change_amount' => 'Kembalian',
            'credit_amount' => 'Sisa Kredit',
            'credit_days' => 'Tempo Kredit',
            'credit_due_date' => 'Jatuh Tempo',
            'total' => 'Total',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }
}

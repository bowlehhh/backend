<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminBesarTransactionController extends Controller
{
    public function history(Request $request): View
    {
        return view('admin.admin-besar.history', [
            'user' => $request->user(),
            'sales' => $this->getSales(),
            'installmentPaidMap' => $this->getInstallmentPaidMap(),
            'returns' => $this->getReturns(),
        ]);
    }

    public function historyBySupplier(Request $request): View
    {
        return view('admin.admin-besar.history-supplier', [
            'user' => $request->user(),
            'groups' => $this->getPtCvGroups(),
        ]);
    }

    public function historyBySupplierDetail(Request $request): View
    {
        $ptName = $this->normalizePtCvName((string) $request->query('pt', ''));
        $group = collect($this->getPtCvGroups())
            ->first(fn (array $row): bool => $this->normalizePtCvName((string) ($row['pt_name'] ?? '')) === $ptName);

        abort_if($group === null, 404, 'Riwayat PT/CV tidak ditemukan.');

        return view('admin.admin-besar.history-supplier-detail', [
            'user' => $request->user(),
            'group' => $group,
        ]);
    }

    private function getSales(): array
    {
        return Cache::remember(
            'admin-besar:history:sales:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (! Schema::hasTable('sales')) {
                    return [];
                }

                $itemsAgg = DB::table('sale_items')
                    ->selectRaw('sale_id, COUNT(*) as items_count, COALESCE(SUM(qty), 0) as qty')
                    ->groupBy('sale_id');

                $returnsAgg = DB::table('sales_returns')
                    ->selectRaw('sale_id, COUNT(*) as returns_count, COALESCE(SUM(return_total), 0) as total_return_refund')
                    ->groupBy('sale_id');

                $installmentPaidMap = $this->getInstallmentPaidMap();

                return DB::table('sales')
                    ->leftJoinSub($itemsAgg, 'sale_items_agg', fn ($join) => $join->on('sale_items_agg.sale_id', '=', 'sales.id'))
                    ->leftJoinSub($returnsAgg, 'sales_returns_agg', fn ($join) => $join->on('sales_returns_agg.sale_id', '=', 'sales.id'))
                    ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($query) => $query->whereNull('sales.deleted_at'))
                    ->latest('sales.id')
                    ->limit(100)
                    ->get([
                        'sales.id',
                        'sales.invoice_number',
                        'sales.customer_name',
                        'sales.payment_method',
                        'sales.total',
                        'sales.paid_amount',
                        'sales.credit_amount',
                        'sales.created_at',
                        DB::raw('COALESCE(sale_items_agg.items_count, 0) as items_count'),
                        DB::raw('COALESCE(sale_items_agg.qty, 0) as sold_qty'),
                        DB::raw('COALESCE(sales_returns_agg.returns_count, 0) as returns_count'),
                        DB::raw('COALESCE(sales_returns_agg.total_return_refund, 0) as total_return_refund'),
                    ])
                    ->map(function ($sale) use ($installmentPaidMap): array {
                        $createdAt = ! empty($sale->created_at) ? Carbon::parse($sale->created_at) : null;
                        $isCredit = strtolower((string) $sale->payment_method) === 'credit';
                        $creditAmount = (float) ($sale->credit_amount ?? 0);
                        $installmentPaid = (float) ($installmentPaidMap[(int) $sale->id] ?? 0);
                        $remainingCredit = max(0, $creditAmount - $installmentPaid);

                        return [
                            'id' => (int) $sale->id,
                            'invoice_number' => (string) $sale->invoice_number,
                            'customer_name' => (string) ($sale->customer_name ?: '-'),
                            'created_at' => $createdAt?->format('d M Y H:i') ?: '-',
                            'payment_method' => strtoupper((string) $sale->payment_method),
                            'items_count' => (int) ($sale->items_count ?? 0),
                            'sold_qty' => (int) ($sale->sold_qty ?? 0),
                            'returned_qty' => (int) ($sale->returns_count ?? 0),
                            'total' => (float) $sale->total,
                            'paid_amount' => (float) $sale->paid_amount,
                            'credit_amount' => $creditAmount,
                            'installment_paid' => $installmentPaid,
                            'remaining_credit' => $remainingCredit,
                            'total_return_refund' => (float) ($sale->total_return_refund ?? 0),
                            'payment_status' => $isCredit
                                ? ($remainingCredit > 0 ? 'BELUM LUNAS' : 'LUNAS')
                                : 'LUNAS',
                            'receipt_url' => route('admin.admin-besar.receipt', $sale->id),
                        ];
                    })
                    ->all();
            }
        );
    }

    private function getReturns(): array
    {
        return Cache::remember(
            'admin-besar:history:returns:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (! Schema::hasTable('sales_returns')) {
                    return [];
                }

                return DB::table('sales_returns')
                    ->leftJoin('sales', 'sales.id', '=', 'sales_returns.sale_id')
                    ->leftJoin('users', 'users.id', '=', 'sales_returns.user_id')
                    ->when(Schema::hasColumn('sales_returns', 'deleted_at'), fn ($query) => $query->whereNull('sales_returns.deleted_at'))
                    ->latest('sales_returns.id')
                    ->limit(100)
                    ->get([
                        'sales_returns.id',
                        'sales_returns.return_number',
                        'sales_returns.invoice_number',
                        'sales_returns.return_total',
                        'sales_returns.returned_at',
                        'sales_returns.return_type',
                        'sales_returns.reason',
                        'sales_returns.reason_other',
                        'sales_returns.exchange_total',
                        'sales_returns.price_difference_total',
                        'sales_returns.user_id',
                        'sales.customer_name',
                        'users.name as user_name',
                    ])
                    ->map(fn ($row) => [
                        'id' => (int) $row->id,
                        'return_number' => (string) $row->return_number,
                        'invoice_number' => (string) $row->invoice_number,
                        'return_total' => (float) $row->return_total,
                        'returned_at' => $row->returned_at ? Carbon::parse($row->returned_at) : null,
                        'return_type' => (string) ($row->return_type ?? '-'),
                        'reason' => (string) ($row->reason_other ?: $row->reason ?: '-'),
                        'exchange_total' => (float) ($row->exchange_total ?? 0),
                        'price_difference_total' => (float) ($row->price_difference_total ?? 0),
                        'cashier_name' => (string) ($row->user_name ?: '-'),
                        'customer_name' => (string) ($row->customer_name ?: '-'),
                    ])
                    ->all();
            }
        );
    }

    private function getInstallmentPaidMap(): array
    {
        return Cache::remember(
            'admin-besar:history:installment-paid:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (! Schema::hasTable('sale_installments')) {
                    return [];
                }

                return DB::table('sale_installments')
                    ->selectRaw('sale_id, COALESCE(SUM(amount), 0) as total_amount')
                    ->groupBy('sale_id')
                    ->pluck('total_amount', 'sale_id')
                    ->map(fn ($value) => (float) $value)
                    ->toArray();
            }
        );
    }

    private function getPtCvGroups(): array
    {
        return Cache::remember(
            'admin-besar:history:ptcv:' . now()->toDateString(),
            now()->addSeconds(30),
            function (): array {
                if (! Schema::hasTable('sales')) {
                    return [];
                }

                $itemsAgg = DB::table('sale_items')
                    ->selectRaw('sale_id, COUNT(*) as items_count, COALESCE(SUM(qty), 0) as qty, COALESCE(SUM(subtotal), 0) as subtotal')
                    ->groupBy('sale_id');

                $rows = DB::table('sales')
                    ->leftJoinSub($itemsAgg, 'sale_items_agg', fn ($join) => $join->on('sale_items_agg.sale_id', '=', 'sales.id'))
                    ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($query) => $query->whereNull('sales.deleted_at'))
                    ->whereNotNull('sales.customer_name')
                    ->whereRaw("TRIM(sales.customer_name) <> ''")
                    ->where(fn ($query) => $query->whereRaw("UPPER(TRIM(sales.customer_name)) LIKE 'PT %'")->orWhereRaw("UPPER(TRIM(sales.customer_name)) LIKE 'CV %'"))
                    ->orderByDesc('sales.id')
                    ->get([
                        'sales.id as sale_id',
                        'sales.invoice_number',
                        'sales.customer_name',
                        'sales.payment_method',
                        'sales.total',
                        'sales.credit_amount',
                        'sales.credit_due_date',
                        'sales.created_at',
                        DB::raw('COALESCE(sale_items_agg.items_count, 0) as items_count'),
                        DB::raw('COALESCE(sale_items_agg.qty, 0) as qty'),
                        DB::raw('COALESCE(sale_items_agg.subtotal, 0) as subtotal'),
                    ]);

                $grouped = [];
                foreach ($rows as $row) {
                    $name = $this->normalizePtCvName((string) ($row->customer_name ?? ''));
                    $createdAt = $row->created_at ? Carbon::parse($row->created_at) : null;
                    $method = strtoupper((string) ($row->payment_method ?? 'CASH'));
                    $creditAmount = (float) ($row->credit_amount ?? 0);
                    $dueDate = ! empty($row->credit_due_date) ? Carbon::parse($row->credit_due_date) : null;
                    $status = $method === 'CREDIT' && $creditAmount > 0
                        ? (($dueDate && $dueDate->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS')
                        : 'LUNAS';

                    if (! isset($grouped[$name])) {
                        $grouped[$name] = [
                            'pt_name' => $name,
                            'summary' => [
                                'total_transaksi' => 0,
                                'total_qty' => 0,
                                'total_nilai_value' => 0.0,
                                'kredit' => 0,
                                'jatuh_tempo' => 0,
                                'lunas' => 0,
                            ],
                            'rows' => [],
                        ];
                    }

                    $grouped[$name]['summary']['total_transaksi']++;
                    $grouped[$name]['summary']['total_qty'] += (int) ($row->qty ?? 0);
                    $grouped[$name]['summary']['total_nilai_value'] += (float) ($row->subtotal ?? 0);
                    if (in_array($status, ['BELUM LUNAS', 'JATUH TEMPO'], true)) {
                        $grouped[$name]['summary']['kredit']++;
                    }
                    if ($status === 'JATUH TEMPO') {
                        $grouped[$name]['summary']['jatuh_tempo']++;
                    }
                    if ($status === 'LUNAS') {
                        $grouped[$name]['summary']['lunas']++;
                    }

                    $grouped[$name]['rows'][] = [
                        'sale_id' => (int) $row->sale_id,
                        'invoice' => (string) $row->invoice_number,
                        'waktu' => $createdAt?->format('d M Y H:i') ?: '-',
                        'waktu_raw' => $createdAt?->toDateTimeString() ?: '',
                        'metode' => $method,
                        'qty' => (int) ($row->qty ?? 0),
                        'subtotal' => 'Rp ' . number_format((float) ($row->subtotal ?? 0), 0, ',', '.'),
                        'kredit' => 'Rp ' . number_format($creditAmount, 0, ',', '.'),
                        'jatuh_tempo' => $dueDate ? $dueDate->format('d M Y') : '-',
                        'status' => $status,
                    ];
                }

                return collect($grouped)
                    ->map(function (array $group): array {
                        $group['summary']['total_nilai'] = 'Rp ' . number_format((float) $group['summary']['total_nilai_value'], 0, ',', '.');
                        unset($group['summary']['total_nilai_value']);
                        return $group;
                    })
                    ->values()
                    ->all();
            }
        );
    }

    private function normalizePtCvName(string $name): string
    {
        $normalized = strtoupper(trim($name));
        return preg_replace('/\s+/', ' ', $normalized) ?: 'TANPA PT/CV';
    }
}

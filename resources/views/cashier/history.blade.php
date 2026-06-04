<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>History Transaksi - Toko Pak Paul</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[260px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <h1 class="text-3xl font-extrabold text-emerald-700">Toko Pak Paul</h1>
            <p class="text-xs text-slate-500">Kasir Terminal - Station 01</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('cashier.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span>
                <span class="font-semibold">Register</span>
            </a>
            <a href="{{ route('cashier.history') }}" class="flex items-center gap-3 rounded-xl bg-indigo-500 px-3 py-2 text-white">
                <span class="material-symbols-outlined">history</span>
                <span class="font-semibold">History</span>
            </a>
            <a href="{{ route('cashier.history.supplier') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">local_shipping</span>
                <span class="font-semibold">Supplier</span>
            </a>
            <a href="{{ route('cashier.drafts') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">draft</span>
                <span class="font-semibold">Draft</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-200">
            <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin logout dari akun ini?')">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <span class="material-symbols-outlined">lock_clock</span>
                    <span class="font-semibold">Close Shift</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold">History Transaksi</h2>
                <p class="text-sm text-slate-500">{{ $user?->name }} - Kasir</p>
            </div>
            <a href="{{ route('cashier.dashboard') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Register</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Pembeli</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Metode</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Item</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Total</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">DP</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Sisa Kredit</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($sales as $sale)
                    @php
                        $soldQty = (int) ($sale->sold_qty ?? 0);
                        $returnedQty = (int) ($sale->returned_qty ?? 0);
                        $itemsCount = (int) ($sale->items_count ?? 0);
                        $totalReturnRefund = (float) ($sale->total_return_refund ?? 0);
                        $canReturn = $soldQty > $returnedQty;
                        $hasReturn = $returnedQty > 0 || $totalReturnRefund > 0;
                        $canModify = ! $hasReturn;
                        $creditAmount = (float) ($sale->credit_amount ?? 0);
                        $downPayment = (float) ($sale->paid_amount ?? 0);
                        $isCredit = strtolower((string) $sale->payment_method) === 'credit';
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3">{{ $sale->customer_name ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $sale->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 uppercase">{{ $sale->payment_method }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $soldQty }} qty</div>
                            <div class="text-xs text-slate-500">{{ $itemsCount }} item</div>
                            @if($returnedQty > 0)
                                <div class="text-xs text-amber-600">Diretur: {{ $returnedQty }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold">
                            <div>Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</div>
                            @if($totalReturnRefund > 0)
                                <div class="text-xs font-semibold text-amber-600">Retur: Rp {{ number_format($totalReturnRefund, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right {{ $isCredit ? 'font-semibold text-emerald-700' : 'text-slate-500' }}">
                            {{ $isCredit ? ('Rp ' . number_format($downPayment, 0, ',', '.')) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $isCredit && $creditAmount > 0 ? 'font-semibold text-amber-700' : 'text-slate-500' }}">
                            {{ $isCredit ? ('Rp ' . number_format($creditAmount, 0, ',', '.')) : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('cashier.receipt', $sale) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat Detail</a>
                                <a href="{{ route('cashier.receipt', $sale) }}?pdf=1" target="_blank" rel="noopener" class="rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Print</a>
                                @if($canModify)
                                    <a href="{{ route('cashier.history.edit', $sale) }}" class="rounded-lg border border-indigo-500 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Edit</a>
                                    <form method="POST" action="{{ route('cashier.history.destroy', $sale) }}" class="inline-flex js-delete-sale-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="delete_note" value="" />
                                        <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Hapus</button>
                                    </form>
                                @else
                                    <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">Edit Terkunci</span>
                                @endif
                                @if($canReturn)
                                    <a href="{{ route('cashier.return.form', $sale) }}" class="rounded-lg border border-amber-600 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">Return</a>
                                @else
                                    <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">Return Habis</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $sales->links() }}</div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-xl font-extrabold text-slate-900">Riwayat Return Penjualan</h3>
                <p class="text-sm text-slate-500">Track record return per invoice lengkap dengan nota return.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Nomor Return</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Nomor Invoice</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Barang</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Qty Return</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Jenis</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Alasan</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Nilai Return</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kasir</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($returns as $return)
                        @php
                            $itemNames = collect($return->items ?? [])->pluck('part_name')->filter()->unique()->take(2)->implode(', ');
                            $qtyReturn = (int) collect($return->items ?? [])->sum('qty_return');
                            $reason = trim((string) ($return->reason_other ?: $return->reason ?: '-'));
                        @endphp
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold">{{ $return->return_number }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $return->invoice_number }}</td>
                            <td class="px-4 py-3">{{ $itemNames !== '' ? $itemNames : '-' }}</td>
                            <td class="px-4 py-3">{{ $qtyReturn }}</td>
                            <td class="px-4 py-3 uppercase">{{ str_replace('_', ' ', (string) $return->return_type) }}</td>
                            <td class="px-4 py-3">{{ $reason }}</td>
                            <td class="px-4 py-3 text-right font-bold">Rp {{ number_format((float) $return->return_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $return->returned_at?->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $return->user?->name ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('cashier.return.receipt', $return) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat</a>
                                    <a href="{{ route('cashier.return.receipt', $return) }}?pdf=1" target="_blank" rel="noopener" class="rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Print</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat return.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-xl font-extrabold text-slate-900">Riwayat Edit Transaksi</h3>
                <p class="text-sm text-slate-500">Track record perubahan transaksi kasir.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Field Berubah</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Catatan Edit</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kasir</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($editLogs as $log)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $log->invoice_number }}</td>
                            <td class="px-4 py-3">{{ $log->changed_fields ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $log->edit_note ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $log->editor?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat edit transaksi.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-xl font-extrabold text-slate-900">Riwayat Hapus Transaksi</h3>
                <p class="text-sm text-slate-500">Track record transaksi yang dihapus permanen.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Metode</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Item</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Total</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Kredit</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Alasan Hapus</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kasir</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($deleteLogs as $log)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $log->invoice_number }}</td>
                            <td class="px-4 py-3 uppercase">{{ $log->payment_method ?: '-' }}</td>
                            <td class="px-4 py-3">{{ (int) ($log->items_count ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold">Rp {{ number_format((float) $log->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $log->credit_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $log->delete_note ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $log->deleter?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat hapus transaksi.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
    document.querySelectorAll('.js-delete-sale-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const note = window.prompt('Alasan hapus transaksi (wajib diisi):', '');
            if (note === null) {
                return;
            }

            const trimmed = note.trim();
            if (trimmed === '') {
                alert('Alasan hapus wajib diisi.');
                return;
            }

            form.querySelector('input[name="delete_note"]').value = trimmed;
            form.submit();
        });
    });
</script>
</body>
</html>

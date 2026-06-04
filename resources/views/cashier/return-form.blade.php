<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Retur - {{ $sale->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<main class="mx-auto max-w-6xl p-4 lg:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Form Retur Transaksi</h1>
            <p class="text-sm text-slate-500">Invoice: {{ $sale->invoice_number }} | Kasir: {{ $user?->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.receipt', $sale) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Lihat Nota</a>
            <a href="{{ route('cashier.history') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke History</a>
        </div>
    </div>

    <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4">
        <div>
            <p class="text-xs uppercase text-slate-500">Tanggal</p>
            <p class="font-semibold">{{ $sale->created_at?->format('d M Y H:i') }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Pembeli</p>
            <p class="font-semibold">{{ $sale->customer_name ?: '-' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Metode</p>
            <p class="font-semibold uppercase">{{ $sale->payment_method }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Total</p>
            <p class="font-semibold">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('cashier.return.store', $sale) }}" class="rounded-2xl border border-slate-200 bg-white">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Produk</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Qty Jual</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Sudah Diretur</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Maks Retur</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Harga</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Qty Retur</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sale->items as $index => $item)
                    @php
                        $returnedQty = (int) ($returnedQtyMap[$item->id] ?? 0);
                        $maxReturn = max(0, (int) $item->qty - $returnedQty);
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $item->product_name }}</p>
                            <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}" />
                        </td>
                        <td class="px-4 py-3 text-right">{{ $item->qty }}</td>
                        <td class="px-4 py-3 text-right">{{ $returnedQty }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $maxReturn }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <input
                                type="number"
                                min="0"
                                max="{{ $maxReturn }}"
                                name="items[{{ $index }}][qty]"
                                value="{{ old("items.{$index}.qty", 0) }}"
                                class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-right"
                                @disabled($maxReturn === 0)
                            />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">
            <label class="mb-2 block text-sm font-semibold text-slate-700" for="notes">Catatan Retur (opsional)</label>
            <textarea id="notes" name="notes" rows="3" maxlength="1000" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Alasan retur / catatan kondisi barang">{{ old('notes') }}</textarea>
            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <a href="{{ route('cashier.history') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
                <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-500">Proses Retur</button>
            </div>
        </div>
    </form>
</main>
</body>
</html>


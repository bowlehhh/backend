<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditInstallment;
use App\Models\ProductBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminCreditController extends Controller
{
    public function detail(ProductBatch $batch): View
    {
        [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining] = $this->creditSummary($batch);
        $batch->loadMissing([
            'product:id,name,barcode,unit,brand_id,category_id',
            'product.brand:id,name',
            'product.category:id,name',
            'supplier:id,name',
            'creditInstallments.user:id,name',
        ]);

        $installments = $batch->creditInstallments()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $paymentHistory = $this->buildPaymentHistory($batch, $installments, $downPayment);

        return view('admin.credit-detail', [
            'batch' => $batch,
            'installments' => $installments,
            'totalCredit' => $totalCredit,
            'downPayment' => $downPayment,
            'installmentPaid' => $installmentPaid,
            'totalPaid' => $paid,
            'remainingCredit' => $remaining,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    public function payInstallment(Request $request, ProductBatch $batch): RedirectResponse
    {
        if (! Schema::hasTable('credit_installments')) {
            return back()->withErrors(['credit' => 'Fitur cicilan belum aktif. Jalankan migrasi database terlebih dahulu.']);
        }

        $validated = $request->validate([
            'amount' => ['required', 'string'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'processed_by' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'in:list,detail'],
        ]);

        $amount = $this->parseCurrency($validated['amount'] ?? '0');
        if ($amount <= 0) {
            return back()->withErrors(['credit' => 'Nominal cicilan harus lebih dari 0.']);
        }

        $dueDate = $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->startOfDay() : null;
        $paidAt = ! empty($validated['paid_at'])
            ? Carbon::parse($validated['paid_at'])->startOfDay()
            : now()->startOfDay();
        if ($dueDate && $paidAt->gt($dueDate)) {
            return back()->withErrors(['paid_at' => 'Tanggal bayar tidak boleh melebihi jatuh tempo.']);
        }

        [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining] = $this->creditSummary($batch);
        if ($remaining <= 0) {
            return back()->withErrors(['credit' => 'Kredit INV ini sudah lunas.']);
        }
        if ($amount > $remaining) {
            return back()->withErrors(['credit' => 'Nominal cicilan melebihi sisa kredit.']);
        }

        DB::transaction(function () use ($batch, $request, $validated, $amount): void {
            $installmentData = [
                'product_batch_id' => $batch->id,
                'user_id' => $request->user()?->id,
                'amount' => $amount,
                'paid_at' => ! empty($validated['paid_at']) ? Carbon::parse($validated['paid_at'])->toDateString() : now()->toDateString(),
                'note' => $validated['note'] ?? null,
            ];

            if (Schema::hasColumn('credit_installments', 'processed_by')) {
                $installmentData['processed_by'] = $this->resolveProcessedBy($validated, $request->user()?->name);
            }

            CreditInstallment::create($installmentData);

            $freshRemaining = $this->creditSummary($batch)[4];
            if ($freshRemaining <= 0) {
                $batch->forceFill([
                    'payment_type' => 'LUNAS',
                    'credit_days' => null,
                    'credit_due_date' => null,
                ])->save();
            }
        });

        $redirectTo = ($validated['redirect_to'] ?? 'list') === 'detail'
            ? route('admin.credits.detail', ['batch' => $batch->id])
            : url('/admin/admin-module?type=credits');
        return redirect()->to($redirectTo)->with('success', 'Cicilan kredit berhasil disimpan.');
    }

    public function settle(Request $request, ProductBatch $batch): RedirectResponse
    {
        if (! Schema::hasTable('credit_installments')) {
            return back()->withErrors(['credit' => 'Fitur cicilan belum aktif. Jalankan migrasi database terlebih dahulu.']);
        }

        $validated = $request->validate([
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'processed_by' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'in:list,detail'],
        ]);

        [, , , , $remaining] = $this->creditSummary($batch);
        if ($remaining <= 0) {
            return back()->withErrors(['credit' => 'Kredit INV ini sudah lunas.']);
        }

        $dueDate = $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->startOfDay() : null;
        $paidAt = ! empty($validated['paid_at'])
            ? Carbon::parse($validated['paid_at'])->startOfDay()
            : now()->startOfDay();
        if ($dueDate && $paidAt->gt($dueDate)) {
            return back()->withErrors(['paid_at' => 'Tanggal bayar tidak boleh melebihi jatuh tempo.']);
        }

        DB::transaction(function () use ($batch, $request, $validated, $remaining): void {
            $installmentData = [
                'product_batch_id' => $batch->id,
                'user_id' => $request->user()?->id,
                'amount' => $remaining,
                'paid_at' => ! empty($validated['paid_at']) ? Carbon::parse($validated['paid_at'])->toDateString() : now()->toDateString(),
                'note' => $validated['note'] ?? 'Pelunasan kredit',
            ];

            if (Schema::hasColumn('credit_installments', 'processed_by')) {
                $installmentData['processed_by'] = $this->resolveProcessedBy($validated, $request->user()?->name);
            }

            CreditInstallment::create($installmentData);

            $batch->forceFill([
                'payment_type' => 'LUNAS',
                'credit_days' => null,
                'credit_due_date' => null,
            ])->save();
        });

        $redirectTo = ($validated['redirect_to'] ?? 'list') === 'detail'
            ? route('admin.credits.detail', ['batch' => $batch->id])
            : url('/admin/admin-module?type=credits');
        return redirect()->to($redirectTo)->with('success', 'Kredit berhasil dilunaskan.');
    }

    public function receipt(Request $request, ProductBatch $batch): View|Response
    {
        [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining] = $this->creditSummary($batch);
        $batch->loadMissing([
            'product:id,name,barcode,unit,brand_id,category_id',
            'product.brand:id,name',
            'product.category:id,name',
            'supplier:id,name',
            'creditInstallments.user:id,name',
        ]);
        $installments = $batch->creditInstallments()->with('user:id,name')->orderBy('paid_at')->orderBy('id')->get();
        $paymentHistory = $this->buildPaymentHistory($batch, $installments, $downPayment);

        $viewData = [
            'batch' => $batch,
            'installments' => $installments,
            'totalCredit' => $totalCredit,
            'downPayment' => $downPayment,
            'installmentPaid' => $installmentPaid,
            'totalPaid' => $paid,
            'remainingCredit' => $remaining,
            'paymentHistory' => $paymentHistory,
            'printedAt' => now(),
        ];

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.credit-receipt', $viewData)->setPaper('a4', 'portrait');
            return $pdf->download('nota-kredit-' . $batch->id . '.pdf');
        }

        return response()->view('admin.credit-receipt', $viewData)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function installmentReceipt(Request $request, ProductBatch $batch, CreditInstallment $installment): View|Response
    {
        if ((int) $installment->product_batch_id !== (int) $batch->id) {
            abort(404);
        }

        [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining] = $this->creditSummary($batch);
        $batch->loadMissing(['product:id,name,barcode,unit', 'supplier:id,name']);
        $installment->loadMissing('user:id,name');

        $allInstallments = $batch->creditInstallments()
            ->with('user:id,name')
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
        $paymentHistory = $this->buildPaymentHistory($batch, $allInstallments, $downPayment);

        $installmentNumber = max(1, $allInstallments->search(fn ($row) => (int) $row->id === (int) $installment->id) + 1);

        $viewData = [
            'batch' => $batch,
            'installment' => $installment,
            'allInstallments' => $allInstallments,
            'paymentHistory' => $paymentHistory,
            'installmentNumber' => $installmentNumber,
            'totalCredit' => $totalCredit,
            'downPayment' => $downPayment,
            'installmentPaid' => $installmentPaid,
            'totalPaid' => $paid,
            'remainingCredit' => $remaining,
            'printedAt' => now(),
        ];

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.credit-installment-receipt', $viewData)->setPaper('a4', 'portrait');
            return $pdf->download('nota-cicilan-' . $installment->id . '.pdf');
        }

        return response()->view('admin.credit-installment-receipt', $viewData)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function creditSummary(ProductBatch $batch): array
    {
        $qty = (int) ($batch->stock ?? 0);
        $expeditionCost = (float) ($batch->expedition_cost ?? 0);
        $totalCredit = max(0, ($qty * (float) ($batch->purchase_price ?? 0)) + $expeditionCost);
        $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
        $downPayment = max(0, (float) ($batch->down_payment_amount ?? 0));
        $installmentPaid = 0.0;
        $hasInstallmentHistory = false;
        if (Schema::hasTable('credit_installments')) {
            $installmentQuery = CreditInstallment::query()
                ->where('product_batch_id', $batch->id);
            $installmentPaid = (float) $installmentQuery->sum('amount');
            $hasInstallmentHistory = $installmentQuery->exists();
        }
        $hasCreditHistory = $downPayment > 0 || $hasInstallmentHistory;

        if ($paymentType !== 'KREDIT' && ! $hasCreditHistory) {
            $downPayment = 0.0;
            $installmentPaid = 0.0;
            $paid = $totalCredit;
            $remaining = 0.0;
        } else {
            $paid = min($totalCredit, $downPayment + $installmentPaid);
            $remaining = max(0, $totalCredit - $paid);
        }

        return [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining];
    }

    /**
     * @param  iterable<int, CreditInstallment>  $installments
     * @return array<int, array<string, mixed>>
     */
    private function buildPaymentHistory(ProductBatch $batch, iterable $installments, float $downPayment): array
    {
        $history = [];
        $installmentList = collect($installments)->values();
        $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
        $hasCreditHistory = $downPayment > 0 || $installmentList->isNotEmpty();

        if (! $hasCreditHistory) {
            $createdAt = $batch->created_at;
            $history[] = [
                'type' => 'Lunas',
                'date' => $createdAt?->format('d M Y') ?? '-',
                'time' => $createdAt?->format('H:i:s') ?? '-',
                'amount' => (float) ($batch->purchase_price ?? 0) * (int) ($batch->stock ?? 0) + (float) ($batch->expedition_cost ?? 0),
                'user' => '-',
                'processed_by' => trim((string) ($batch->processed_by ?? '')) ?: '-',
                'note' => 'Pembelian lunas',
                'receipt_url' => null,
            ];

            return $history;
        }

        if ($downPayment > 0) {
            $createdAt = $batch->created_at;
            $history[] = [
                'type' => 'DP / Uang Muka',
                'date' => $createdAt?->format('d M Y') ?? '-',
                'time' => $createdAt?->format('H:i:s') ?? '-',
                'amount' => $downPayment,
                'user' => '-',
                'processed_by' => trim((string) ($batch->processed_by ?? '')) ?: '-',
                'note' => 'DP awal pembelian',
                'receipt_url' => null,
            ];
        }

        foreach ($installmentList as $installment) {
            $history[] = [
                'type' => 'Cicilan',
                'date' => $installment->paid_at?->format('d M Y') ?? '-',
                'time' => $installment->created_at?->format('H:i:s') ?? '-',
                'amount' => (float) $installment->amount,
                'user' => $installment->user?->name ?? '-',
                'processed_by' => trim((string) ($installment->processed_by ?? '')) ?: ($installment->user?->name ?? '-'),
                'note' => $installment->note ?: '-',
                'receipt_url' => route('admin.credits.installment.receipt', ['batch' => $batch->id, 'installment' => $installment->id]),
            ];
        }

        return $history;
    }

    private function parseCurrency(string $value): float
    {
        $digits = preg_replace('/[^\d]/', '', $value);
        return (float) ($digits ?: 0);
    }

    private function resolveProcessedBy(array $validated, ?string $fallbackName = null): string
    {
        $name = trim((string) ($validated['processed_by'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $fallback = trim((string) ($fallbackName ?? ''));
        return $fallback !== '' ? $fallback : 'Admin POS';
    }
}

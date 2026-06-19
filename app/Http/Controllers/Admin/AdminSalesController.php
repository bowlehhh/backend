<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;

class AdminSalesController extends Controller
{
    public function receipt(Request $request, Sale $sale): View|Response
    {
        $sale->loadMissing([
            'items.product:id,name,barcode,unit,brand_id',
            'items.product.brand:id,name',
            'items.productBatch.product.brand:id,name',
            'user:id,name',
            'returns',
        ]);
        $isAdminBesar = (bool) $request->user()?->isAdminBesar();
        $historyUrl = $isAdminBesar
            ? url('/admin/admin-besar/history')
            : url('/admin/products');
        $historyLabel = $isAdminBesar ? 'Kembali ke History' : 'Kembali ke Daftar Stok';

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('cashier.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Surya Duta Multindo'),
                'historyUrl' => $historyUrl,
                'historyLabel' => $historyLabel,
                'newTransactionUrl' => url('/admin/products'),
                'showNewTransactionButton' => false,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$sale->invoice_number}.pdf");
        }

        return response()
            ->view('cashier.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Surya Duta Multindo'),
                'historyUrl' => $historyUrl,
                'historyLabel' => $historyLabel,
                'newTransactionUrl' => url('/admin/products'),
                'showNewTransactionButton' => false,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}

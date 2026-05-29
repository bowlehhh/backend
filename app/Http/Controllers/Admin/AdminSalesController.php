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
        $sale->loadMissing(['items', 'user:id,name']);

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('cashier.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => url('/admin/admin-dashboard'),
                'historyLabel' => 'Kembali ke Dashboard Admin',
                'newTransactionUrl' => url('/admin/admin-dashboard'),
                'showNewTransactionButton' => false,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$sale->invoice_number}.pdf");
        }

        return response()
            ->view('cashier.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => url('/admin/admin-dashboard'),
                'historyLabel' => 'Kembali ke Dashboard Admin',
                'newTransactionUrl' => url('/admin/admin-dashboard'),
                'showNewTransactionButton' => false,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}

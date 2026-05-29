<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        $sales = Sale::query()
            ->with('user:id,name')
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->get();

        return SaleResource::collection($sales);
    }

    public function invoice(Request $request, Sale $sale): JsonResponse
    {
        abort_unless(
            $sale->user_id === $request->user()->id || $sale->created_at->isToday(),
            Response::HTTP_FORBIDDEN,
            'You are not allowed to view this invoice.',
        );

        $sale->load(['user:id,name', 'items']);

        return response()->json([
            'sale' => new SaleResource($sale),
            'invoice' => $this->invoiceService->makePayload($sale),
        ]);
    }
}

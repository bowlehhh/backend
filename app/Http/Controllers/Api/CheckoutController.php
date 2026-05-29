<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\SaleResource;
use App\Services\CheckoutService;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly InvoiceService $invoiceService,
    ) {
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $sale = $this->checkoutService->checkout(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'message' => 'Checkout success',
            'sale' => new SaleResource($sale),
            'invoice' => $this->invoiceService->makePayload($sale),
        ], JsonResponse::HTTP_CREATED);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductSearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function __construct(private readonly ProductSearchService $productSearchService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->productSearchService->search(
            $request->query('q'),
            min((int) $request->integer('limit', 20), 50),
        );

        return ProductResource::collection($products);
    }
}

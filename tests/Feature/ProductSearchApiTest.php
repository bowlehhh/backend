<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_only_active_products_with_available_stock(): void
    {
        $cashier = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);
        $brand = Brand::query()->create([
            'name' => 'Aqua',
            'slug' => 'aqua',
            'is_active' => true,
        ]);
        $supplier = Supplier::query()->create([
            'name' => 'Supplier A',
            'is_active' => true,
        ]);

        $available = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Aqua 600ml',
            'slug' => 'aqua-600ml',
            'barcode' => '111',
            'is_active' => true,
        ]);
        ProductBatch::query()->create([
            'product_id' => $available->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'AQ-1',
            'purchase_price' => 2000,
            'selling_price' => 3000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $outOfStock = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Aqua 1500ml',
            'slug' => 'aqua-1500ml',
            'barcode' => '222',
            'is_active' => true,
        ]);
        ProductBatch::query()->create([
            'product_id' => $outOfStock->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'AQ-2',
            'purchase_price' => 4000,
            'selling_price' => 5000,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($cashier, 'sanctum')
            ->getJson('/api/pos/products/search?q=aqua');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Aqua 600ml', $response->json('data.0.name'));
    }
}

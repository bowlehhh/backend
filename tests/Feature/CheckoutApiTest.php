<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_checkout_and_stock_is_reduced(): void
    {
        $cashier = User::factory()->adminBesar()->create();
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
        $product = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Aqua 600ml',
            'slug' => Str::slug('Aqua 600ml'),
            'barcode' => '8990001',
            'is_active' => true,
        ]);
        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'BATCH-001',
            'purchase_price' => 2000,
            'selling_price' => 3000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($cashier, 'sanctum')
            ->postJson('/api/pos/checkout', [
                'payment_method' => 'cash',
                'paid_amount' => 10000,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_batch_id' => $batch->id,
                        'qty' => 2,
                    ],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('sale.total', 6000)
            ->assertJsonPath('sale.change_amount', 4000);

        $this->assertDatabaseHas('product_batches', [
            'id' => $batch->id,
            'stock' => 8,
        ]);

        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'user_id' => $cashier->id,
            'type' => StockHistory::TYPE_OUT,
            'qty' => 2,
            'stock_before' => 10,
            'stock_after' => 8,
        ]);
    }
}

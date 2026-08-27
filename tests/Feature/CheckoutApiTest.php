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

    public function test_merged_stock_uses_the_accumulated_cart_price_for_every_batch(): void
    {
        $cashier = User::factory()->adminBesar()->create();
        $category = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'is_active' => true,
        ]);
        $brand = Brand::query()->create([
            'name' => 'Contoh',
            'slug' => 'contoh',
            'is_active' => true,
        ]);
        $supplier = Supplier::query()->create([
            'name' => 'Supplier Gabung',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Lampu LED',
            'slug' => 'lampu-led',
            'barcode' => 'LED-001',
            'is_active' => true,
        ]);
        $firstBatch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'LED-A',
            'purchase_price' => 80000,
            'selling_price' => 100000,
            'stock' => 1,
            'is_active' => true,
        ]);
        $secondBatch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'LED-B',
            'purchase_price' => 120000,
            'selling_price' => 150000,
            'stock' => 1,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($cashier, 'sanctum')
            ->postJson('/api/pos/checkout', [
                'payment_method' => 'cash',
                'paid_amount' => 250000,
                'items' => [[
                    'product_id' => $product->id,
                    'product_batch_id' => $firstBatch->id,
                    'part_number' => $product->barcode,
                    'merge_stock' => true,
                    'qty' => 2,
                    // Total harga gabungan Rp250.000 dibagi untuk dua qty.
                    'price' => 125000,
                    'stock_allocations' => [
                        $firstBatch->id => 1,
                        $secondBatch->id => 1,
                    ],
                ]],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('sale.total', 250000);

        $this->assertDatabaseHas('sale_items', [
            'product_batch_id' => $firstBatch->id,
            'merge_stock' => true,
            'price' => 125000,
            'qty' => 1,
            'subtotal' => 125000,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_batch_id' => $secondBatch->id,
            'merge_stock' => true,
            'price' => 125000,
            'qty' => 1,
            'subtotal' => 125000,
        ]);
    }
}

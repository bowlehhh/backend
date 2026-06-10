<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBesarReceiptNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_besar_receipt_uses_admin_besar_navigation_links(): void
    {
        $admin = User::factory()->adminBesar()->create();

        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'is_active' => true,
        ]);

        $brand = Brand::query()->create([
            'name' => 'Brand A',
            'slug' => 'brand-a',
            'is_active' => true,
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'PT ABC',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Produk A',
            'slug' => 'produk-a',
            'barcode' => '111222333',
            'is_active' => true,
        ]);

        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'BATCH-001',
            'purchase_price' => 500000,
            'selling_price' => 650000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $sale = Sale::query()->create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-20260610-0001',
            'customer_name' => 'PT ABC',
            'total' => 1000000,
            'payment_method' => 'CASH',
            'paid_amount' => 1000000,
            'change_amount' => 0,
            'credit_amount' => 0,
            'credit_days' => 0,
            'credit_due_date' => null,
        ]);

        SaleItem::query()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => 'Produk A',
            'price' => 1000000,
            'qty' => 1,
            'subtotal' => 1000000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.admin-besar.receipt', $sale));

        $response->assertOk();
        $response->assertSee(route('admin.admin-besar.index'), false);
        $response->assertSeeText('Kembali ke Admin Besar');
    }
}

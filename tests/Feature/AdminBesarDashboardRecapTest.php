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

class AdminBesarDashboardRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_besar_dashboard_shows_company_recap_with_invoices_and_statuses(): void
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
            'name' => 'PT Supplier',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Produk A',
            'slug' => 'produk-a',
            'barcode' => '123456789',
            'is_active' => true,
        ]);

        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'BATCH-001',
            'purchase_price' => 500000,
            'selling_price' => 650000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $sale1 = Sale::query()->create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-001',
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
            'sale_id' => $sale1->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => 'Produk A',
            'price' => 1000000,
            'qty' => 1,
            'subtotal' => 1000000,
        ]);

        $sale2 = Sale::query()->create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-002',
            'customer_name' => 'PT ABC',
            'total' => 2000000,
            'payment_method' => 'CREDIT',
            'paid_amount' => 1500000,
            'change_amount' => 0,
            'credit_amount' => 500000,
            'credit_days' => 7,
            'credit_due_date' => now()->addDays(7),
        ]);

        SaleItem::query()->create([
            'sale_id' => $sale2->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => 'Produk A',
            'price' => 2000000,
            'qty' => 1,
            'subtotal' => 2000000,
        ]);

        $sale3 = Sale::query()->create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-003',
            'customer_name' => 'PT XYZ',
            'total' => 3000000,
            'payment_method' => 'CASH',
            'paid_amount' => 3000000,
            'change_amount' => 0,
            'credit_amount' => 0,
            'credit_days' => 0,
            'credit_due_date' => null,
        ]);

        SaleItem::query()->create([
            'sale_id' => $sale3->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => 'Produk A',
            'price' => 3000000,
            'qty' => 1,
            'subtotal' => 3000000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.admin-besar.index'));

        $response->assertOk();
        $response->assertSeeText('Rekap Perusahaan');
        $response->assertSeeText('PT ABC');
        $response->assertSeeText('PT XYZ');
        $response->assertSeeText('INV-001');
        $response->assertSeeText('INV-002');
        $response->assertSeeText('INV-003');
        $response->assertSeeText('BELUM LUNAS');
        $response->assertSeeText('LUNAS');
        $response->assertSeeText('Rp 6.000.000');
    }
}

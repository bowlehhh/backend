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

class CashierReceiptDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_shows_discount_amount_and_percentage_when_sale_has_discount(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::query()->create([
            'name' => 'Sparepart',
            'slug' => 'sparepart',
            'is_active' => true,
        ]);

        $brand = Brand::query()->create([
            'name' => 'Brand Test',
            'slug' => 'brand-test',
            'is_active' => true,
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'Supplier Test',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Kijang',
            'slug' => 'kijang',
            'barcode' => '12938HB02HF',
            'unit' => 'UNIT',
            'is_active' => true,
        ]);

        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'BATCH-TEST-001',
            'purchase_price' => 800000,
            'selling_price' => 1000000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $sale = Sale::query()->create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-20260626-0001',
            'customer_name' => 'Darma',
            'total' => 800000,
            'discount_amount' => 200000,
            'payment_method' => 'cash',
            'paid_amount' => 800000,
            'change_amount' => 0,
            'credit_amount' => 0,
            'credit_days' => 0,
            'credit_due_date' => null,
        ]);

        SaleItem::query()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => 'Kijang',
            'part_number' => '12938HB02HF',
            'price' => 1000000,
            'qty' => 1,
            'subtotal' => 1000000,
        ]);

        $response = $this->actingAs($admin)->get(route('cashier.receipt', $sale));

        $response->assertOk();
        $response->assertSee('DISKON (20%)', false);
        $response->assertSee('- Rp 200.000', false);
    }
}

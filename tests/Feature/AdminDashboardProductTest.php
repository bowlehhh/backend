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
use Tests\TestCase;

class AdminDashboardProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_from_dashboard_modal_endpoint(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'is_active' => true,
        ]);
        $brand = Brand::query()->create([
            'name' => 'Indofood',
            'slug' => 'indofood',
            'is_active' => true,
        ]);
        $supplier = Supplier::query()->create([
            'name' => 'Supplier Utama',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('admin.dashboard.products.store'), [
                'name' => 'Indomie Goreng',
                'barcode' => '8992388111111',
                'description' => 'Mi instan',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'supplier_id' => $supplier->id,
                'batch_code' => 'INDO-001',
                'purchase_price' => 2500,
                'selling_price' => 3500,
                'stock' => 30,
                'is_active' => true,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('product.name', 'Indomie Goreng')
            ->assertJsonPath('product.category_id', $category->id)
            ->assertJsonPath('product.supplier_id', $supplier->id)
            ->assertJsonPath('product.stock', 30);

        $this->assertDatabaseHas('products', [
            'name' => 'Indomie Goreng',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'barcode' => '8992388111111',
        ]);

        $this->assertDatabaseHas('product_batches', [
            'batch_code' => 'INDO-001',
            'supplier_id' => $supplier->id,
            'stock' => 30,
        ]);

        $this->assertDatabaseHas('stock_histories', [
            'user_id' => $admin->id,
            'type' => StockHistory::TYPE_IN,
            'stock_before' => 0,
            'stock_after' => 30,
        ]);
    }

    public function test_admin_can_update_product_from_dashboard_modal_endpoint(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::query()->create([
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);
        $newCategory = Category::query()->create([
            'name' => 'Snack',
            'slug' => 'snack',
            'is_active' => true,
        ]);
        $brand = Brand::query()->create([
            'name' => 'Teh Botol',
            'slug' => 'teh-botol',
            'is_active' => true,
        ]);
        $newBrand = Brand::query()->create([
            'name' => 'Mayora',
            'slug' => 'mayora',
            'is_active' => true,
        ]);
        $supplier = Supplier::query()->create([
            'name' => 'Supplier A',
            'is_active' => true,
        ]);
        $newSupplier = Supplier::query()->create([
            'name' => 'Supplier B',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Teh Botol 350ml',
            'slug' => 'teh-botol-350ml',
            'barcode' => '111222333',
            'description' => 'Minuman teh',
            'is_active' => true,
        ]);

        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'batch_code' => 'TB-001',
            'purchase_price' => 4000,
            'selling_price' => 5000,
            'stock' => 12,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->putJson(route('admin.dashboard.products.update', $product), [
                'name' => 'Roma Kelapa',
                'barcode' => '555666777',
                'description' => 'Biskuit kelapa',
                'category_id' => $newCategory->id,
                'brand_id' => $newBrand->id,
                'supplier_id' => $newSupplier->id,
                'batch_code' => 'RM-009',
                'purchase_price' => 4500,
                'selling_price' => 6000,
                'stock' => 25,
                'is_active' => false,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('product.name', 'Roma Kelapa')
            ->assertJsonPath('product.category_id', $newCategory->id)
            ->assertJsonPath('product.brand_id', $newBrand->id)
            ->assertJsonPath('product.supplier_id', $newSupplier->id)
            ->assertJsonPath('product.stock', 25)
            ->assertJsonPath('product.is_active', false);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Roma Kelapa',
            'category_id' => $newCategory->id,
            'brand_id' => $newBrand->id,
            'barcode' => '555666777',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_batches', [
            'id' => $batch->id,
            'supplier_id' => $newSupplier->id,
            'batch_code' => 'RM-009',
            'purchase_price' => 4500,
            'selling_price' => 6000,
            'stock' => 25,
        ]);

        $this->assertDatabaseHas('stock_histories', [
            'user_id' => $admin->id,
            'type' => StockHistory::TYPE_ADJUST,
            'stock_before' => 12,
            'stock_after' => 25,
        ]);
    }

    public function test_dashboard_product_validation_errors_are_returned_as_json(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->postJson(route('admin.dashboard.products.store'), []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'category_id',
                'brand_id',
                'supplier_id',
                'purchase_price',
                'selling_price',
                'stock',
                'is_active',
            ]);
    }
}

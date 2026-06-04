<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_returns')) {
            Schema::create('sales_returns', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('return_number')->unique();
                $table->string('invoice_number');
                $table->string('return_type', 20);
                $table->string('reason', 50);
                $table->string('reason_other')->nullable();
                $table->decimal('return_total', 14, 2)->default(0);
                $table->decimal('refund_amount', 14, 2)->default(0);
                $table->timestamp('returned_at');
                $table->timestamps();
                $table->index(['sale_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('sales_return_items')) {
            Schema::create('sales_return_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('product_batch_id')->constrained('product_batches')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('product_name');
                $table->decimal('price', 14, 2);
                $table->unsignedInteger('qty_sold');
                $table->unsignedInteger('qty_return');
                $table->decimal('subtotal_return', 14, 2);
                $table->foreignId('replacement_product_id')->nullable()->constrained('products')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('replacement_batch_id')->nullable()->constrained('product_batches')->cascadeOnUpdate()->nullOnDelete();
                $table->unsignedInteger('replacement_qty')->nullable();
                $table->timestamps();
                $table->index('sales_return_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_returns')) {
            Schema::table('sales_returns', function (Blueprint $table): void {
                if (! Schema::hasColumn('sales_returns', 'exchange_total')) {
                    $table->decimal('exchange_total', 14, 2)->default(0)->after('refund_amount');
                }

                if (! Schema::hasColumn('sales_returns', 'price_difference_total')) {
                    $table->decimal('price_difference_total', 14, 2)->default(0)->after('exchange_total');
                }
            });
        }

        if (Schema::hasTable('sales_return_items')) {
            Schema::table('sales_return_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('sales_return_items', 'replacement_price')) {
                    $table->decimal('replacement_price', 14, 2)->nullable()->after('replacement_qty');
                }

                if (! Schema::hasColumn('sales_return_items', 'replacement_subtotal')) {
                    $table->decimal('replacement_subtotal', 14, 2)->nullable()->after('replacement_price');
                }

                if (! Schema::hasColumn('sales_return_items', 'price_difference')) {
                    $table->decimal('price_difference', 14, 2)->nullable()->after('replacement_subtotal');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_return_items')) {
            Schema::table('sales_return_items', function (Blueprint $table): void {
                if (Schema::hasColumn('sales_return_items', 'price_difference')) {
                    $table->dropColumn('price_difference');
                }

                if (Schema::hasColumn('sales_return_items', 'replacement_subtotal')) {
                    $table->dropColumn('replacement_subtotal');
                }

                if (Schema::hasColumn('sales_return_items', 'replacement_price')) {
                    $table->dropColumn('replacement_price');
                }
            });
        }

        if (Schema::hasTable('sales_returns')) {
            Schema::table('sales_returns', function (Blueprint $table): void {
                if (Schema::hasColumn('sales_returns', 'price_difference_total')) {
                    $table->dropColumn('price_difference_total');
                }

                if (Schema::hasColumn('sales_returns', 'exchange_total')) {
                    $table->dropColumn('exchange_total');
                }
            });
        }
    }
};

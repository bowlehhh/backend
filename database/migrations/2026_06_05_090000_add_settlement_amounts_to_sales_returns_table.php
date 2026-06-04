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
                if (! Schema::hasColumn('sales_returns', 'extra_payment_amount')) {
                    $table->decimal('extra_payment_amount', 14, 2)->default(0)->after('price_difference_total');
                }

                if (! Schema::hasColumn('sales_returns', 'extra_payment_change_amount')) {
                    $table->decimal('extra_payment_change_amount', 14, 2)->default(0)->after('extra_payment_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_returns')) {
            Schema::table('sales_returns', function (Blueprint $table): void {
                if (Schema::hasColumn('sales_returns', 'extra_payment_change_amount')) {
                    $table->dropColumn('extra_payment_change_amount');
                }

                if (Schema::hasColumn('sales_returns', 'extra_payment_amount')) {
                    $table->dropColumn('extra_payment_amount');
                }
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_batches')) {
            return;
        }

        if (! Schema::hasColumn('product_batches', 'down_payment_amount')) {
            Schema::table('product_batches', function (Blueprint $table): void {
                $table->decimal('down_payment_amount', 14, 2)->default(0)->after('expedition_cost');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_batches')) {
            return;
        }

        if (Schema::hasColumn('product_batches', 'down_payment_amount')) {
            Schema::table('product_batches', function (Blueprint $table): void {
                $table->dropColumn('down_payment_amount');
            });
        }
    }
};

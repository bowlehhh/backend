<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_batches', 'expedition_cost')) {
                $table->decimal('expedition_cost', 14, 2)->default(0)->after('purchase_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            if (Schema::hasColumn('product_batches', 'expedition_cost')) {
                $table->dropColumn('expedition_cost');
            }
        });
    }
};

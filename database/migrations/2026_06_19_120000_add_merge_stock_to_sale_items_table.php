<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('sale_items', 'merge_stock')) {
                $table->boolean('merge_stock')->default(false)->after('product_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table): void {
            if (Schema::hasColumn('sale_items', 'merge_stock')) {
                $table->dropColumn('merge_stock');
            }
        });
    }
};

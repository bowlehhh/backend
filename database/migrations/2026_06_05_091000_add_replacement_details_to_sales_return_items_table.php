<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_return_items')) {
            Schema::table('sales_return_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('sales_return_items', 'replacement_details')) {
                    $table->json('replacement_details')->nullable()->after('price_difference');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_return_items')) {
            Schema::table('sales_return_items', function (Blueprint $table): void {
                if (Schema::hasColumn('sales_return_items', 'replacement_details')) {
                    $table->dropColumn('replacement_details');
                }
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            $table->date('purchase_date')->nullable()->after('supplier_invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            $table->dropColumn('purchase_date');
        });
    }
};

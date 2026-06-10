<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_batches', 'supplier_invoice_number')) {
                $table->string('supplier_invoice_number')->nullable()->after('batch_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            if (Schema::hasColumn('product_batches', 'supplier_invoice_number')) {
                $table->dropColumn('supplier_invoice_number');
            }
        });
    }
};

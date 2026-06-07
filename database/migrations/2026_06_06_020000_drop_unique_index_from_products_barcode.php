<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'barcode')) {
                try {
                    $table->dropUnique('products_barcode_unique');
                } catch (\Throwable $e) {
                    // Index may already be missing on some environments.
                }

                $table->index('barcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'barcode')) {
                try {
                    $table->dropIndex(['barcode']);
                } catch (\Throwable $e) {
                    // Ignore if index is absent.
                }

                $table->unique('barcode');
            }
        });
    }
};

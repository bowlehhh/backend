<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'unit')) {
                $afterColumn = Schema::hasColumn('products', 'barcode') ? 'barcode' : 'slug';
                $table->string('unit', 30)->nullable()->after($afterColumn);
            }

            if (! Schema::hasColumn('products', 'weight')) {
                $afterColumn = Schema::hasColumn('products', 'unit') ? 'unit' : (Schema::hasColumn('products', 'barcode') ? 'barcode' : 'slug');
                $table->decimal('weight', 10, 2)->nullable()->after($afterColumn);
            }

            if (! Schema::hasColumn('products', 'weight_unit')) {
                $afterColumn = Schema::hasColumn('products', 'weight') ? 'weight' : (Schema::hasColumn('products', 'unit') ? 'unit' : (Schema::hasColumn('products', 'barcode') ? 'barcode' : 'slug'));
                $table->string('weight_unit', 30)->nullable()->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'weight_unit')) {
                $table->dropColumn('weight_unit');
            }

            if (Schema::hasColumn('products', 'weight')) {
                $table->dropColumn('weight');
            }

            if (Schema::hasColumn('products', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};

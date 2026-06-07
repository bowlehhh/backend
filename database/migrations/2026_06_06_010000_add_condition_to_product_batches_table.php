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

        if (! Schema::hasColumn('product_batches', 'condition')) {
            Schema::table('product_batches', function (Blueprint $table): void {
                $table->string('condition', 120)->nullable()->after('batch_code');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_batches')) {
            return;
        }

        if (Schema::hasColumn('product_batches', 'condition')) {
            Schema::table('product_batches', function (Blueprint $table): void {
                $table->dropColumn('condition');
            });
        }
    }
};

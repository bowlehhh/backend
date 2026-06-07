<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_batches') && ! Schema::hasColumn('product_batches', 'processed_by')) {
            Schema::table('product_batches', function (Blueprint $table): void {
                $table->string('processed_by')->nullable()->after('condition');
            });
        }

        if (Schema::hasTable('credit_installments') && ! Schema::hasColumn('credit_installments', 'processed_by')) {
            Schema::table('credit_installments', function (Blueprint $table): void {
                $table->string('processed_by')->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_batches') && Schema::hasColumn('product_batches', 'processed_by')) {
            Schema::table('product_batches', function (Blueprint $table): void {
                $table->dropColumn('processed_by');
            });
        }

        if (Schema::hasTable('credit_installments') && Schema::hasColumn('credit_installments', 'processed_by')) {
            Schema::table('credit_installments', function (Blueprint $table): void {
                $table->dropColumn('processed_by');
            });
        }
    }
};

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

        Schema::table('product_batches', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_batches', 'payment_type')) {
                $table->string('payment_type', 20)->default('LUNAS');
            }

            if (! Schema::hasColumn('product_batches', 'credit_days')) {
                $table->unsignedInteger('credit_days')->nullable();
            }

            if (! Schema::hasColumn('product_batches', 'credit_due_date')) {
                $table->date('credit_due_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_batches')) {
            return;
        }

        Schema::table('product_batches', function (Blueprint $table): void {
            if (Schema::hasColumn('product_batches', 'credit_due_date')) {
                $table->dropColumn('credit_due_date');
            }

            if (Schema::hasColumn('product_batches', 'credit_days')) {
                $table->dropColumn('credit_days');
            }

            if (Schema::hasColumn('product_batches', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};

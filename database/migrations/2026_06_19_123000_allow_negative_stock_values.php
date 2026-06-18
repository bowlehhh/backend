<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            $table->integer('stock')->default(0)->change();
        });

        Schema::table('stock_histories', function (Blueprint $table): void {
            $table->integer('stock_before')->change();
            $table->integer('stock_after')->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            $table->unsignedInteger('stock')->default(0)->change();
        });

        Schema::table('stock_histories', function (Blueprint $table): void {
            $table->unsignedInteger('stock_before')->change();
            $table->unsignedInteger('stock_after')->change();
        });
    }
};

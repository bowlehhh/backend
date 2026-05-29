<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'cashier_service_name')) {
                $table->string('cashier_service_name')->nullable()->after('customer_name');
            }

            if (! Schema::hasColumn('sales', 'cashier_phone')) {
                $table->string('cashier_phone', 30)->nullable()->after('cashier_service_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'cashier_phone')) {
                $table->dropColumn('cashier_phone');
            }

            if (Schema::hasColumn('sales', 'cashier_service_name')) {
                $table->dropColumn('cashier_service_name');
            }
        });
    }
};

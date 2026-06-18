<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'po_number')) {
                $table->string('po_number', 100)->nullable()->after('customer_phone');
            }

            if (! Schema::hasColumn('sales', 'site_name')) {
                $table->string('site_name', 100)->nullable()->after('po_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'site_name')) {
                $table->dropColumn('site_name');
            }

            if (Schema::hasColumn('sales', 'po_number')) {
                $table->dropColumn('po_number');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_installments', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_installments', 'received_amount')) {
                $table->decimal('received_amount', 14, 2)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('sale_installments', 'change_amount')) {
                $table->decimal('change_amount', 14, 2)->default(0)->after('received_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_installments', function (Blueprint $table) {
            $table->dropColumn(['received_amount', 'change_amount']);
        });
    }
};

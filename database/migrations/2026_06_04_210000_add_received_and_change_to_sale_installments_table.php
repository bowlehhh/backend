<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sale_installments')) {
            return;
        }

        Schema::table('sale_installments', function (Blueprint $table): void {
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
        if (! Schema::hasTable('sale_installments')) {
            return;
        }

        Schema::table('sale_installments', function (Blueprint $table): void {
            if (Schema::hasColumn('sale_installments', 'change_amount')) {
                $table->dropColumn('change_amount');
            }

            if (Schema::hasColumn('sale_installments', 'received_amount')) {
                $table->dropColumn('received_amount');
            }
        });
    }
};

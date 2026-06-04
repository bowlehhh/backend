<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_name');
            }

            if (! Schema::hasColumn('sales', 'credit_amount')) {
                $table->decimal('credit_amount', 14, 2)->default(0)->after('change_amount');
            }

            if (! Schema::hasColumn('sales', 'credit_days')) {
                $table->unsignedInteger('credit_days')->nullable()->after('credit_amount');
            }

            if (! Schema::hasColumn('sales', 'credit_due_date')) {
                $table->date('credit_due_date')->nullable()->after('credit_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'credit_due_date')) {
                $table->dropColumn('credit_due_date');
            }

            if (Schema::hasColumn('sales', 'credit_days')) {
                $table->dropColumn('credit_days');
            }

            if (Schema::hasColumn('sales', 'credit_amount')) {
                $table->dropColumn('credit_amount');
            }

            if (Schema::hasColumn('sales', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
        });
    }
};

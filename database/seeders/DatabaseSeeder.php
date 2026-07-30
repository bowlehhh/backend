<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tablesToTruncate = [
            'sale_installments',
            'credit_installments',
            'sales_return_items',
            'sales_returns',
            'sale_delete_logs',
            'sale_edit_logs',
            'cashier_drafts',
            'stock_histories',
            'sale_items',
            'sales',
            'product_batches',
            'products',
            'suppliers',
            'brands',
            'categories',
            'users',
            'personal_access_tokens',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->call(DefaultUserSeeder::class);
    }
}

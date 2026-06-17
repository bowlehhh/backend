<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        User::query()->create([
            'email' => 'suryadutamultindo@gmail.com',
            'name' => 'Admin Toko/Gudang',
            'password' => Hash::make('suryadutamultindo123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'email' => 'suryadi.paulus06@gmail.com',
            'name' => 'Admin Besar',
            'password' => Hash::make('suryadutamultindo123'),
            'role' => User::ROLE_ADMIN_BESAR,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}

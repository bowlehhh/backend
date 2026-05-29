<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@pos.test',
        ], [
            'name' => 'Admin POS',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->updateOrCreate([
            'email' => 'cashier@pos.test',
        ], [
            'name' => 'Cashier POS',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CASHIER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}

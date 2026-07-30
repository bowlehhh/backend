<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Create the initial accounts without modifying any existing user or data.
     */
    public function run(): void
    {
        $accounts = [
            [
                'email' => 'suryadutamultindo@gmail.com',
                'name' => 'Admin Toko/Gudang',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'email' => 'suryadi.paulus06@gmail.com',
                'name' => 'Admin Besar',
                'role' => User::ROLE_ADMIN_BESAR,
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('suryadutamultindo123'),
                    'role' => $account['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}

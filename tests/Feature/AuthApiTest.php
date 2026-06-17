<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_login_and_get_pos_redirect(): void
    {
        $user = User::factory()->adminBesar()->create([
            'email' => 'cashier@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('redirect_to', '/pos')
            ->assertJsonPath('user.role', User::ROLE_CASHIER);
    }

    public function test_admin_cannot_login_from_pos_api(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_admin_cannot_access_cashier_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/pos/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_besar_can_access_cashier_routes(): void
    {
        $adminBesar = User::factory()->adminBesar()->create();

        $response = $this
            ->actingAs($adminBesar, 'sanctum')
            ->getJson('/api/pos/dashboard');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'today_sales_total',
                    'today_transactions_count',
                    'today_revenue_total',
                    'recent_transactions',
                ],
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBesarHistoryNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_besar_history_shows_admin_besar_back_link(): void
    {
        $admin = User::factory()->adminBesar()->create();

        $response = $this->actingAs($admin)->get(route('admin.admin-besar.history'));

        $response->assertOk();
        $response->assertSee(route('admin.admin-besar.index'), false);
        $response->assertSeeText('Kembali ke Admin Besar');
        $response->assertDontSee(route('cashier.dashboard'), false);
    }
}

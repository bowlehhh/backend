<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_besar_can_access_admin_toko_area(): void
    {
        $adminBesar = User::factory()->adminBesar()->create();

        $response = $this->actingAs($adminBesar)->get(route('admin.transaksi.dashboard'));

        $response->assertOk();
    }

    public function test_admin_toko_cannot_access_admin_besar_area(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.admin-besar.index'));

        $response->assertForbidden();
    }
}

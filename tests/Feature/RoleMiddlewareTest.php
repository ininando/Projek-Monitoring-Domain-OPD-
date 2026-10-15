<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Route uji terisolasi untuk menguji middleware role secara langsung (tidak bergantung route aplikasi).
        Route::middleware(['web', 'auth', 'role:super_admin'])
            ->get('/_uji/khusus-super-admin', fn () => 'ok');
    }

    public function test_admin_mendapat_403_di_route_super_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/_uji/khusus-super-admin')
            ->assertForbidden()
            ->assertSee('Akses Ditolak');
    }

    public function test_super_admin_boleh_akses_route_super_admin(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/_uji/khusus-super-admin')
            ->assertOk();
    }

    public function test_admin_dan_super_admin_boleh_akses_opd_dan_domain(): void
    {
        foreach ([User::factory()->create(), User::factory()->superAdmin()->create()] as $user) {
            $this->actingAs($user)->get(route('opd.index'))->assertOk();
            $this->actingAs($user)->get(route('domain.index'))->assertOk();
        }
    }

    public function test_role_tidak_dikenal_mendapat_403(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'tamu']); // tidak disimpan; hanya simulasi role di luar daftar

        $this->actingAs($user)->get(route('domain.index'))->assertForbidden();
    }
}

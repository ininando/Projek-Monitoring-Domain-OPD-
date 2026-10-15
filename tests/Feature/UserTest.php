<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->superAdmin()->create(['name' => 'Kepala Bidang']);
    }

    private function dataValid(array $override = []): array
    {
        return array_merge([
            'name' => 'Operator Baru',
            'email' => 'operator@tomohon.go.id',
            'role' => 'admin',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $override);
    }

    // ---------- Otorisasi ----------

    public function test_admin_mendapat_403_di_semua_route_user(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();
        $this->actingAs($admin);

        $this->get(route('user.index'))->assertForbidden();
        $this->get(route('user.create'))->assertForbidden();
        $this->post(route('user.store'), $this->dataValid())->assertForbidden();
        $this->get(route('user.edit', $target))->assertForbidden();
        $this->put(route('user.update', $target), $this->dataValid())->assertForbidden();
        $this->delete(route('user.destroy', $target))->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'operator@tomohon.go.id']);
        $this->assertModelExists($target);
    }

    public function test_menu_user_hanya_tampil_untuk_super_admin(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertDontSee('Manajemen User');
        $this->actingAs($this->superAdmin)->get(route('dashboard'))->assertSee('Manajemen User');
    }

    // ---------- CRUD ----------

    public function test_list_user(): void
    {
        User::factory()->create(['name' => 'Budi Operator']);

        $this->actingAs($this->superAdmin)->get(route('user.index'))
            ->assertOk()
            ->assertSeeInOrder(['Kepala Bidang', 'Budi Operator'])
            ->assertSee('Anda');
    }

    public function test_tambah_user_dan_password_di_hash(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('user.store'), $this->dataValid(['email' => ' Operator@Tomohon.go.id ']))
            ->assertRedirect(route('user.index'));

        $user = User::firstWhere('email', 'operator@tomohon.go.id');
        $this->assertNotNull($user);
        $this->assertSame('admin', $user->role);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_validasi_user(): void
    {
        User::factory()->create(['email' => 'ada@tomohon.go.id']);
        $this->actingAs($this->superAdmin);

        $this->post(route('user.store'), [])->assertSessionHasErrors(['name', 'email', 'role', 'password']);
        $this->post(route('user.store'), $this->dataValid(['email' => 'ada@tomohon.go.id']))->assertSessionHasErrors('email');
        $this->post(route('user.store'), $this->dataValid(['role' => 'raja']))->assertSessionHasErrors('role');
        $this->post(route('user.store'), $this->dataValid(['password' => 'pendek1', 'password_confirmation' => 'pendek1']))->assertSessionHasErrors('password');
        $this->post(route('user.store'), $this->dataValid(['password_confirmation' => 'beda12345']))->assertSessionHasErrors('password');
    }

    public function test_edit_tanpa_mengganti_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        $this->actingAs($this->superAdmin)
            ->put(route('user.update', $user), $this->dataValid(['password' => '', 'password_confirmation' => '', 'role' => 'super_admin']))
            ->assertRedirect(route('user.index'));

        $user->refresh();
        $this->assertSame('super_admin', $user->role);
        $this->assertTrue(Hash::check('lama12345', $user->password));
    }

    public function test_edit_mengganti_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->superAdmin)->put(route('user.update', $user), $this->dataValid(['password' => 'baru12345', 'password_confirmation' => 'baru12345']));

        $this->assertTrue(Hash::check('baru12345', $user->fresh()->password));
    }

    public function test_hapus_user_lain(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->superAdmin)->delete(route('user.destroy', $user))
            ->assertRedirect(route('user.index'))->assertSessionHas('success');

        $this->assertModelMissing($user);
    }

    public function test_super_admin_tidak_bisa_menghapus_diri_sendiri(): void
    {
        $this->actingAs($this->superAdmin)->delete(route('user.destroy', $this->superAdmin))
            ->assertRedirect(route('user.index'))
            ->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertModelExists($this->superAdmin);
    }

    public function test_super_admin_tidak_bisa_menurunkan_role_sendiri(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('user.update', $this->superAdmin), $this->dataValid(['email' => $this->superAdmin->email, 'role' => 'admin', 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors('role');

        $this->assertSame('super_admin', $this->superAdmin->fresh()->role);
    }
}

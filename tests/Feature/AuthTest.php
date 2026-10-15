<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_tampil(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Masuk');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('domain.index'))->assertRedirect(route('login'));
        $this->get(route('opd.index'))->assertRedirect(route('login'));
    }

    public function test_login_berhasil_dan_session_diregenerasi(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);
        $this->get(route('login'));
        $sessionLama = session()->getId();

        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionLama, session()->getId());
    }

    public function test_login_gagal_menampilkan_pesan(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->from(route('login'))
            ->post(route('login.attempt'), ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email/Password salah.']);

        $this->assertGuest();
    }

    public function test_login_dikunci_setelah_5_kali_gagal(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        foreach (range(1, 5) as $_) {
            $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_login_tidak_bisa_membuka_halaman_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect('/');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pemeriksaan keamanan dasar: CSRF, escape output, dan validasi server-side.
 */
class KeamananTest extends TestCase
{
    use RefreshDatabase;

    public static function halamanForm(): array
    {
        return [
            'login' => ['/login', false],
            'tambah opd' => ['/opd/create', true],
            'tambah domain' => ['/domain/create', true],
            'tambah user' => ['/user/create', true],
        ];
    }

    #[DataProvider('halamanForm')]
    public function test_setiap_form_post_memiliki_token_csrf(string $url, bool $login): void
    {
        if ($login) {
            $this->actingAs(User::factory()->superAdmin()->create());
        }

        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('/<form[^>]*method="POST"[^>]*>(.*?)<\/form>/si', $html, $forms);
        $this->assertNotEmpty($forms[1], 'Tidak ada form POST di '.$url);
        foreach ($forms[1] as $isi) {
            $this->assertStringContainsString('name="_token"', $isi);
        }
    }

    public function test_request_tanpa_token_csrf_ditolak(): void
    {
        // Middleware CSRF dinonaktifkan otomatis saat test; aktifkan kembali secara eksplisit.
        $this->withMiddleware(ValidateCsrfToken::class);
        $this->app->instance('env', 'production'); // runningUnitTests() tidak lagi melewati pemeriksaan

        $this->actingAs(User::factory()->create())
            ->post('/opd', ['nama_opd' => 'Tanpa Token'])
            ->assertStatus(419);

        $this->assertDatabaseMissing('opd', ['nama_opd' => 'Tanpa Token']);
    }

    public function test_output_di_escape_untuk_mencegah_xss(): void
    {
        $this->actingAs(User::factory()->create());
        $opd = Opd::factory()->create(['nama_opd' => '<script>alert("xss")</script>']);
        $domain = Domain::factory()->for($opd)->create(['keterangan' => '<img src=x onerror=alert(1)>']);

        foreach (['/opd', '/domain', route('domain.show', $domain), route('pemetaan.show', $opd)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('<script>alert("xss")</script>', false)
                ->assertSee('&lt;script&gt;', false);
        }

        $this->get(route('domain.show', $domain))->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_input_berbahaya_tidak_lolos_validasi_domain(): void
    {
        $this->actingAs(User::factory()->create());
        $opd = Opd::factory()->create();

        $this->post(route('domain.store'), [
            'opd_id' => $opd->id,
            'nama_domain' => "x.go.id'; DROP TABLE domains; --",
            'tanggal_expired' => '2027-01-01',
            'status' => 'aktif',
        ])->assertSessionHasErrors('nama_domain');

        $this->assertDatabaseCount('domains', 0);
    }

    public function test_pencarian_dengan_karakter_sql_aman(): void
    {
        $this->actingAs(User::factory()->create());
        Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);

        // Parameter di-bind oleh query builder, jadi string ini hanya dicari sebagai teks.
        $this->get('/opd?search='.urlencode("' OR 1=1 --"))
            ->assertOk()
            ->assertDontSee('Dinas Kesehatan');

        $this->get('/domain?search='.urlencode('%_\\'))->assertOk();
    }

    public function test_ringkasan_error_tampil_dan_menautkan_field(): void
    {
        $this->actingAs(User::factory()->create());

        $this->followingRedirects()
            ->from(route('domain.create'))
            ->post(route('domain.store'), [])
            ->assertSee('isian yang perlu diperbaiki', false)
            ->assertSee('href="#nama_domain"', false)
            ->assertSee('Nama domain wajib diisi.');
    }
}

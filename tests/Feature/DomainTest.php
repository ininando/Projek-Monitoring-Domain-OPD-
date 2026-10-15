<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    private Opd $opd;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->actingAs(User::factory()->create());
        $this->opd = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
    }

    private function dataValid(array $override = []): array
    {
        return array_merge([
            'opd_id' => $this->opd->id,
            'nama_domain' => 'dinkes.tomohon.go.id',
            'tanggal_registrasi' => '2024-01-10',
            'tanggal_expired' => '2027-01-10',
            'status' => 'aktif',
            'registrar' => 'PANDI',
            'nameserver' => 'ns1.tomohon.go.id, ns2.tomohon.go.id',
            'ip_address' => '103.10.20.30',
            'hosting_provider' => 'Server Diskominfo',
            'ssl_status' => 'aktif',
            'keterangan' => 'Website resmi',
        ], $override);
    }

    // ---------- CRUD ----------

    public function test_tambah_domain(): void
    {
        $response = $this->post(route('domain.store'), $this->dataValid());

        $domain = Domain::firstWhere('nama_domain', 'dinkes.tomohon.go.id');
        $this->assertNotNull($domain);
        $response->assertRedirect(route('domain.show', $domain))->assertSessionHas('success');
    }

    public function test_nama_domain_dinormalisasi(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['nama_domain' => ' HTTPS://Dinkes.Tomohon.go.id/ ']));

        $this->assertDatabaseHas('domains', ['nama_domain' => 'dinkes.tomohon.go.id']);
    }

    public function test_detail_domain(): void
    {
        $domain = Domain::factory()->for($this->opd)->expiredDalam(5)->create(['ip_address' => '10.0.0.1']);

        $this->get(route('domain.show', $domain))
            ->assertOk()
            ->assertSee($domain->nama_domain)
            ->assertSee('Dinas Kesehatan')
            ->assertSee('Critical')
            ->assertSee('5 hari lagi')
            ->assertSee('10.0.0.1');
    }

    public function test_edit_domain(): void
    {
        $domain = Domain::factory()->for($this->opd)->create();

        $this->get(route('domain.edit', $domain))->assertOk();

        $this->put(route('domain.update', $domain), $this->dataValid(['nama_domain' => $domain->nama_domain, 'status' => 'tidak_aktif']))
            ->assertRedirect(route('domain.show', $domain));

        $this->assertSame('tidak_aktif', $domain->fresh()->status);
    }

    public function test_update_boleh_memakai_nama_domain_sendiri(): void
    {
        $domain = Domain::factory()->for($this->opd)->create(['nama_domain' => 'sama.tomohon.go.id']);

        $this->put(route('domain.update', $domain), $this->dataValid(['nama_domain' => 'sama.tomohon.go.id']))
            ->assertSessionHasNoErrors();
    }

    public function test_hapus_domain(): void
    {
        $domain = Domain::factory()->for($this->opd)->create();

        $this->delete(route('domain.destroy', $domain))->assertRedirect(route('domain.index'));

        $this->assertModelMissing($domain);
    }

    // ---------- Validasi ----------

    public function test_field_wajib(): void
    {
        $this->post(route('domain.store'), [])
            ->assertSessionHasErrors(['opd_id', 'nama_domain', 'tanggal_expired', 'status']);
    }

    public function test_nama_domain_harus_unik(): void
    {
        Domain::factory()->for($this->opd)->create(['nama_domain' => 'dinkes.tomohon.go.id']);

        $this->post(route('domain.store'), $this->dataValid())->assertSessionHasErrors('nama_domain');
    }

    public function test_format_nama_domain(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['nama_domain' => 'bukan domain!']))
            ->assertSessionHasErrors('nama_domain');
    }

    public function test_tanggal_expired_tidak_boleh_sebelum_registrasi(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['tanggal_registrasi' => '2026-05-01', 'tanggal_expired' => '2026-04-30']))
            ->assertSessionHasErrors('tanggal_expired');
    }

    public function test_tanggal_expired_boleh_tanpa_tanggal_registrasi(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['tanggal_registrasi' => null]))
            ->assertSessionHasNoErrors();
    }

    public function test_format_tanggal_salah(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['tanggal_expired' => '31/12/2026']))
            ->assertSessionHasErrors('tanggal_expired');
    }

    public function test_ip_harus_valid_jika_diisi(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['ip_address' => '999.1.1.1']))
            ->assertSessionHasErrors('ip_address');

        $this->post(route('domain.store'), $this->dataValid(['nama_domain' => 'v6.tomohon.go.id', 'ip_address' => '2001:db8::1']))
            ->assertSessionHasNoErrors();
    }

    public function test_opd_harus_ada_dan_status_harus_valid(): void
    {
        $this->post(route('domain.store'), $this->dataValid(['opd_id' => 9999, 'status' => 'hapus', 'ssl_status' => 'x']))
            ->assertSessionHasErrors(['opd_id', 'status', 'ssl_status']);
    }

    // ---------- List & filter ----------

    public function test_list_diurutkan_dari_yang_paling_mendesak(): void
    {
        Domain::factory()->for($this->opd)->expiredDalam(90)->create(['nama_domain' => 'lama.tomohon.go.id']);
        Domain::factory()->for($this->opd)->expiredDalam(-3)->create(['nama_domain' => 'lewat.tomohon.go.id']);
        Domain::factory()->for($this->opd)->expiredDalam(10)->create(['nama_domain' => 'dekat.tomohon.go.id']);

        $this->get(route('domain.index'))
            ->assertOk()
            ->assertSeeInOrder(['lewat.tomohon.go.id', 'dekat.tomohon.go.id', 'lama.tomohon.go.id'])
            ->assertSee('Expired 3 hari lalu');
    }

    public function test_filter_opd_status_dan_pencarian(): void
    {
        $opdLain = Opd::factory()->create();
        Domain::factory()->for($this->opd)->create(['nama_domain' => 'ppid.tomohon.go.id', 'status' => 'aktif']);
        Domain::factory()->for($this->opd)->create(['nama_domain' => 'arsip.tomohon.go.id', 'status' => 'tidak_aktif']);
        Domain::factory()->for($opdLain)->create(['nama_domain' => 'lain.tomohon.go.id', 'status' => 'aktif']);

        $this->get(route('domain.index', ['opd_id' => $this->opd->id]))
            ->assertSee('ppid.tomohon.go.id')->assertSee('arsip.tomohon.go.id')->assertDontSee('lain.tomohon.go.id');

        $this->get(route('domain.index', ['status' => 'tidak_aktif']))
            ->assertSee('arsip.tomohon.go.id')->assertDontSee('ppid.tomohon.go.id');

        $this->get(route('domain.index', ['search' => 'ppid']))
            ->assertSee('ppid.tomohon.go.id')->assertDontSee('arsip.tomohon.go.id');
    }

    public function test_filter_rentang_tanggal_expired(): void
    {
        Domain::factory()->for($this->opd)->create(['nama_domain' => 'a.tomohon.go.id', 'tanggal_expired' => '2026-11-01']);
        Domain::factory()->for($this->opd)->create(['nama_domain' => 'b.tomohon.go.id', 'tanggal_expired' => '2026-11-30']);
        Domain::factory()->for($this->opd)->create(['nama_domain' => 'c.tomohon.go.id', 'tanggal_expired' => '2026-12-15']);

        $this->get(route('domain.index', ['expired_dari' => '2026-11-01', 'expired_sampai' => '2026-11-30']))
            ->assertViewHas('domains', fn ($p) => $p->total() === 2)
            ->assertDontSee('c.tomohon.go.id');
    }

    public function test_filter_tanggal_tidak_valid_diabaikan_dengan_pesan(): void
    {
        Domain::factory()->for($this->opd)->count(2)->create();

        $this->get(route('domain.index', ['expired_dari' => '2026-12-01', 'expired_sampai' => '2026-01-01']))
            ->assertOk()
            ->assertSee('Tanggal akhir harus sama dengan atau setelah tanggal awal.')
            ->assertViewHas('domains', fn ($p) => $p->total() === 2);
    }

    public function test_list_dipaginasi(): void
    {
        Domain::factory()->for($this->opd)->count(13)->create();

        $this->get(route('domain.index'))->assertViewHas('domains', fn ($p) => $p->count() === 10 && $p->total() === 13);
    }
}

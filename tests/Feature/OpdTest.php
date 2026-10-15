<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_daftar_opd_tampil_dengan_jumlah_domain(): void
    {
        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Komunikasi dan Informatika']);
        Domain::factory()->count(2)->for($opd)->create();

        $this->get(route('opd.index'))
            ->assertOk()
            ->assertSee('Dinas Komunikasi dan Informatika')
            ->assertViewHas('opd', fn ($p) => $p->first()->domains_count === 2);
    }

    public function test_pencarian_opd(): void
    {
        Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        Opd::factory()->create(['nama_opd' => 'Dinas Pariwisata']);

        $this->get(route('opd.index', ['search' => 'Kesehatan']))
            ->assertOk()
            ->assertSee('Dinas Kesehatan')
            ->assertDontSee('Dinas Pariwisata');
    }

    public function test_daftar_opd_dipaginasi(): void
    {
        Opd::factory()->count(15)->create();

        $this->get(route('opd.index'))
            ->assertViewHas('opd', fn ($p) => $p->count() === 10 && $p->total() === 15);
    }

    public function test_tambah_opd(): void
    {
        $this->post(route('opd.store'), [
            'nama_opd' => 'Dinas Pariwisata',
            'kepala_opd' => 'Budi',
            'kontak' => '0431-123456',
            'alamat' => 'Jl. Raya Tomohon',
        ])->assertRedirect(route('opd.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('opd', ['nama_opd' => 'Dinas Pariwisata', 'kepala_opd' => 'Budi']);
    }

    public function test_validasi_nama_opd_wajib(): void
    {
        $this->post(route('opd.store'), ['nama_opd' => ''])
            ->assertSessionHasErrors('nama_opd');

        $this->assertDatabaseCount('opd', 0);
    }

    public function test_edit_opd(): void
    {
        $opd = Opd::factory()->create();

        $this->get(route('opd.edit', $opd))->assertOk()->assertSee($opd->nama_opd);

        $this->put(route('opd.update', $opd), ['nama_opd' => 'Nama Baru'])
            ->assertRedirect(route('opd.index'));

        $this->assertSame('Nama Baru', $opd->fresh()->nama_opd);
    }

    public function test_hapus_opd_tanpa_domain(): void
    {
        $opd = Opd::factory()->create();

        $this->delete(route('opd.destroy', $opd))
            ->assertRedirect(route('opd.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($opd);
    }

    public function test_hapus_opd_yang_masih_punya_domain_ditolak(): void
    {
        $opd = Opd::factory()->create();
        Domain::factory()->for($opd)->create();

        $this->delete(route('opd.destroy', $opd))
            ->assertRedirect(route('opd.index'))
            ->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'masih memiliki 1 domain'));

        $this->assertModelExists($opd);
    }
}

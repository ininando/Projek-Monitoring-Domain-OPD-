<?php

namespace Tests\Feature;

use App\Exports\LaporanExport;
use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use App\Services\LaporanDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private Opd $kesehatan;

    private Opd $pariwisata;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->actingAs(User::factory()->create());

        $this->kesehatan = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        $this->pariwisata = Opd::factory()->create(['nama_opd' => 'Dinas Pariwisata']);

        Domain::factory()->for($this->kesehatan)->create(['nama_domain' => 'lewat.tomohon.go.id', 'tanggal_expired' => '2026-10-10', 'status' => 'aktif']);
        Domain::factory()->for($this->kesehatan)->create(['nama_domain' => 'dekat.tomohon.go.id', 'tanggal_expired' => '2026-10-20', 'status' => 'tidak_aktif']);
        Domain::factory()->for($this->kesehatan)->create(['nama_domain' => 'jauh.tomohon.go.id', 'tanggal_expired' => '2027-06-01', 'status' => 'aktif']);
        Domain::factory()->for($this->pariwisata)->create(['nama_domain' => 'wisata.tomohon.go.id', 'tanggal_expired' => '2026-11-30', 'status' => 'aktif']);
    }

    private function namaDomainPratinjau(array $params): array
    {
        $response = $this->get(route('laporan.index', $params))->assertOk();

        return $response->viewData('domains')->pluck('nama_domain')->all();
    }

    public function test_halaman_laporan_tanpa_parameter(): void
    {
        $this->get(route('laporan.index'))->assertOk()->assertSee('Belum ada pratinjau');
    }

    public function test_filter_per_opd(): void
    {
        $this->assertSame(
            ['lewat.tomohon.go.id', 'dekat.tomohon.go.id', 'jauh.tomohon.go.id'],
            $this->namaDomainPratinjau(['jenis' => 'per_opd', 'opd_id' => $this->kesehatan->id])
        );
    }

    public function test_filter_per_opd_dengan_periode(): void
    {
        $this->assertSame(['dekat.tomohon.go.id'], $this->namaDomainPratinjau([
            'jenis' => 'per_opd', 'opd_id' => $this->kesehatan->id,
            'periode_dari' => '2026-10-15', 'periode_sampai' => '2026-12-31',
        ]));
    }

    public function test_filter_per_status(): void
    {
        $this->assertSame(['dekat.tomohon.go.id'], $this->namaDomainPratinjau(['jenis' => 'per_status', 'status' => 'tidak_aktif']));
    }

    public function test_filter_expired_sudah_akan_dan_semua(): void
    {
        $this->assertSame(['lewat.tomohon.go.id'], $this->namaDomainPratinjau(['jenis' => 'expired', 'kategori' => 'sudah', 'dalam_hari' => 30]));
        $this->assertSame(['dekat.tomohon.go.id'], $this->namaDomainPratinjau(['jenis' => 'expired', 'kategori' => 'akan', 'dalam_hari' => 30]));
        $this->assertSame(['dekat.tomohon.go.id', 'wisata.tomohon.go.id'], $this->namaDomainPratinjau(['jenis' => 'expired', 'kategori' => 'akan', 'dalam_hari' => 60]));
        $this->assertSame(['lewat.tomohon.go.id', 'dekat.tomohon.go.id'], $this->namaDomainPratinjau(['jenis' => 'expired', 'kategori' => 'semua', 'dalam_hari' => 7]));
    }

    public function test_parameter_tidak_relevan_diabaikan(): void
    {
        // status ikut terkirim tapi jenisnya per OPD → status tidak dipakai.
        $this->assertCount(3, $this->namaDomainPratinjau(['jenis' => 'per_opd', 'opd_id' => $this->kesehatan->id, 'status' => 'tidak_aktif']));
    }

    public function test_validasi_parameter(): void
    {
        $this->get(route('laporan.index', ['jenis' => 'per_opd']))
            ->assertOk()->assertSee('OPD wajib diisi.')->assertViewHas('laporan', null);

        $this->get(route('laporan.index', ['jenis' => 'per_opd', 'opd_id' => $this->kesehatan->id, 'periode_dari' => '2026-12-01', 'periode_sampai' => '2026-01-01']))
            ->assertSee('Tanggal akhir periode harus sama dengan atau setelah tanggal awal.');

        $this->get(route('laporan.export', ['jenis' => 'acak', 'format' => 'pdf']))->assertSessionHasErrors('jenis');
    }

    public function test_unduh_pdf(): void
    {
        $response = $this->get(route('laporan.export', ['jenis' => 'per_opd', 'opd_id' => $this->kesehatan->id, 'format' => 'pdf']));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('laporan-per-opd-20261015', $response->headers->get('content-disposition'));
    }

    public function test_isi_pdf_header_tabel_dan_total(): void
    {
        $html = view('laporan.pdf', [
            'laporan' => new LaporanDomain(['jenis' => 'per_status', 'status' => 'aktif']),
            'domains' => Domain::with('opd')->where('status', 'aktif')->orderBy('tanggal_expired')->get(),
            'logo' => null,
        ])->render();

        $this->assertStringContainsString('Laporan Domain per Status', $html);
        $this->assertStringContainsString('Tanggal dibuat', $html);
        foreach (['No', 'Nama Domain', 'OPD', 'Status', 'Tanggal Expired', 'Keterangan'] as $kolom) {
            $this->assertStringContainsString(">{$kolom}</th>", $html);
        }
        $this->assertStringContainsString('3 domain', $html);
        $this->assertStringContainsString('Expired 5 hari lalu', $html);
    }

    public function test_unduh_excel(): void
    {
        Excel::fake();

        $this->get(route('laporan.export', ['jenis' => 'per_status', 'status' => 'aktif', 'format' => 'excel']))->assertOk();

        Excel::assertDownloaded('laporan-per-status-20261015-090000.xlsx', function (LaporanExport $export) {
            $html = $export->view()->render();

            return str_contains($html, 'wisata.tomohon.go.id')
                && ! str_contains($html, 'dekat.tomohon.go.id')
                && str_contains($html, 'Total Domain');
        });
    }

    public function test_file_excel_benar_benar_terbentuk(): void
    {
        $this->get(route('laporan.export', ['jenis' => 'expired', 'kategori' => 'semua', 'dalam_hari' => 30, 'format' => 'excel']))
            ->assertOk()
            ->assertDownload();
    }
}

<?php

namespace Tests\Feature;

use App\Exports\PemetaanDetailExport;
use App\Exports\PemetaanExport;
use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PemetaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->actingAs(User::factory()->create());
    }

    public function test_tabel_pemetaan_jumlah_domain_per_opd(): void
    {
        $a = Opd::factory()->create(['nama_opd' => 'Dinas A']);
        Opd::factory()->create(['nama_opd' => 'Dinas Tanpa Domain']);
        Domain::factory()->for($a)->count(3)->create();

        $this->get(route('pemetaan.index'))
            ->assertOk()
            ->assertSeeInOrder(['Dinas A', '3 domain', 'Dinas Tanpa Domain', '0 domain'])
            ->assertSee(route('pemetaan.show', $a))
            ->assertViewHas('ringkasan', fn ($r) => $r['total_opd'] === 2 && $r['total_domain'] === 3 && $r['opd_tanpa_domain'] === 1);
    }

    public function test_detail_pemetaan_dan_data_chart(): void
    {
        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        Domain::factory()->for($opd)->expiredDalam(-1)->create(['status' => 'aktif', 'nama_domain' => 'lewat.tomohon.go.id']);
        Domain::factory()->for($opd)->expiredDalam(5)->create(['status' => 'aktif']);
        Domain::factory()->for($opd)->expiredDalam(20)->create(['status' => 'tidak_aktif']);
        Domain::factory()->for($opd)->expiredDalam(200)->create(['status' => 'aktif']);
        Domain::factory()->create(['nama_domain' => 'milik-lain.tomohon.go.id']);

        $this->get(route('pemetaan.show', $opd))
            ->assertOk()
            ->assertSee('lewat.tomohon.go.id')
            ->assertDontSee('milik-lain.tomohon.go.id')
            ->assertViewHas('chart', function ($chart) {
                return $chart['label']['data'] === [2, 1, 1]
                    && $chart['status']['data'] === [3, 1]
                    && $chart['sudah_expired'] === 1;
            });
    }

    public function test_export_excel_rekap(): void
    {
        Excel::fake();
        Opd::factory()->create(['nama_opd' => 'Dinas A']);

        $this->get(route('pemetaan.export'))->assertOk();

        Excel::assertDownloaded('pemetaan-domain-opd-20261015.xlsx', function (PemetaanExport $export) {
            $baris = $export->collection();

            return $baris->first() === [1, 'Dinas A', 0] && $baris->last() === ['', 'Total', 0];
        });
    }

    public function test_export_excel_detail_opd(): void
    {
        Excel::fake();
        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        Domain::factory()->for($opd)->expiredDalam(3)->create(['nama_domain' => 'dinkes.tomohon.go.id']);

        $this->get(route('pemetaan.export-detail', $opd))->assertOk();

        Excel::assertDownloaded('domain-dinas-kesehatan-20261015.xlsx', function (PemetaanDetailExport $export) {
            $baris = $export->map($export->collection()->first());

            return $baris[1] === 'dinkes.tomohon.go.id' && $baris[5] === '3 hari lagi' && $baris[6] === 'Critical';
        });
    }

    public function test_file_excel_benar_benar_terbentuk(): void
    {
        $opd = Opd::factory()->create();
        Domain::factory()->for($opd)->count(2)->create();

        $response = $this->get(route('pemetaan.export-detail', $opd));

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
    }
}

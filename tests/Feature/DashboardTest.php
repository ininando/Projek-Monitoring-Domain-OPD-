<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->actingAs(User::factory()->create());
    }

    public function test_beranda_diarahkan_ke_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_kartu_statistik(): void
    {
        $opd = Opd::factory()->create();
        Domain::factory()->for($opd)->expiredDalam(100)->create(['status' => 'aktif']);
        Domain::factory()->for($opd)->expiredDalam(10)->create(['status' => 'aktif']);
        Domain::factory()->for($opd)->expiredDalam(30)->create(['status' => 'tidak_aktif']);
        Domain::factory()->for($opd)->expiredDalam(-2)->create(['status' => 'aktif']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('statistik', [
                'total' => 4,
                'aktif' => 3,
                'tidak_aktif' => 1,
                'akan_expired' => 2,
                'sudah_expired' => 1,
            ]);
    }

    public function test_tabel_perlu_perhatian_urut_paling_mendesak(): void
    {
        $opd = Opd::factory()->create();
        Domain::factory()->for($opd)->expiredDalam(25)->create(['nama_domain' => 'c.tomohon.go.id']);
        Domain::factory()->for($opd)->expiredDalam(-5)->create(['nama_domain' => 'a.tomohon.go.id']);
        Domain::factory()->for($opd)->expiredDalam(3)->create(['nama_domain' => 'b.tomohon.go.id']);
        Domain::factory()->for($opd)->expiredDalam(31)->create(['nama_domain' => 'aman.tomohon.go.id']);

        $this->get(route('dashboard'))
            ->assertSeeInOrder(['a.tomohon.go.id', 'b.tomohon.go.id', 'c.tomohon.go.id'])
            ->assertDontSee('aman.tomohon.go.id')
            ->assertSee('Expired 5 hari lalu');
    }

    public function test_data_chart_json(): void
    {
        $besar = Opd::factory()->create(['nama_opd' => 'Dinas Besar']);
        $kecil = Opd::factory()->create(['nama_opd' => 'Dinas Kecil']);
        Domain::factory()->for($besar)->count(2)->create(['tanggal_registrasi' => '2026-09-10']);
        Domain::factory()->for($kecil)->create(['tanggal_registrasi' => '2026-10-01']);
        Domain::factory()->for($kecil)->create(['tanggal_registrasi' => '2024-01-01']); // sebelum periode grafik

        $json = $this->getJson(route('dashboard.chart'))->assertOk()->json();

        $this->assertSame(['Dinas Besar', 'Dinas Kecil'], $json['per_opd']['labels']);
        $this->assertSame([2, 2], $json['per_opd']['data']);

        $tumbuh = $json['pertumbuhan'];
        $this->assertCount(12, $tumbuh['labels']);
        $this->assertSame('Okt 2026', end($tumbuh['labels']));
        $this->assertSame(1, $tumbuh['total'][0]);       // titik awal kumulatif
        $this->assertSame(2, $tumbuh['baru'][10]);       // September 2026
        $this->assertSame(1, $tumbuh['baru'][11]);       // Oktober 2026
        $this->assertSame(4, end($tumbuh['total']));
    }

    public function test_dashboard_wajib_login(): void
    {
        auth()->logout();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('dashboard.chart'))->assertUnauthorized();
    }
}

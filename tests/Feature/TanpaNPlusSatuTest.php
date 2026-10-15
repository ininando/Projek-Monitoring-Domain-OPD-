<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Jumlah query halaman list/dashboard tidak boleh bertambah seiring jumlah data (N+1).
 */
class TanpaNPlusSatuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function jumlahQuery(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $jumlah = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $jumlah;
    }

    private function tambahData(int $opd, int $domainPerOpd): void
    {
        Opd::factory()->count($opd)->create()->each(
            fn (Opd $o) => Domain::factory()->for($o)->count($domainPerOpd)->expiredDalam(10)->create()
        );
    }

    public static function halaman(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'chart dashboard' => ['/dashboard/chart-data'],
            'list domain' => ['/domain'],
            'list opd' => ['/opd'],
            'pemetaan' => ['/pemetaan'],
        ];
    }

    #[DataProvider('halaman')]
    public function test_jumlah_query_konstan(string $url): void
    {
        $this->tambahData(2, 1);
        $sedikit = $this->jumlahQuery($url);

        $this->tambahData(5, 2);
        $banyak = $this->jumlahQuery($url);

        $this->assertSame($sedikit, $banyak, "Jumlah query {$url} bertambah: {$sedikit} -> {$banyak}");
    }
}

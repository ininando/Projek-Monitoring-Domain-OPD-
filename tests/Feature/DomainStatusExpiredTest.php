<?php

namespace Tests\Feature;

use App\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DomainStatusExpiredTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-15 14:30:00');
    }

    public static function kasusLabel(): array
    {
        return [
            'sudah lewat 5 hari' => [-5, 'Critical', 'danger', 'Expired 5 hari lalu'],
            'hari ini' => [0, 'Critical', 'danger', 'Expired hari ini'],
            'tepat 7 hari' => [7, 'Critical', 'danger', '7 hari lagi'],
            'tepat 8 hari' => [8, 'Warning', 'warning', '8 hari lagi'],
            'tepat 30 hari' => [30, 'Warning', 'warning', '30 hari lagi'],
            'tepat 31 hari' => [31, 'Normal', 'success', '31 hari lagi'],
        ];
    }

    #[DataProvider('kasusLabel')]
    public function test_label_expired(int $hari, string $label, string $warna, string $teks): void
    {
        $domain = Domain::factory()->expiredDalam($hari)->create()->fresh();

        $this->assertSame($hari, $domain->sisa_hari);
        $this->assertSame($label, $domain->label_expired);
        $this->assertSame($warna, $domain->warna_label);
        $this->assertSame($teks, $domain->teks_sisa_hari);
    }

    public function test_scope_akan_expired_dan_sudah_expired(): void
    {
        foreach ([-1, 0, 15, 30, 31] as $hari) {
            Domain::factory()->expiredDalam($hari)->create();
        }

        $this->assertSame(3, Domain::akanExpired()->count());
        $this->assertSame(2, Domain::akanExpired(15)->count());
        $this->assertSame(1, Domain::sudahExpired()->count());
    }
}

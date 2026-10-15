<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Menyusun data laporan domain dari parameter yang sudah tervalidasi.
 * Satu sumber untuk pratinjau web, PDF, dan Excel agar isinya selalu sama.
 */
class LaporanDomain
{
    public const JENIS = [
        'per_opd' => 'Laporan per OPD',
        'per_status' => 'Laporan per Status',
        'expired' => 'Laporan Domain Expired',
    ];

    public const KATEGORI_EXPIRED = [
        'semua' => 'Sudah & akan expired',
        'sudah' => 'Sudah expired',
        'akan' => 'Akan expired',
    ];

    public const PILIHAN_HARI = [7, 30, 60, 90];

    public readonly string $judul;

    /** Baris keterangan filter di bawah judul, misalnya "OPD: Dinas Kesehatan". */
    public readonly array $keteranganFilter;

    public readonly Carbon $dibuatPada;

    private Builder $query;

    public function __construct(private readonly array $params)
    {
        $this->dibuatPada = now();
        $this->query = Domain::query()->with('opd:id,nama_opd');

        [$this->judul, $this->keteranganFilter] = match ($params['jenis']) {
            'per_opd' => $this->perOpd(),
            'per_status' => $this->perStatus(),
            'expired' => $this->expired(),
        };
    }

    public function domains(): Collection
    {
        return (clone $this->query)
            ->orderBy('tanggal_expired')
            ->orderBy('nama_domain')
            ->get();
    }

    /** Nama file unduhan tanpa ekstensi. */
    public function namaFile(): string
    {
        return 'laporan-'.str_replace('_', '-', $this->params['jenis']).'-'.$this->dibuatPada->format('Ymd-His');
    }

    private function perOpd(): array
    {
        $opd = Opd::findOrFail($this->params['opd_id']);

        $this->query->filter([
            'opd_id' => $opd->id,
            'expired_dari' => $this->params['periode_dari'] ?? null,
            'expired_sampai' => $this->params['periode_sampai'] ?? null,
        ]);

        return ['Laporan Domain per OPD', [
            'OPD' => $opd->nama_opd,
            'Periode tanggal expired' => $this->teksPeriode(),
        ]];
    }

    private function perStatus(): array
    {
        $this->query->filter(['status' => $this->params['status']]);

        return ['Laporan Domain per Status', [
            'Status' => Domain::STATUS[$this->params['status']],
        ]];
    }

    private function expired(): array
    {
        $hari = (int) $this->params['dalam_hari'];
        $kategori = $this->params['kategori'];

        match ($kategori) {
            'sudah' => $this->query->sudahExpired(),
            'akan' => $this->query->akanExpired($hari),
            'semua' => $this->query->whereDate('tanggal_expired', '<=', today()->addDays($hari)),
        };

        $keterangan = ['Kategori' => self::KATEGORI_EXPIRED[$kategori]];
        if ($kategori !== 'sudah') {
            $keterangan['Batas'] = "{$hari} hari ke depan (s.d. ".today()->addDays($hari)->translatedFormat('d F Y').')';
        }

        return ['Laporan Domain Expired', $keterangan];
    }

    private function teksPeriode(): string
    {
        $dari = $this->params['periode_dari'] ?? null;
        $sampai = $this->params['periode_sampai'] ?? null;
        $fmt = fn (string $t) => Carbon::parse($t)->translatedFormat('d F Y');

        return match (true) {
            $dari && $sampai => $fmt($dari).' s.d. '.$fmt($sampai),
            (bool) $dari => 'Mulai '.$fmt($dari),
            (bool) $sampai => 'Sampai '.$fmt($sampai),
            default => 'Semua periode',
        };
    }
}

<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $daftarOpd = Opd::orderBy('id')->get();

        // Subdomain per OPD (urutan sama dengan OpdSeeder).
        $subdomain = [
            ['diskominfo', 'ppid', 'satudata'],
            ['disdikbud', 'ppdb', 'perpustakaan'],
            ['dinkes', 'puskesmas', 'sikda'],
            ['dpupr', 'simbg', 'tataruang'],
            ['disdukcapil', 'adminduk', 'antrian-capil'],
            ['pariwisata', 'tif', 'festivalbunga'],
            ['dpmptsp', 'perizinan', 'investasi'],
            ['bappeda', 'sipd', 'musrenbang'],
            ['bpkad', 'aset', 'pad'],
            ['setda', 'jdih', 'lpse'],
        ];

        // Selisih hari tanggal expired dari hari ini, agar semua kategori label terisi:
        // sudah lewat, <= 7 hari (Critical), 8-30 hari (Warning), > 30 hari (Normal).
        $selisihHari = [
            -60, -15, -3, 0, 2, 5, 7,
            8, 12, 18, 23, 27, 30,
            31, 45, 60, 75, 90, 120, 150, 180, 200, 240, 270, 300, 330, 365, 400, 500, 700,
        ];
        shuffle($selisihHari);

        $i = 0;
        foreach ($daftarOpd as $index => $opd) {
            foreach ($subdomain[$index] ?? [] as $nama) {
                Domain::factory()
                    ->expiredDalam($selisihHari[$i % count($selisihHari)])
                    ->create([
                        'opd_id' => $opd->id,
                        'nama_domain' => $nama.'.tomohon.go.id',
                        // Sebar tanggal registrasi agar grafik pertumbuhan per bulan bervariasi.
                        'tanggal_registrasi' => today()->subMonths(rand(1, 24))->subDays(rand(0, 27)),
                    ]);
                $i++;
            }
        }
    }
}

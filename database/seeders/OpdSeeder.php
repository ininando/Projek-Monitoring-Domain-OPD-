<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    public function run(): void
    {
        // Data contoh; alamat, kontak, dan nama kepala OPD bersifat dummy.
        $daftarOpd = [
            'Dinas Komunikasi dan Informatika',
            'Dinas Pendidikan dan Kebudayaan',
            'Dinas Kesehatan',
            'Dinas Pekerjaan Umum dan Penataan Ruang',
            'Dinas Kependudukan dan Pencatatan Sipil',
            'Dinas Pariwisata',
            'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu',
            'Badan Perencanaan Pembangunan Daerah',
            'Badan Pengelola Keuangan dan Aset Daerah',
            'Sekretariat Daerah',
        ];

        foreach ($daftarOpd as $nama) {
            Opd::factory()->create(['nama_opd' => $nama]);
        }
    }
}

<?php

namespace App\Exports;

use App\Models\Opd;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Rekap pemetaan: jumlah domain per OPD.
 */
class PemetaanExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function collection(): Enumerable
    {
        $opd = Opd::query()->withCount('domains')->orderByDesc('domains_count')->orderBy('nama_opd')->get();

        $baris = $opd->values()->map(fn (Opd $item, int $i) => [$i + 1, $item->nama_opd, $item->domains_count]);

        // Baris total di akhir tabel.
        return $baris->push(['', 'Total', $opd->sum('domains_count')]);
    }

    public function headings(): array
    {
        return ['No', 'Nama OPD', 'Jumlah Domain'];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
            $sheet->getHighestRow() => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Pemetaan Domain OPD';
    }
}

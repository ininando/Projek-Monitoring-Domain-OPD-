<?php

namespace App\Exports;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Daftar domain milik satu OPD.
 */
class PemetaanDetailExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $nomor = 0;

    public function __construct(private readonly Opd $opd) {}

    public function collection(): Enumerable
    {
        return $this->opd->domains()->orderBy('tanggal_expired')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Domain', 'Status', 'Tanggal Registrasi', 'Tanggal Expired', 'Sisa Hari', 'Label',
            'Registrar', 'IP Address', 'Hosting Provider', 'SSL', 'Keterangan',
        ];
    }

    /** @param Domain $row */
    public function map(mixed $row): array
    {
        return [
            ++$this->nomor,
            $row->nama_domain,
            $row->status_label,
            $row->tanggal_registrasi?->format('d/m/Y') ?? '-',
            $row->tanggal_expired->format('d/m/Y'),
            $row->teks_sisa_hari,
            $row->label_expired,
            $row->registrar ?? '-',
            $row->ip_address ?? '-',
            $row->hosting_provider ?? '-',
            Domain::SSL_STATUS[$row->ssl_status] ?? 'Belum diketahui',
            $row->keterangan ?? '',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        // Nama sheet Excel maksimal 31 karakter.
        return Str::limit($this->opd->nama_opd, 28, '...');
    }
}

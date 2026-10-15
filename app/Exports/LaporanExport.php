<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Laporan domain dalam Excel; isinya dirender dari view yang sama strukturnya dengan PDF.
 */
class LaporanExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $data) {}

    public function view(): View
    {
        return view('laporan.excel', $this->data);
    }

    public function title(): string
    {
        return 'Laporan';
    }
}

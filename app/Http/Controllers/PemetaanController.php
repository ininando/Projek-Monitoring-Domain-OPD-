<?php

namespace App\Http\Controllers;

use App\Exports\PemetaanDetailExport;
use App\Exports\PemetaanExport;
use App\Models\Opd;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PemetaanController extends Controller
{
    public function index(): View
    {
        $opd = Opd::query()
            ->withCount('domains')
            ->orderByDesc('domains_count')
            ->orderBy('nama_opd')
            ->get();

        $ringkasan = [
            'total_opd' => $opd->count(),
            'total_domain' => $opd->sum('domains_count'),
            'opd_tanpa_domain' => $opd->where('domains_count', 0)->count(),
            'maks' => max(1, (int) $opd->max('domains_count')),
        ];

        return view('pemetaan.index', compact('opd', 'ringkasan'));
    }

    public function show(Opd $opd): View
    {
        $domains = $opd->domains()->orderBy('tanggal_expired')->get();

        // Agregasi dari accessor model agar aturan label tidak diduplikasi di query.
        $chart = [
            'label' => [
                'labels' => ['Critical', 'Warning', 'Normal'],
                'data' => collect(['Critical', 'Warning', 'Normal'])
                    ->map(fn ($l) => $domains->where('label_expired', $l)->count())->all(),
            ],
            'status' => [
                'labels' => ['Aktif', 'Tidak Aktif'],
                'data' => [$domains->where('status', 'aktif')->count(), $domains->where('status', 'tidak_aktif')->count()],
            ],
            'sudah_expired' => $domains->where('sudah_expired', true)->count(),
        ];

        return view('pemetaan.show', compact('opd', 'domains', 'chart'));
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new PemetaanExport, 'pemetaan-domain-opd-'.today()->format('Ymd').'.xlsx');
    }

    public function exportDetail(Opd $opd): BinaryFileResponse
    {
        $nama = 'domain-'.Str::slug($opd->nama_opd).'-'.today()->format('Ymd').'.xlsx';

        return Excel::download(new PemetaanDetailExport($opd), $nama);
    }
}

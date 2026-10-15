<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Jumlah bulan yang ditampilkan pada grafik pertumbuhan. */
    private const BULAN_GRAFIK = 12;

    /** Jumlah baris maksimal tabel "perlu perhatian" di dashboard. */
    private const LIMIT_TABEL = 10;

    public function index(): View
    {
        $statistik = [
            'total' => Domain::count(),
            'aktif' => Domain::where('status', 'aktif')->count(),
            'tidak_aktif' => Domain::where('status', 'tidak_aktif')->count(),
            'akan_expired' => Domain::akanExpired(Domain::BATAS_WARNING)->count(),
            'sudah_expired' => Domain::sudahExpired()->count(),
        ];

        // Domain yang sudah lewat atau akan expired <= 30 hari, paling mendesak di atas.
        $perluPerhatian = Domain::query()
            ->with('opd:id,nama_opd')
            ->whereDate('tanggal_expired', '<=', today()->addDays(Domain::BATAS_WARNING))
            ->orderBy('tanggal_expired')
            ->limit(self::LIMIT_TABEL)
            ->get();

        $jumlahPerluPerhatian = $statistik['akan_expired'] + $statistik['sudah_expired'];

        return view('dashboard.index', compact('statistik', 'perluPerhatian', 'jumlahPerluPerhatian'));
    }

    /**
     * Data grafik dashboard (dipanggil via fetch dari halaman dashboard).
     */
    public function chartData(): JsonResponse
    {
        $perOpd = Opd::query()
            ->withCount('domains')
            ->orderByDesc('domains_count')
            ->orderBy('nama_opd')
            ->get(['id', 'nama_opd']);

        return response()->json([
            'per_opd' => [
                'labels' => $perOpd->pluck('nama_opd'),
                'data' => $perOpd->pluck('domains_count'),
            ],
            'pertumbuhan' => $this->pertumbuhanPerBulan(),
        ]);
    }

    /**
     * Domain baru dan total kumulatif per bulan, berdasarkan tanggal registrasi
     * (fallback ke tanggal input jika tanggal registrasi kosong).
     * Dihitung di PHP agar tidak bergantung pada fungsi tanggal khusus database.
     */
    private function pertumbuhanPerBulan(): array
    {
        $awal = today()->startOfMonth()->subMonths(self::BULAN_GRAFIK - 1);

        $tanggal = Domain::query()
            ->get(['tanggal_registrasi', 'created_at'])
            ->map(fn (Domain $d) => ($d->tanggal_registrasi ?? $d->created_at)->copy()->startOfMonth());

        // Domain yang terdaftar sebelum periode grafik menjadi titik awal kumulatif.
        $kumulatif = $tanggal->filter(fn (Carbon $t) => $t->lt($awal))->count();
        $perBulan = $tanggal->countBy(fn (Carbon $t) => $t->format('Y-m'));

        $labels = $baru = $total = [];
        for ($bulan = $awal->copy(); $bulan->lte(today()); $bulan->addMonth()) {
            $jumlah = $perBulan->get($bulan->format('Y-m'), 0);
            $kumulatif += $jumlah;

            $labels[] = $bulan->translatedFormat('M Y');
            $baru[] = $jumlah;
            $total[] = $kumulatif;
        }

        return ['labels' => $labels, 'baru' => $baru, 'total' => $total];
    }
}

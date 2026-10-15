<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Http\Requests\LaporanRequest;
use App\Models\Opd;
use App\Services\LaporanDomain;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
    /**
     * Form pemilihan laporan + pratinjau bila parameter sudah dipilih.
     */
    public function index(Request $request): View
    {
        $laporan = null;
        $domains = collect();
        $errors = null;

        if ($request->filled('jenis')) {
            // Validasi di halaman yang sama (tanpa redirect) agar pesan tampil di dekat field.
            $validator = Validator::make($request->query(), (new LaporanRequest)->rules(), (new LaporanRequest)->messages(), (new LaporanRequest)->attributes());

            if ($validator->fails()) {
                $errors = $validator->errors();
            } else {
                $laporan = new LaporanDomain($validator->validated());
                $domains = $laporan->domains();
            }
        }

        return view('laporan.index', [
            'daftarOpd' => Opd::orderBy('nama_opd')->pluck('nama_opd', 'id'),
            'laporan' => $laporan,
            'domains' => $domains,
            'input' => $request->query(),
        ])->withErrors($errors ?? []);
    }

    /**
     * Unduh laporan sebagai PDF atau Excel.
     */
    public function export(LaporanRequest $request): Response
    {
        $laporan = new LaporanDomain($request->validated());
        $domains = $laporan->domains();
        $data = ['laporan' => $laporan, 'domains' => $domains, 'logo' => $this->logoBase64()];

        if ($request->input('format') === 'excel') {
            return Excel::download(new LaporanExport($data), $laporan->namaFile().'.xlsx');
        }

        return Pdf::loadView('laporan.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isFontSubsettingEnabled', true) // hanya glyph yang dipakai → file jauh lebih kecil
            ->download($laporan->namaFile().'.pdf');
    }

    /**
     * Logo disematkan sebagai data URI agar dompdf tidak perlu akses file/remote.
     */
    private function logoBase64(): ?string
    {
        $path = public_path('images/logo-diskominfo.png');

        return is_file($path) ? 'data:image/png;base64,'.base64_encode(file_get_contents($path)) : null;
    }
}

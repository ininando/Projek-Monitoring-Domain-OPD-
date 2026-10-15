<?php

namespace App\Http\Controllers;

use App\Http\Requests\DomainRequest;
use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DomainController extends Controller
{
    private const FILTER_KEYS = ['search', 'opd_id', 'status', 'expired_dari', 'expired_sampai'];

    public function index(Request $request): View
    {
        // Filter tidak valid diabaikan dan pesannya ditampilkan di halaman yang sama (tanpa redirect).
        $validator = Validator::make($request->only(self::FILTER_KEYS), [
            'search' => ['nullable', 'string', 'max:100'],
            'opd_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(array_keys(Domain::STATUS))],
            'expired_dari' => ['nullable', 'date_format:Y-m-d'],
            'expired_sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:expired_dari'],
        ], [
            'expired_sampai.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ], [
            'expired_dari' => 'tanggal awal',
            'expired_sampai' => 'tanggal akhir',
        ]);
        $filters = $validator->valid();

        // Rentang tanggal hanya dipakai utuh; jika salah satu tidak valid, keduanya diabaikan.
        if ($validator->errors()->hasAny(['expired_dari', 'expired_sampai'])) {
            unset($filters['expired_dari'], $filters['expired_sampai']);
        }

        $domains = Domain::query()
            ->with('opd:id,nama_opd')
            ->filter($filters)
            ->orderBy('tanggal_expired')
            ->orderBy('nama_domain')
            ->paginate(10)
            ->withQueryString();

        return view('domain.index', [
            'domains' => $domains,
            'filters' => $filters,
            'input' => $request->only(self::FILTER_KEYS),
            'daftarOpd' => $this->daftarOpd(),
        ])->withErrors($validator);
    }

    public function create(): View
    {
        return view('domain.create', [
            'domain' => new Domain(['status' => 'aktif']),
            'daftarOpd' => $this->daftarOpd(),
        ]);
    }

    public function store(DomainRequest $request): RedirectResponse
    {
        $domain = Domain::create($request->validated());

        return redirect()->route('domain.show', $domain)->with('success', "Domain {$domain->nama_domain} berhasil ditambahkan.");
    }

    public function show(Domain $domain): View
    {
        $domain->load('opd');

        return view('domain.show', compact('domain'));
    }

    public function edit(Domain $domain): View
    {
        return view('domain.edit', [
            'domain' => $domain,
            'daftarOpd' => $this->daftarOpd(),
        ]);
    }

    public function update(DomainRequest $request, Domain $domain): RedirectResponse
    {
        $domain->update($request->validated());

        return redirect()->route('domain.show', $domain)->with('success', "Domain {$domain->nama_domain} berhasil diperbarui.");
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        $domain->delete();

        return redirect()->route('domain.index')->with('success', "Domain {$domain->nama_domain} berhasil dihapus.");
    }

    /**
     * Pilihan OPD untuk dropdown filter dan form.
     */
    private function daftarOpd()
    {
        return Opd::orderBy('nama_opd')->pluck('nama_opd', 'id');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpdRequest;
use App\Models\Opd;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $opd = Opd::query()
            ->withCount('domains')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_opd', 'like', "%{$search}%")
                        ->orWhere('kepala_opd', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_opd')
            ->paginate(10)
            ->withQueryString();

        return view('opd.index', compact('opd', 'search'));
    }

    public function create(): View
    {
        return view('opd.create', ['opd' => new Opd]);
    }

    public function store(OpdRequest $request): RedirectResponse
    {
        Opd::create($request->validated());

        return redirect()->route('opd.index')->with('success', 'Data OPD berhasil ditambahkan.');
    }

    public function edit(Opd $opd): View
    {
        return view('opd.edit', compact('opd'));
    }

    public function update(OpdRequest $request, Opd $opd): RedirectResponse
    {
        $opd->update($request->validated());

        return redirect()->route('opd.index')->with('success', 'Data OPD berhasil diperbarui.');
    }

    public function destroy(Opd $opd): RedirectResponse
    {
        $jumlahDomain = $opd->domains()->count();

        if ($jumlahDomain > 0) {
            return redirect()->route('opd.index')->with(
                'error',
                "OPD \"{$opd->nama_opd}\" tidak dapat dihapus karena masih memiliki {$jumlahDomain} domain. "
                .'Pindahkan atau hapus domain tersebut terlebih dahulu.'
            );
        }

        try {
            $opd->delete();
        } catch (QueryException) {
            // Pengaman jika domain ditambahkan bersamaan (FK restrict on delete).
            return redirect()->route('opd.index')->with('error', 'OPD tidak dapat dihapus karena masih memiliki domain.');
        }

        return redirect()->route('opd.index')->with('success', 'Data OPD berhasil dihapus.');
    }
}

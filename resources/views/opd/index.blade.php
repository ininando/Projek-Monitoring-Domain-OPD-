@extends('layouts.app')

@section('title', 'Data OPD')

@section('content')
    <x-page-header title="Data OPD"
                   description="Kelola daftar Organisasi Perangkat Daerah pemilik domain di lingkungan Pemerintah Kota Tomohon."
                   :breadcrumbs="['Beranda' => url('/'), 'Data OPD' => null]">
        <a href="{{ route('opd.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah OPD
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <form method="GET" action="{{ route('opd.index') }}" class="ds-search d-flex gap-2 flex-grow-1" role="search">
                <label for="search" class="visually-hidden">Cari OPD</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" id="search" name="search" value="{{ $search }}" class="form-control border-start-0 ps-0"
                           placeholder="Nama OPD atau kepala">
                </div>
                <button type="submit" class="btn btn-outline-primary">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('opd.index') }}" class="btn btn-light-ds">Reset</a>
                @endif
            </form>
            <span class="text-muted small">
                @if ($search !== '')
                    {{ $opd->total() }} hasil untuk &ldquo;{{ $search }}&rdquo;
                @else
                    Total {{ $opd->total() }} OPD
                @endif
            </span>
        </div>

        @if ($opd->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon" aria-hidden="true"><i class="bi {{ $search !== '' ? 'bi-search' : 'bi-building' }}"></i></div>
                @if ($search !== '')
                    <h2>OPD tidak ditemukan</h2>
                    <p class="text-muted mb-3">Tidak ada OPD yang cocok dengan &ldquo;{{ $search }}&rdquo;. Coba kata kunci lain.</p>
                    <a href="{{ route('opd.index') }}" class="btn btn-light-ds">Tampilkan semua OPD</a>
                @else
                    <h2>Belum ada data OPD</h2>
                    <p class="text-muted mb-3">Tambahkan OPD terlebih dahulu sebelum mendaftarkan domain.</p>
                    <a href="{{ route('opd.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah OPD</a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table ds-table ds-table-stack table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 64px">No</th>
                            <th scope="col">Nama OPD</th>
                            <th scope="col">Kepala OPD &amp; Kontak</th>
                            <th scope="col" class="text-center">Domain</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($opd as $item)
                            <tr>
                                <td class="text-center text-muted tabular-nums ds-stack-hide">{{ $opd->firstItem() + $loop->index }}</td>
                                <td class="ds-stack-title" style="min-width: 240px">
                                    <div class="ds-cell-title">{{ $item->nama_opd }}</div>
                                    @if ($item->alamat)
                                        <div class="ds-cell-sub">{{ $item->alamat }}</div>
                                    @endif
                                </td>
                                <td data-label="Kepala OPD" style="min-width: 160px">
                                    <div>
                                        <div>{{ $item->kepala_opd ?? '—' }}</div>
                                        @if ($item->kontak)
                                            <div class="ds-cell-sub tabular-nums"><i class="bi bi-telephone me-1" aria-hidden="true"></i>{{ $item->kontak }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center" data-label="Jumlah domain">
                                    <span class="ds-badge {{ $item->domains_count > 0 ? 'ds-badge-info' : 'ds-badge-neutral' }} tabular-nums">
                                        {{ $item->domains_count }} domain
                                    </span>
                                </td>
                                <td class="text-end text-nowrap ds-stack-actions">
                                    <a href="{{ route('opd.edit', $item) }}" class="btn btn-sm btn-light-ds" aria-label="Edit {{ $item->nama_opd }}">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i><span class="d-none d-xl-inline">Edit</span>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $item->nama_opd }}"
                                            data-bs-toggle="modal" data-bs-target="#modalHapus"
                                            data-action="{{ route('opd.destroy', $item) }}"
                                            data-nama="{{ $item->nama_opd }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i><span class="d-none d-xl-inline">Hapus</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted tabular-nums">
                    Menampilkan {{ $opd->firstItem() }}–{{ $opd->lastItem() }} dari {{ $opd->total() }} OPD
                </small>
                {{ $opd->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @include('partials.modal-hapus')
@endsection

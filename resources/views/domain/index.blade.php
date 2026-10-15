@extends('layouts.app')

@section('title', 'Data Domain')

@section('content')
    <x-page-header title="Data Domain"
                   description="Pantau masa berlaku dan status domain milik setiap OPD."
                   :breadcrumbs="['Beranda' => url('/'), 'Data Domain' => null]">
        <a href="{{ route('domain.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Domain
        </a>
    </x-page-header>

    @php
        $jumlahFilter = collect($filters)->filter(fn ($v) => filled($v))->count();
        $adaFilter = $jumlahFilter > 0;
        // Di HP filter terlipat; dibuka otomatis jika ada filter aktif atau error.
        $filterTerbuka = $adaFilter || $errors->any();
    @endphp

    <div class="card">
        {{-- Filter --}}
        <div class="card-header">
            <button class="btn btn-light-ds w-100 d-sm-none ds-filter-toggle justify-content-between" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterDomain"
                    aria-expanded="{{ $filterTerbuka ? 'true' : 'false' }}" aria-controls="filterDomain">
                <span><i class="bi bi-funnel" aria-hidden="true"></i> Filter &amp; Pencarian
                    @if ($adaFilter)
                        <span class="ds-badge ds-badge-info ms-1">{{ $jumlahFilter }} aktif</span>
                    @endif
                </span>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </button>
            <div class="collapse d-sm-block {{ $filterTerbuka ? 'show' : '' }}" id="filterDomain">
            <form method="GET" action="{{ route('domain.index') }}" class="row g-3 align-items-end pt-3 pt-sm-0" role="search" aria-label="Filter domain">
                <div class="col-12 col-xl-3">
                    <label for="search" class="form-label">Cari</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                        <input type="search" id="search" name="search" value="{{ $input['search'] ?? '' }}"
                               class="form-control border-start-0 ps-0" placeholder="Nama domain, registrar, hosting">
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="opd_id" class="form-label">OPD</label>
                    <select id="opd_id" name="opd_id" class="form-select">
                        <option value="">Semua OPD</option>
                        @foreach ($daftarOpd as $id => $nama)
                            <option value="{{ $id }}" @selected((string) ($input['opd_id'] ?? '') === (string) $id)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-xl-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Domain::STATUS as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(($input['status'] ?? '') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="col-md-8 col-xl-4">
                    <legend class="form-label fs-6 mb-2">Rentang tanggal expired</legend>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <label for="expired_dari" class="visually-hidden">Dari tanggal</label>
                        <input type="date" id="expired_dari" name="expired_dari" value="{{ $input['expired_dari'] ?? '' }}"
                               class="form-control @error('expired_dari') is-invalid @enderror">
                        <span class="text-muted text-center">s.d.</span>
                        <label for="expired_sampai" class="visually-hidden">Sampai tanggal</label>
                        <input type="date" id="expired_sampai" name="expired_sampai" value="{{ $input['expired_sampai'] ?? '' }}"
                               class="form-control @error('expired_sampai') is-invalid @enderror">
                    </div>
                    @error('expired_dari') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                    @error('expired_sampai') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </fieldset>
                <div class="col-md-4 col-xl-12 d-flex flex-column-reverse flex-sm-row gap-2 justify-content-xl-end">
                    @if ($adaFilter)
                        <a href="{{ route('domain.index') }}" class="btn btn-light-ds">Reset</a>
                    @endif
                    <button type="submit" class="btn btn-outline-primary flex-grow-1 flex-xl-grow-0">
                        <i class="bi bi-funnel" aria-hidden="true"></i> Terapkan Filter
                    </button>
                </div>
            </form>
            </div>
        </div>

        @if ($domains->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon" aria-hidden="true"><i class="bi {{ $adaFilter ? 'bi-search' : 'bi-globe' }}"></i></div>
                @if ($adaFilter)
                    <h2>Domain tidak ditemukan</h2>
                    <p class="text-muted mb-3">Tidak ada domain yang cocok dengan filter. Ubah atau reset filter.</p>
                    <a href="{{ route('domain.index') }}" class="btn btn-light-ds">Reset filter</a>
                @else
                    <h2>Belum ada data domain</h2>
                    <p class="text-muted mb-3">Daftarkan domain pertama milik OPD.</p>
                    <a href="{{ route('domain.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Domain</a>
                @endif
            </div>
        @else
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-2 border-bottom small text-muted">
                <span>{{ $domains->total() }} domain{{ $adaFilter ? ' sesuai filter' : '' }} · diurutkan dari yang paling mendesak</span>
                <span class="d-flex flex-wrap column-gap-3 row-gap-1">
                    <span><i class="bi bi-exclamation-octagon-fill" style="color: var(--ds-danger)" aria-hidden="true"></i> Critical ≤ 7 hari</span>
                    <span><i class="bi bi-exclamation-triangle-fill" style="color: var(--ds-warning)" aria-hidden="true"></i> Warning 8–30 hari</span>
                    <span><i class="bi bi-check-circle-fill" style="color: var(--ds-success)" aria-hidden="true"></i> Normal &gt; 30 hari</span>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table ds-table ds-table-stack table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center d-none d-xl-table-cell" style="width: 56px">No</th>
                            <th scope="col">Domain &amp; OPD</th>
                            <th scope="col">Status</th>
                            <th scope="col">Masa Berlaku</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($domains as $domain)
                            <tr>
                                <td class="text-center text-muted tabular-nums d-none d-xl-table-cell ds-stack-hide">{{ $domains->firstItem() + $loop->index }}</td>
                                <td class="ds-stack-title" style="min-width: 200px">
                                    <a href="{{ route('domain.show', $domain) }}" class="ds-cell-title text-decoration-none">{{ $domain->nama_domain }}</a>
                                    <div class="ds-cell-sub">{{ $domain->opd->nama_opd }}</div>
                                </td>
                                <td data-label="Status"><x-badge-status :status="$domain->status" /></td>
                                <td data-label="Masa berlaku">
                                    <div>
                                        <div class="tabular-nums mb-1">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</div>
                                        <x-badge-expired :domain="$domain" />
                                    </div>
                                </td>
                                <td class="text-end text-nowrap ds-stack-actions">
                                    <a href="{{ route('domain.edit', $domain) }}" class="btn btn-sm btn-light-ds" aria-label="Edit {{ $domain->nama_domain }}">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i><span class="d-none">Edit</span>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $domain->nama_domain }}"
                                            data-bs-toggle="modal" data-bs-target="#modalHapus"
                                            data-action="{{ route('domain.destroy', $domain) }}"
                                            data-nama="{{ $domain->nama_domain }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i><span class="d-none">Hapus</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted tabular-nums">
                    Menampilkan {{ $domains->firstItem() }}–{{ $domains->lastItem() }} dari {{ $domains->total() }} domain
                </small>
                {{ $domains->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @include('partials.modal-hapus')
@endsection

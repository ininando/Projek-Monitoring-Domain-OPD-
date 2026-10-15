@extends('layouts.app')

@section('title', 'Laporan')

@use('App\Services\LaporanDomain')
@php($jenisDipilih = $input['jenis'] ?? 'per_opd')

@section('content')
    <x-page-header title="Laporan"
                   description="Susun laporan domain lalu unduh sebagai PDF atau Excel."
                   :breadcrumbs="['Beranda' => url('/'), 'Laporan' => null]" />

    <div class="row g-4">
        {{-- Form parameter --}}
        <div class="col-xl-4">
            <form method="GET" action="{{ route('laporan.index') }}" class="card" id="formLaporan" novalidate>
                <div class="card-header">
                    <h2 class="card-title-ds">Parameter Laporan</h2>
                </div>
                <div class="card-body">
                    <fieldset class="mb-4">
                        <legend class="form-label fs-6">Jenis laporan</legend>
                        <div class="d-grid gap-2">
                            @foreach ([
                                'per_opd' => ['bi-building', 'Domain milik satu OPD, opsional dalam periode tertentu'],
                                'per_status' => ['bi-toggle-on', 'Domain berstatus aktif atau tidak aktif'],
                                'expired' => ['bi-hourglass-split', 'Domain yang sudah atau akan expired'],
                            ] as $nilai => [$ikon, $deskripsi])
                                <label class="ds-option" for="jenis_{{ $nilai }}">
                                    <input type="radio" class="form-check-input mt-1" name="jenis" id="jenis_{{ $nilai }}" value="{{ $nilai }}"
                                           @checked($jenisDipilih === $nilai)>
                                    <span>
                                        <span class="d-block fw-semibold"><i class="bi {{ $ikon }} me-1" aria-hidden="true"></i>{{ LaporanDomain::JENIS[$nilai] }}</span>
                                        <span class="d-block small text-muted">{{ $deskripsi }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                    </fieldset>

                    {{-- Field per jenis; yang tidak relevan disembunyikan oleh JS dan diabaikan server. --}}
                    <div data-jenis="per_opd">
                        <div class="mb-3">
                            <label for="opd_id" class="form-label">OPD <span class="required-mark" aria-hidden="true">*</span></label>
                            <select id="opd_id" name="opd_id" class="form-select @error('opd_id') is-invalid @enderror">
                                <option value="">Pilih OPD</option>
                                @foreach ($daftarOpd as $id => $nama)
                                    <option value="{{ $id }}" @selected((string) ($input['opd_id'] ?? '') === (string) $id)>{{ $nama }}</option>
                                @endforeach
                            </select>
                            @error('opd_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <fieldset class="mb-3">
                            <legend class="form-label fs-6 mb-1">Periode tanggal expired</legend>
                            <p class="form-text mt-0 mb-2">Kosongkan untuk semua periode.</p>
                            <div class="row g-2">
                                <div class="col-sm-6 col-xl-12 col-xxl-6">
                                    <label for="periode_dari" class="form-label small fw-normal mb-1">Dari</label>
                                    <input type="date" id="periode_dari" name="periode_dari" value="{{ $input['periode_dari'] ?? '' }}"
                                           class="form-control @error('periode_dari') is-invalid @enderror">
                                    @error('periode_dari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-sm-6 col-xl-12 col-xxl-6">
                                    <label for="periode_sampai" class="form-label small fw-normal mb-1">Sampai</label>
                                    <input type="date" id="periode_sampai" name="periode_sampai" value="{{ $input['periode_sampai'] ?? '' }}"
                                           class="form-control @error('periode_sampai') is-invalid @enderror">
                                    @error('periode_sampai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div data-jenis="per_status">
                        <fieldset class="mb-3">
                            <legend class="form-label fs-6">Status <span class="required-mark" aria-hidden="true">*</span></legend>
                            @foreach (\App\Models\Domain::STATUS as $nilai => $label)
                                <div class="form-check">
                                    <input class="form-check-input @error('status') is-invalid @enderror" type="radio" name="status" id="status_{{ $nilai }}"
                                           value="{{ $nilai }}" @checked(($input['status'] ?? 'aktif') === $nilai)>
                                    <label class="form-check-label" for="status_{{ $nilai }}">{{ $label }}</label>
                                </div>
                            @endforeach
                            @error('status') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                        </fieldset>
                    </div>

                    <div data-jenis="expired">
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori <span class="required-mark" aria-hidden="true">*</span></label>
                            <select id="kategori" name="kategori" class="form-select @error('kategori') is-invalid @enderror">
                                @foreach (LaporanDomain::KATEGORI_EXPIRED as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(($input['kategori'] ?? 'semua') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="dalam_hari" class="form-label">Akan expired dalam</label>
                            <select id="dalam_hari" name="dalam_hari" class="form-select @error('dalam_hari') is-invalid @enderror" aria-describedby="dalam_hari_help">
                                @foreach (LaporanDomain::PILIHAN_HARI as $hari)
                                    <option value="{{ $hari }}" @selected((int) ($input['dalam_hari'] ?? 30) === $hari)>{{ $hari }} hari ke depan</option>
                                @endforeach
                            </select>
                            <div id="dalam_hari_help" class="form-text">Tidak berpengaruh untuk kategori &ldquo;Sudah expired&rdquo;.</div>
                            @error('dalam_hari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-eye" aria-hidden="true"></i> Tampilkan Pratinjau
                    </button>
                </div>
            </form>
        </div>

        {{-- Pratinjau --}}
        <div class="col-xl-8">
            <div class="card">
                @if (! $laporan)
                    <div class="ds-empty">
                        <div class="ds-empty-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div>
                        <h2>Belum ada pratinjau</h2>
                        <p class="text-muted mb-0">Pilih jenis laporan dan parameternya, lalu klik &ldquo;Tampilkan Pratinjau&rdquo;.</p>
                    </div>
                @else
                    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-start">
                        <div>
                            <h2 class="card-title-ds">{{ $laporan->judul }}</h2>
                            <dl class="ds-report-meta mb-0">
                                @foreach ($laporan->keteranganFilter as $label => $nilai)
                                    <div><dt>{{ $label }}</dt><dd>{{ $nilai }}</dd></div>
                                @endforeach
                            </dl>
                        </div>
                        @php($paramUnduh = collect($input)->except('format')->all())
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('laporan.export', $paramUnduh + ['format' => 'pdf']) }}" class="btn btn-primary" data-unduh>
                                <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> <span data-teks>Unduh PDF</span>
                            </a>
                            <a href="{{ route('laporan.export', $paramUnduh + ['format' => 'excel']) }}" class="btn btn-light-ds" data-unduh>
                                <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> <span data-teks>Unduh Excel</span>
                            </a>
                        </div>
                    </div>

                    @if ($domains->isEmpty())
                        <div class="ds-empty">
                            <div class="ds-empty-icon" aria-hidden="true"><i class="bi bi-inbox"></i></div>
                            <h2>Tidak ada domain</h2>
                            <p class="text-muted mb-0">Tidak ada domain yang sesuai parameter. Laporan tetap bisa diunduh (berisi 0 domain).</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table ds-table ds-table-stack align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center d-none d-xxl-table-cell" style="width: 56px">No</th>
                                        <th scope="col">Nama Domain &amp; OPD</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Tanggal Expired</th>
                                        <th scope="col">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($domains as $domain)
                                        <tr>
                                            <td class="text-center text-muted tabular-nums d-none d-xxl-table-cell ds-stack-hide">{{ $loop->iteration }}</td>
                                            <td class="ds-stack-title" style="min-width: 180px">
                                                <a href="{{ route('domain.show', $domain) }}" class="ds-cell-title text-decoration-none">{{ $domain->nama_domain }}</a>
                                                <div class="ds-cell-sub">{{ $domain->opd->nama_opd }}</div>
                                            </td>
                                            <td data-label="Status"><x-badge-status :status="$domain->status" /></td>
                                            <td data-label="Tanggal expired" class="text-nowrap tabular-nums">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</td>
                                            <td data-label="Keterangan">
                                                <div>
                                                    <x-badge-expired :domain="$domain" class="ds-badge-wrap" />
                                                    @if ($domain->keterangan)
                                                        <div class="ds-cell-sub mt-1">{{ $domain->keterangan }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="card-footer d-flex flex-wrap justify-content-between gap-2 small text-muted">
                        <span class="fw-semibold text-body">Total: {{ $domains->count() }} domain</span>
                        <span>Data per {{ $laporan->dibuatPada->translatedFormat('d F Y, H:i') }} WITA</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            // Tampilkan hanya field untuk jenis laporan terpilih (progressive disclosure).
            const form = document.getElementById('formLaporan');
            function sinkron() {
                const jenis = form.querySelector('input[name="jenis"]:checked')?.value;
                form.querySelectorAll('[data-jenis]').forEach((grup) => {
                    const aktif = grup.dataset.jenis === jenis;
                    grup.hidden = ! aktif;
                    grup.querySelectorAll('input, select').forEach((el) => { el.disabled = ! aktif; });
                });
            }
            form.querySelectorAll('input[name="jenis"]').forEach((r) => r.addEventListener('change', sinkron));
            sinkron();

            // Umpan balik saat file sedang disiapkan server.
            document.querySelectorAll('[data-unduh]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const teks = btn.querySelector('[data-teks]');
                    const asli = teks.textContent;
                    teks.textContent = 'Menyiapkan…';
                    btn.classList.add('disabled');
                    btn.setAttribute('aria-disabled', 'true');
                    setTimeout(() => {
                        teks.textContent = asli;
                        btn.classList.remove('disabled');
                        btn.removeAttribute('aria-disabled');
                    }, 2500);
                });
            });
        })();
    </script>
@endpush

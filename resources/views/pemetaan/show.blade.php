@extends('layouts.app')

@section('title', 'Pemetaan · '.$opd->nama_opd)

@section('content')
    <x-page-header :title="$opd->nama_opd"
                   description="{{ $domains->count() }} domain terdaftar{{ $opd->kepala_opd ? ' · Kepala OPD: '.$opd->kepala_opd : '' }}"
                   :breadcrumbs="['Beranda' => url('/'), 'Pemetaan Domain' => route('pemetaan.index'), $opd->nama_opd => null]">
        @if ($domains->isNotEmpty())
            <a href="{{ route('pemetaan.export-detail', $opd) }}" class="btn btn-light-ds">
                <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Export Excel
            </a>
        @endif
    </x-page-header>

    @if ($domains->isEmpty())
        <div class="card">
            <div class="ds-empty">
                <div class="ds-empty-icon" aria-hidden="true"><i class="bi bi-globe"></i></div>
                <h2>OPD ini belum memiliki domain</h2>
                <p class="text-muted mb-3">Daftarkan domain dan pilih OPD ini sebagai pemilik.</p>
                <a href="{{ route('domain.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Domain</a>
            </div>
        </div>
    @else
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="card-title-ds">Masa Berlaku Domain</h2>
                            <p class="small text-muted mb-0">
                                Critical ≤ 7 hari{{ $chart['sudah_expired'] > 0 ? ' (termasuk '.$chart['sudah_expired'].' sudah expired)' : '' }} · Warning 8–30 hari · Normal &gt; 30 hari
                            </p>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-ds" data-unduh="chartLabel"
                                data-nama-file="masa-berlaku-{{ Str::slug($opd->nama_opd) }}.png" aria-label="Unduh grafik Masa Berlaku (PNG)">
                            <i class="bi bi-download" aria-hidden="true"></i><span class="d-none d-sm-inline">PNG</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="ds-chart" style="height: 280px">
                            <canvas id="chartLabel" role="img"
                                    aria-label="Grafik batang: {{ collect($chart['label']['labels'])->map(fn ($l, $i) => $l.' '.$chart['label']['data'][$i])->implode(', ') }}"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="card-title-ds">Status Domain</h2>
                            <p class="small text-muted mb-0">Perbandingan domain aktif dan tidak aktif.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-ds" data-unduh="chartStatus"
                                data-nama-file="status-{{ Str::slug($opd->nama_opd) }}.png" aria-label="Unduh grafik Status Domain (PNG)">
                            <i class="bi bi-download" aria-hidden="true"></i><span class="d-none d-sm-inline">PNG</span>
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="ds-chart flex-grow-1" style="height: 220px">
                            <canvas id="chartStatus" role="img"
                                    aria-label="Grafik lingkaran: Aktif {{ $chart['status']['data'][0] }}, Tidak Aktif {{ $chart['status']['data'][1] }}"></canvas>
                        </div>
                        {{-- Legenda + nilai langsung (warna tidak berdiri sendiri). --}}
                        <ul class="list-unstyled d-flex justify-content-center gap-4 mt-3 mb-0">
                            @foreach ($chart['status']['labels'] as $i => $label)
                                @php($total = array_sum($chart['status']['data']))
                                <li class="d-flex align-items-center gap-2">
                                    <span class="ds-legend-swatch" style="background: {{ $i === 0 ? '#16A34A' : '#64748B' }}" aria-hidden="true"></span>
                                    <span>{{ $label }} <strong class="tabular-nums">{{ $chart['status']['data'][$i] }}</strong>
                                        <span class="text-muted small">({{ $total ? round($chart['status']['data'][$i] / $total * 100) : 0 }}%)</span></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title-ds">Daftar Domain</h2>
                <p class="small text-muted mb-0">Diurutkan dari yang paling mendesak.</p>
            </div>
            <div class="table-responsive">
                <table class="table ds-table ds-table-stack table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 56px">No</th>
                            <th scope="col">Nama Domain</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal Expired</th>
                            <th scope="col">Sisa Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($domains as $domain)
                            <tr>
                                <td class="text-center text-muted tabular-nums ds-stack-hide">{{ $loop->iteration }}</td>
                                <td class="ds-stack-title">
                                    <a href="{{ route('domain.show', $domain) }}" class="ds-cell-title text-decoration-none">{{ $domain->nama_domain }}</a>
                                </td>
                                <td data-label="Status"><x-badge-status :status="$domain->status" /></td>
                                <td data-label="Tanggal expired" class="text-nowrap tabular-nums">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</td>
                                <td data-label="Sisa waktu"><x-badge-expired :domain="$domain" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@if ($domains->isNotEmpty())
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="{{ asset('js/charts.js') }}"></script>
        <script>
            (function () {
                const { WARNA, sumbuNilai, sumbuKategori, datasetBar, unduhPng } = window.DsChart;
                const data = {{ Js::from($chart) }};
                const charts = {};

                charts.chartLabel = new Chart(document.getElementById('chartLabel'), {
                    type: 'bar',
                    data: {
                        labels: data.label.labels,
                        datasets: [datasetBar(data.label.data, [WARNA.critical, WARNA.warning, WARNA.normal])],
                    },
                    options: {
                        scales: { y: sumbuNilai(), x: sumbuKategori() },
                        plugins: { tooltip: { callbacks: { label: (item) => item.raw + ' domain' } } },
                    },
                });

                charts.chartStatus = new Chart(document.getElementById('chartStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: data.status.labels,
                        datasets: [{
                            data: data.status.data,
                            backgroundColor: [WARNA.normal, WARNA.nonaktif],
                            borderColor: WARNA.surface,
                            borderWidth: 2,
                            hoverOffset: 4,
                        }],
                    },
                    options: {
                        cutout: '62%',
                        plugins: { tooltip: { callbacks: { label: (item) => item.label + ': ' + item.raw + ' domain' } } },
                    },
                });

                document.querySelectorAll('[data-unduh]').forEach((btn) => {
                    btn.addEventListener('click', () => unduhPng(charts[btn.dataset.unduh], btn.dataset.namaFile));
                });
            })();
        </script>
    @endpush
@endif

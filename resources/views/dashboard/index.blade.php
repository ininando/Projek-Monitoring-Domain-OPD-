@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard"
                   description="Ringkasan status dan masa berlaku domain OPD per {{ today()->translatedFormat('d F Y') }}." />

    {{-- Kartu statistik --}}
    @php
        $kartu = [
            ['label' => 'Total Domain', 'nilai' => $statistik['total'], 'ikon' => 'bi-globe', 'warna' => 'info',
             'url' => route('domain.index'), 'catatan' => 'Seluruh domain terdaftar'],
            ['label' => 'Domain Aktif', 'nilai' => $statistik['aktif'], 'ikon' => 'bi-check-circle', 'warna' => 'success',
             'url' => route('domain.index', ['status' => 'aktif']), 'catatan' => 'Status aktif'],
            ['label' => 'Domain Tidak Aktif', 'nilai' => $statistik['tidak_aktif'], 'ikon' => 'bi-dash-circle', 'warna' => 'neutral',
             'url' => route('domain.index', ['status' => 'tidak_aktif']), 'catatan' => 'Status tidak aktif'],
            ['label' => 'Akan Expired (30 hari)', 'nilai' => $statistik['akan_expired'], 'ikon' => 'bi-hourglass-split', 'warna' => 'warning',
             'url' => route('domain.index', ['expired_dari' => today()->toDateString(), 'expired_sampai' => today()->addDays(30)->toDateString()]),
             'catatan' => $statistik['sudah_expired'] > 0 ? "+ {$statistik['sudah_expired']} domain sudah expired" : 'Tidak ada yang sudah expired'],
        ];
    @endphp
    <div class="row g-3 g-lg-4 mb-4">
        @foreach ($kartu as $k)
            <div class="col-6 col-xl-3">
                <a href="{{ $k['url'] }}" class="card ds-stat h-100 text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="ds-stat-label">{{ $k['label'] }}</span>
                            <span class="ds-stat-icon ds-stat-icon-{{ $k['warna'] }}" aria-hidden="true"><i class="bi {{ $k['ikon'] }}"></i></span>
                        </div>
                        <div class="ds-stat-value">{{ $k['nilai'] }}</div>
                        <div class="ds-stat-note">{{ $k['catatan'] }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Tabel domain perlu perhatian --}}
    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h2 class="card-title-ds">Domain Expired &amp; Akan Expired</h2>
                <p class="small text-muted mb-0">Sudah lewat atau berakhir dalam 30 hari, diurutkan dari yang paling mendesak.</p>
            </div>
            @if ($jumlahPerluPerhatian > $perluPerhatian->count())
                <a href="{{ route('domain.index', ['expired_sampai' => today()->addDays(30)->toDateString()]) }}" class="btn btn-sm btn-light-ds">
                    Lihat semua ({{ $jumlahPerluPerhatian }}) <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            @endif
        </div>

        @if ($perluPerhatian->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
                <h2>Semua domain aman</h2>
                <p class="text-muted mb-0">Tidak ada domain yang expired atau akan expired dalam 30 hari.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table ds-table ds-table-stack table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Nama Domain &amp; OPD</th>
                            <th scope="col">Tanggal Expired</th>
                            <th scope="col">Sisa Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perluPerhatian as $domain)
                            <tr>
                                <td class="ds-stack-title" style="min-width: 200px">
                                    <a href="{{ route('domain.show', $domain) }}" class="ds-cell-title text-decoration-none">{{ $domain->nama_domain }}</a>
                                    <div class="ds-cell-sub">{{ $domain->opd->nama_opd }}</div>
                                </td>
                                <td data-label="Tanggal expired" class="text-nowrap tabular-nums">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</td>
                                <td data-label="Sisa hari"><x-badge-expired :domain="$domain" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Grafik --}}
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="card-title-ds">Domain per OPD</h2>
                        <p class="small text-muted mb-0">Jumlah domain yang dimiliki setiap OPD.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light-ds" data-unduh="chartOpd" data-nama-file="domain-per-opd.png" aria-label="Unduh grafik Domain per OPD (PNG)" disabled>
                        <i class="bi bi-download" aria-hidden="true"></i><span class="d-none d-sm-inline">PNG</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="ds-chart" id="wrapChartOpd" style="height: 360px">
                        <canvas id="chartOpd" role="img" aria-label="Grafik batang jumlah domain per OPD"></canvas>
                        <div class="ds-chart-status" data-status>Memuat grafik…</div>
                    </div>
                    <details class="ds-chart-table mt-3">
                        <summary>Tampilkan sebagai tabel</summary>
                        <table class="table table-sm mt-2 mb-0">
                            <thead><tr><th scope="col">OPD</th><th scope="col" class="text-end">Jumlah domain</th></tr></thead>
                            <tbody id="tabelOpd"></tbody>
                        </table>
                    </details>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="card-title-ds">Pertumbuhan Domain</h2>
                        <p class="small text-muted mb-0">Total domain terdaftar per bulan, 12 bulan terakhir.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light-ds" data-unduh="chartPertumbuhan" data-nama-file="pertumbuhan-domain.png" aria-label="Unduh grafik Pertumbuhan Domain (PNG)" disabled>
                        <i class="bi bi-download" aria-hidden="true"></i><span class="d-none d-sm-inline">PNG</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="ds-chart" style="height: 360px">
                        <canvas id="chartPertumbuhan" role="img" aria-label="Grafik garis total domain per bulan"></canvas>
                        <div class="ds-chart-status" data-status>Memuat grafik…</div>
                    </div>
                    <details class="ds-chart-table mt-3">
                        <summary>Tampilkan sebagai tabel</summary>
                        <table class="table table-sm mt-2 mb-0">
                            <thead><tr><th scope="col">Bulan</th><th scope="col" class="text-end">Domain baru</th><th scope="col" class="text-end">Total</th></tr></thead>
                            <tbody id="tabelPertumbuhan"></tbody>
                        </table>
                    </details>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/charts.js') }}"></script>
    <script>
        (function () {
            const { WARNA, bungkusLabel, sumbuNilai, sumbuKategori, datasetBar, unduhPng, isiTabel } = window.DsChart;
            const charts = {};

            function selesai(canvasId, pesan) {
                const status = document.getElementById(canvasId).parentElement.querySelector('[data-status]');
                if (pesan) { status.textContent = pesan; } else { status.remove(); }
            }

            fetch(@json(route('dashboard.chart')), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then((res) => { if (!res.ok) throw new Error(res.status); return res.json(); })
                .then((data) => {
                    // Bar horizontal: nama OPD panjang tetap terbaca.
                    const opd = data.per_opd;
                    // Tinggi baris mengikuti jumlah baris label terpanjang agar label tidak bertumpuk di HP.
                    const labelOpd = opd.labels.map((l) => bungkusLabel(l, window.innerWidth < 576 ? 18 : 26));
                    const barisMaks = Math.max(1, ...labelOpd.map((l) => l.length));
                    const tinggiBaris = Math.max(46, barisMaks * 16 + 14);
                    document.getElementById('wrapChartOpd').style.height = Math.max(240, opd.labels.length * tinggiBaris + 40) + 'px';
                    charts.chartOpd = new Chart(document.getElementById('chartOpd'), {
                        type: 'bar',
                        data: { labels: labelOpd, datasets: [datasetBar(opd.data, WARNA.primary)] },
                        options: {
                            indexAxis: 'y',
                            layout: { padding: { right: 24 } },
                            scales: { x: sumbuNilai(), y: sumbuKategori({ ticks: { color: WARNA.teks, autoSkip: false } }) },
                            plugins: { tooltip: { callbacks: {
                                title: (items) => opd.labels[items[0].dataIndex],
                                label: (item) => item.raw + ' domain',
                            } } },
                        },
                    });
                    isiTabel(document.getElementById('tabelOpd'), opd.labels.map((l, i) => [l, opd.data[i]]));
                    selesai('chartOpd');

                    // Garis: total kumulatif; tooltip juga menampilkan domain baru bulan itu.
                    const tumbuh = data.pertumbuhan;
                    charts.chartPertumbuhan = new Chart(document.getElementById('chartPertumbuhan'), {
                        type: 'line',
                        data: { labels: tumbuh.labels, datasets: [{
                            data: tumbuh.total,
                            borderColor: WARNA.primary,
                            backgroundColor: 'rgba(3, 105, 161, .10)',
                            fill: true,
                            borderWidth: 2,
                            tension: 0.25,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: WARNA.primary,
                            pointBorderColor: WARNA.surface,
                            pointBorderWidth: 2,
                        }] },
                        options: {
                            interaction: { mode: 'index', intersect: false },
                            scales: { y: sumbuNilai(), x: sumbuKategori({ ticks: { color: WARNA.teksMuted, maxRotation: 0, autoSkip: true, autoSkipPadding: 12 } }) },
                            plugins: { tooltip: { callbacks: {
                                label: (item) => 'Total: ' + item.raw + ' domain',
                                afterLabel: (item) => 'Baru: ' + tumbuh.baru[item.dataIndex] + ' domain',
                            } } },
                        },
                    });
                    isiTabel(document.getElementById('tabelPertumbuhan'), tumbuh.labels.map((l, i) => [l, tumbuh.baru[i], tumbuh.total[i]]));
                    selesai('chartPertumbuhan');

                    document.querySelectorAll('[data-unduh]').forEach((btn) => { btn.disabled = false; });
                })
                .catch(() => {
                    selesai('chartOpd', 'Grafik gagal dimuat. Muat ulang halaman.');
                    selesai('chartPertumbuhan', 'Grafik gagal dimuat. Muat ulang halaman.');
                });

            document.querySelectorAll('[data-unduh]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const chart = charts[btn.dataset.unduh];
                    if (chart) unduhPng(chart, btn.dataset.namaFile);
                });
            });
        })();
    </script>
@endpush

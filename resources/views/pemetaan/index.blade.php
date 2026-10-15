@extends('layouts.app')

@section('title', 'Pemetaan Domain')

@section('content')
    <x-page-header title="Pemetaan Domain"
                   description="Kepemilikan domain per OPD di lingkungan Pemerintah Kota Tomohon."
                   :breadcrumbs="['Beranda' => url('/'), 'Pemetaan Domain' => null]">
        <a href="{{ route('pemetaan.export') }}" class="btn btn-light-ds">
            <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Export Excel
        </a>
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card h-100"><div class="card-body py-3">
                <div class="ds-stat-label">Total OPD</div>
                <div class="ds-stat-value">{{ $ringkasan['total_opd'] }}</div>
            </div></div>
        </div>
        <div class="col-4">
            <div class="card h-100"><div class="card-body py-3">
                <div class="ds-stat-label">Total Domain</div>
                <div class="ds-stat-value">{{ $ringkasan['total_domain'] }}</div>
            </div></div>
        </div>
        <div class="col-4">
            <div class="card h-100"><div class="card-body py-3">
                <div class="ds-stat-label">OPD tanpa domain</div>
                <div class="ds-stat-value">{{ $ringkasan['opd_tanpa_domain'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title-ds">Domain per OPD</h2>
            <p class="small text-muted mb-0">Diurutkan dari OPD dengan domain terbanyak.</p>
        </div>

        @if ($opd->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></div>
                <h2>Belum ada data OPD</h2>
                <p class="text-muted mb-3">Tambahkan OPD dan domainnya untuk melihat pemetaan.</p>
                <a href="{{ route('opd.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah OPD</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table ds-table ds-table-stack table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 56px">No</th>
                            <th scope="col">Nama OPD</th>
                            <th scope="col" style="width: 40%">Jumlah Domain</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($opd as $item)
                            <tr>
                                <td class="text-center text-muted tabular-nums ds-stack-hide">{{ $loop->iteration }}</td>
                                <td class="ds-stack-title" style="min-width: 200px">
                                    <span class="ds-cell-title">{{ $item->nama_opd }}</span>
                                </td>
                                <td data-label="Jumlah domain">
                                    {{-- Bar kecil sebagai penanda visual; angkanya tetap ditulis. --}}
                                    <div class="d-flex align-items-center gap-2 ds-inline-bar-wrap">
                                        <span class="ds-inline-bar" aria-hidden="true">
                                            <span style="width: {{ round($item->domains_count / $ringkasan['maks'] * 100) }}%"></span>
                                        </span>
                                        <span class="fw-semibold tabular-nums text-nowrap">{{ $item->domains_count }} domain</span>
                                    </div>
                                </td>
                                <td class="text-end text-nowrap ds-stack-actions">
                                    <a href="{{ route('pemetaan.show', $item) }}" class="btn btn-sm btn-outline-primary" aria-label="Lihat detail domain {{ $item->nama_opd }}">
                                        Lihat Detail <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="ds-table-total">
                            <td class="ds-stack-hide"></td>
                            <th scope="row" class="ds-stack-title">Total</th>
                            <td data-label="Jumlah domain" class="fw-semibold tabular-nums">{{ $ringkasan['total_domain'] }} domain</td>
                            <td class="ds-stack-hide"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
@endsection

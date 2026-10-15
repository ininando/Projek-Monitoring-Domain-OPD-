@extends('layouts.app')

@section('title', $domain->nama_domain)

@section('content')
    <x-page-header :title="$domain->nama_domain"
                   :description="$domain->opd->nama_opd"
                   :breadcrumbs="['Beranda' => url('/'), 'Data Domain' => route('domain.index'), $domain->nama_domain => null]">
        <a href="{{ route('domain.edit', $domain) }}" class="btn btn-light-ds">
            <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
        </a>
        <button type="button" class="btn btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#modalHapus"
                data-action="{{ route('domain.destroy', $domain) }}"
                data-nama="{{ $domain->nama_domain }}">
            <i class="bi bi-trash" aria-hidden="true"></i> Hapus
        </button>
    </x-page-header>

    @php
        $sisa = $domain->sisa_hari;
        $nameservers = collect(preg_split('/[\s,]+/', (string) $domain->nameserver))->filter();
        $kosong = '<span class="text-muted">—</span>';
    @endphp

    <div class="row g-4">
        {{-- Ringkasan masa berlaku --}}
        <div class="col-lg-4">
            <div class="card h-100 ds-countdown ds-countdown-{{ $domain->warna_label }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: .05em">Masa berlaku</span>
                        <span class="ds-badge ds-badge-{{ $domain->warna_label }}">{{ $domain->label_expired }}</span>
                    </div>
                    <div class="ds-countdown-number tabular-nums">{{ abs($sisa) }}</div>
                    <div class="fw-semibold mb-3">
                        @if ($sisa < 0)
                            hari sejak expired
                        @elseif ($sisa === 0)
                            hari — expired hari ini
                        @else
                            hari lagi sebelum expired
                        @endif
                    </div>
                    <p class="mb-0 text-muted">
                        Tanggal expired<br>
                        <strong class="text-body">{{ $domain->tanggal_expired->translatedFormat('l, d F Y') }}</strong>
                    </p>
                </div>
            </div>
        </div>

        {{-- Informasi umum --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><h2 class="card-title-ds">Informasi Domain</h2></div>
                <div class="card-body">
                    <dl class="row ds-dl mb-0">
                        <dt class="col-sm-4">Nama Domain</dt>
                        <dd class="col-sm-8">
                            {{ $domain->nama_domain }}
                        </dd>

                        <dt class="col-sm-4">OPD Pemilik</dt>
                        <dd class="col-sm-8">{{ $domain->opd->nama_opd }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8"><x-badge-status :status="$domain->status" /></dd>

                        <dt class="col-sm-4">Tanggal Registrasi</dt>
                        <dd class="col-sm-8 tabular-nums">{!! $domain->tanggal_registrasi ? e($domain->tanggal_registrasi->translatedFormat('d F Y')) : $kosong !!}</dd>

                        <dt class="col-sm-4">Tanggal Expired</dt>
                        <dd class="col-sm-8 tabular-nums">{{ $domain->tanggal_expired->translatedFormat('d F Y') }}</dd>

                        <dt class="col-sm-4">Label</dt>
                        <dd class="col-sm-8"><x-badge-expired :domain="$domain" /></dd>

                        <dt class="col-sm-4">Keterangan</dt>
                        <dd class="col-sm-8" style="white-space: pre-line">{!! filled($domain->keterangan) ? e($domain->keterangan) : $kosong !!}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Informasi teknis --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2 class="card-title-ds">Informasi Teknis</h2></div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="ds-tech-label"><i class="bi bi-building-check" aria-hidden="true"></i> Registrar</div>
                            <div class="ds-tech-value">{!! filled($domain->registrar) ? e($domain->registrar) : $kosong !!}</div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="ds-tech-label"><i class="bi bi-hdd-network" aria-hidden="true"></i> IP Address</div>
                            <div class="ds-tech-value font-monospace">{!! filled($domain->ip_address) ? e($domain->ip_address) : $kosong !!}</div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="ds-tech-label"><i class="bi bi-server" aria-hidden="true"></i> Hosting Provider</div>
                            <div class="ds-tech-value">{!! filled($domain->hosting_provider) ? e($domain->hosting_provider) : $kosong !!}</div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="ds-tech-label"><i class="bi bi-shield-lock" aria-hidden="true"></i> SSL</div>
                            <div class="ds-tech-value">
                                <x-badge-status :status="$domain->ssl_status" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ds-tech-label"><i class="bi bi-diagram-2" aria-hidden="true"></i> Nameserver</div>
                            @if ($nameservers->isEmpty())
                                <div class="ds-tech-value">{!! $kosong !!}</div>
                            @else
                                <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                    @foreach ($nameservers as $ns)
                                        <li><code class="ds-code">{{ $ns }}</code></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer small text-muted">
                    Dibuat {{ $domain->created_at?->translatedFormat('d M Y H:i') }} · Terakhir diperbarui {{ $domain->updated_at?->translatedFormat('d M Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    @include('partials.modal-hapus')
@endsection

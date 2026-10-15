@extends('layouts.auth')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="ds-auth-card card text-center">
        <div class="card-body p-4 p-sm-5">
            <div class="ds-modal-icon mx-auto mb-3" aria-hidden="true"><i class="bi bi-shield-lock"></i></div>
            <p class="text-muted fw-semibold mb-1">Kode 403</p>
            <h1 class="h4 mb-2">Akses Ditolak</h1>
            <p class="text-muted mb-4">{{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses ke halaman ini.' }}</p>
            <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house" aria-hidden="true"></i> Kembali ke Beranda</a>
        </div>
    </div>
@endsection

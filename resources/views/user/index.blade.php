@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
    <x-page-header title="Manajemen User"
                   description="Kelola akun yang dapat masuk ke sistem. Halaman ini khusus Super Admin."
                   :breadcrumbs="['Beranda' => url('/'), 'Manajemen User' => null]">
        <a href="{{ route('user.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus" aria-hidden="true"></i> Tambah User
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <form method="GET" action="{{ route('user.index') }}" class="ds-search d-flex gap-2 flex-grow-1" role="search">
                <label for="search" class="visually-hidden">Cari user</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" id="search" name="search" value="{{ $search }}" class="form-control border-start-0 ps-0"
                           placeholder="Nama atau email">
                </div>
                <button type="submit" class="btn btn-outline-primary">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('user.index') }}" class="btn btn-light-ds">Reset</a>
                @endif
            </form>
            <span class="text-muted small">Total {{ $users->total() }} user</span>
        </div>

        @if ($users->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon" aria-hidden="true"><i class="bi bi-search"></i></div>
                <h2>User tidak ditemukan</h2>
                <p class="text-muted mb-3">Tidak ada user yang cocok dengan &ldquo;{{ $search }}&rdquo;.</p>
                <a href="{{ route('user.index') }}" class="btn btn-light-ds">Tampilkan semua user</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table ds-table ds-table-stack table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 56px">No</th>
                            <th scope="col">Nama &amp; Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Terdaftar</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $item)
                            @php($diriSendiri = $item->is(auth()->user()))
                            <tr>
                                <td class="text-center text-muted tabular-nums ds-stack-hide">{{ $users->firstItem() + $loop->index }}</td>
                                <td class="ds-stack-title">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="ds-user-avatar flex-shrink-0" aria-hidden="true">{{ strtoupper(mb_substr($item->name, 0, 1)) }}</span>
                                        <div class="text-start">
                                            <div class="ds-cell-title">
                                                {{ $item->name }}
                                                @if ($diriSendiri)<span class="ds-badge ds-badge-neutral ms-1">Anda</span>@endif
                                            </div>
                                            <div class="ds-cell-sub">{{ $item->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Role">
                                    <span class="ds-badge {{ $item->isSuperAdmin() ? 'ds-badge-info' : 'ds-badge-neutral' }}">
                                        <i class="bi {{ $item->isSuperAdmin() ? 'bi-shield-lock' : 'bi-person' }}" aria-hidden="true"></i>
                                        {{ $item->role_label }}
                                    </span>
                                </td>
                                <td data-label="Terdaftar" class="text-nowrap tabular-nums">{{ $item->created_at?->translatedFormat('d M Y') }}</td>
                                <td class="text-end text-nowrap ds-stack-actions">
                                    <a href="{{ route('user.edit', $item) }}" class="btn btn-sm btn-light-ds" aria-label="Edit {{ $item->name }}">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i><span class="d-none">Edit</span>
                                    </a>
                                    @if ($diriSendiri)
                                        {{-- Akun sendiri tidak bisa dihapus (juga ditolak di server). --}}
                                        <span class="d-inline-block ds-stack-btn-wrap" tabindex="0" data-bs-toggle="tooltip" title="Akun sendiri tidak dapat dihapus">
                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled aria-label="Hapus {{ $item->name }} (tidak tersedia untuk akun sendiri)">
                                                <i class="bi bi-trash" aria-hidden="true"></i><span class="d-none">Hapus</span>
                                            </button>
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $item->name }}"
                                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                data-action="{{ route('user.destroy', $item) }}"
                                                data-nama="{{ $item->name }} ({{ $item->email }})">
                                            <i class="bi bi-trash" aria-hidden="true"></i><span class="d-none">Hapus</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted tabular-nums">
                    Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
                </small>
                {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @include('partials.modal-hapus')
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
    </script>
@endpush

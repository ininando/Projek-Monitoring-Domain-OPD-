@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="status">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
        <div class="flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup pesan"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i>
        <div class="flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup pesan"></button>
    </div>
@endif

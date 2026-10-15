@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="ds-auth-card card">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                {{-- Ganti dengan logo resmi: <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Kota Tomohon" height="56"> --}}
                <span class="ds-brand-mark mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.75rem" aria-hidden="true"><i class="bi bi-globe2"></i></span>
                <h1 class="h4 mb-1">Monitoring Domain OPD</h1>
                <p class="text-muted mb-0">Dinas Komunikasi dan Informatika Kota Tomohon</p>
            </div>

            @include('partials.flash')

            @error('email')
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i>
                    <div>{{ $message }}</div>
                </div>
            @enderror

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="form-control @error('email') is-invalid @enderror">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="form-control border-end-0 @error('password') is-invalid @enderror">
                        <button type="button" class="btn btn-light-ds border-start-0" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-muted small mt-4 mb-0">&copy; {{ date('Y') }} Pemerintah Kota Tomohon</p>
@endsection

@push('scripts')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            this.setAttribute('aria-pressed', tampil);
            this.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
            this.querySelector('i').className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
@endpush

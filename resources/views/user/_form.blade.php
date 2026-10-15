{{-- Field form user, dipakai halaman tambah dan edit. --}}
@php($diriSendiri = $user->exists && $user->is(auth()->user()))

<div class="card-body">
    <x-error-summary />
    <p class="small text-muted mb-4">Kolom bertanda <span class="required-mark">*</span> wajib diisi.</p>

    <div class="row g-4">
        <div class="col-md-6">
            <label for="name" class="form-label">Nama <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" maxlength="100" required autofocus autocomplete="name"
                   value="{{ old('name', $user->name) }}"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="email" id="email" name="email" maxlength="255" required autocomplete="off" spellcheck="false"
                   value="{{ old('email', $user->email) }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <fieldset class="col-12">
            <legend class="form-label fs-6">Role <span class="required-mark" aria-hidden="true">*</span></legend>
            <div class="row g-2">
                @foreach ([
                    \App\Models\User::ROLE_ADMIN => 'Kelola OPD, Domain, Dashboard, Pemetaan, dan Laporan.',
                    \App\Models\User::ROLE_SUPER_ADMIN => 'Akses penuh, termasuk Manajemen User.',
                ] as $nilai => $deskripsi)
                    <div class="col-md-6">
                        <label class="ds-option h-100" for="role_{{ $nilai }}">
                            <input type="radio" class="form-check-input mt-1 @error('role') is-invalid @enderror" name="role" id="role_{{ $nilai }}"
                                   value="{{ $nilai }}" @checked(old('role', $user->role) === $nilai) @disabled($diriSendiri && $user->role !== $nilai)>
                            <span>
                                <span class="d-block fw-semibold">{{ \App\Models\User::ROLES[$nilai] }}</span>
                                <span class="d-block small text-muted">{{ $deskripsi }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
            @if ($diriSendiri)
                <div class="form-text">Role akun Anda sendiri tidak dapat diubah.</div>
            @endif
            @error('role') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
        </fieldset>

        <div class="col-md-6">
            <label for="password" class="form-label">
                Password @unless ($user->exists)<span class="required-mark" aria-hidden="true">*</span>@endunless
            </label>
            <input type="password" id="password" name="password" autocomplete="new-password" aria-describedby="password_help"
                   @unless ($user->exists) required @endunless
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="password_help" class="form-text">
                    Minimal 8 karakter, berisi huruf dan angka.{{ $user->exists ? ' Kosongkan jika tidak ingin mengganti.' : '' }}
                </div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                   class="form-control">
        </div>
    </div>
</div>

<div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
    <a href="{{ route('user.index') }}" class="btn btn-light-ds">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2" aria-hidden="true"></i> Simpan</button>
</div>

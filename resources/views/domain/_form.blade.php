{{-- Field form domain, dipakai halaman tambah dan edit. --}}
@php
    $tgl = fn ($field) => old($field, optional($domain->{$field})->format('Y-m-d'));
@endphp

<div class="card-body">
    <x-error-summary />
    <p class="small text-muted mb-4">Kolom bertanda <span class="required-mark">*</span> wajib diisi.</p>

    <h2 class="h6 text-uppercase text-muted mb-3" style="letter-spacing: .05em">Informasi Domain</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-7">
            <label for="nama_domain" class="form-label">Nama Domain <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="text" id="nama_domain" name="nama_domain" maxlength="255" required autofocus
                   value="{{ old('nama_domain', $domain->nama_domain) }}" inputmode="url" autocapitalize="off" spellcheck="false"
                   aria-describedby="nama_domain_help"
                   class="form-control @error('nama_domain') is-invalid @enderror">
            @error('nama_domain')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="nama_domain_help" class="form-text">Tanpa http://, misalnya diskominfo.tomohon.go.id</div>
            @enderror
        </div>

        <div class="col-md-5">
            <label for="opd_id" class="form-label">OPD Pemilik <span class="required-mark" aria-hidden="true">*</span></label>
            <select id="opd_id" name="opd_id" required class="form-select @error('opd_id') is-invalid @enderror">
                <option value="">Pilih OPD</option>
                @foreach ($daftarOpd as $id => $nama)
                    <option value="{{ $id }}" @selected((string) old('opd_id', $domain->opd_id) === (string) $id)>{{ $nama }}</option>
                @endforeach
            </select>
            @error('opd_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="tanggal_registrasi" class="form-label">Tanggal Registrasi</label>
            <input type="date" id="tanggal_registrasi" name="tanggal_registrasi" value="{{ $tgl('tanggal_registrasi') }}"
                   class="form-control @error('tanggal_registrasi') is-invalid @enderror">
            @error('tanggal_registrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="tanggal_expired" class="form-label">Tanggal Expired <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="date" id="tanggal_expired" name="tanggal_expired" required value="{{ $tgl('tanggal_expired') }}"
                   class="form-control @error('tanggal_expired') is-invalid @enderror">
            @error('tanggal_expired') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <fieldset class="col-md-4">
            <legend class="form-label fs-6">Status <span class="required-mark" aria-hidden="true">*</span></legend>
            <div class="d-flex gap-4 pt-2">
                @foreach (\App\Models\Domain::STATUS as $nilai => $label)
                    <div class="form-check">
                        <input class="form-check-input @error('status') is-invalid @enderror" type="radio" name="status"
                               id="status_{{ $nilai }}" value="{{ $nilai }}" @checked(old('status', $domain->status) === $nilai)>
                        <label class="form-check-label" for="status_{{ $nilai }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            @error('status') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
        </fieldset>
    </div>

    <h2 class="h6 text-uppercase text-muted mb-3" style="letter-spacing: .05em">Informasi Teknis</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <label for="registrar" class="form-label">Registrar</label>
            <input type="text" id="registrar" name="registrar" maxlength="100" value="{{ old('registrar', $domain->registrar) }}"
                   class="form-control @error('registrar') is-invalid @enderror">
            @error('registrar') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="hosting_provider" class="form-label">Hosting Provider</label>
            <input type="text" id="hosting_provider" name="hosting_provider" maxlength="100" value="{{ old('hosting_provider', $domain->hosting_provider) }}"
                   class="form-control @error('hosting_provider') is-invalid @enderror">
            @error('hosting_provider') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="ip_address" class="form-label">IP Address</label>
            <input type="text" id="ip_address" name="ip_address" maxlength="50" value="{{ old('ip_address', $domain->ip_address) }}"
                   inputmode="decimal" spellcheck="false" aria-describedby="ip_help"
                   class="form-control tabular-nums @error('ip_address') is-invalid @enderror">
            @error('ip_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="ip_help" class="form-text">IPv4 atau IPv6, misalnya 103.10.20.30</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="ssl_status" class="form-label">Status SSL</label>
            <select id="ssl_status" name="ssl_status" class="form-select @error('ssl_status') is-invalid @enderror">
                <option value="">Belum diketahui</option>
                @foreach (\App\Models\Domain::SSL_STATUS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('ssl_status', $domain->ssl_status) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            @error('ssl_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label for="nameserver" class="form-label">Nameserver</label>
            <textarea id="nameserver" name="nameserver" rows="2" spellcheck="false" aria-describedby="ns_help"
                      class="form-control @error('nameserver') is-invalid @enderror">{{ old('nameserver', $domain->nameserver) }}</textarea>
            @error('nameserver')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="ns_help" class="form-text">Pisahkan dengan koma atau baris baru.</div>
            @enderror
        </div>
    </div>

    <h2 class="h6 text-uppercase text-muted mb-3" style="letter-spacing: .05em">Lainnya</h2>
    <div>
        <label for="keterangan" class="form-label">Keterangan</label>
        <textarea id="keterangan" name="keterangan" rows="3"
                  class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $domain->keterangan) }}</textarea>
        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
    <a href="{{ $domain->exists ? route('domain.show', $domain) : route('domain.index') }}" class="btn btn-light-ds">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2" aria-hidden="true"></i> Simpan</button>
</div>

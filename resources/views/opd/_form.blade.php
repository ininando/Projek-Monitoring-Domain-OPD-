{{-- Field form OPD, dipakai halaman tambah dan edit. --}}
<div class="card-body">
    <x-error-summary />
    <p class="small text-muted mb-4">Kolom bertanda <span class="required-mark">*</span> wajib diisi.</p>

    <div class="row g-4">
        <div class="col-12">
            <label for="nama_opd" class="form-label">Nama OPD <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="text" id="nama_opd" name="nama_opd" maxlength="200" required autofocus
                   value="{{ old('nama_opd', $opd->nama_opd) }}"
                   aria-describedby="nama_opd_help"
                   class="form-control @error('nama_opd') is-invalid @enderror">
            @error('nama_opd')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="nama_opd_help" class="form-text">Tulis nama lengkap, misalnya &ldquo;Dinas Komunikasi dan Informatika&rdquo;.</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="kepala_opd" class="form-label">Kepala OPD</label>
            <input type="text" id="kepala_opd" name="kepala_opd" maxlength="100" autocomplete="name"
                   value="{{ old('kepala_opd', $opd->kepala_opd) }}"
                   class="form-control @error('kepala_opd') is-invalid @enderror">
            @error('kepala_opd') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="kontak" class="form-label">Kontak</label>
            <input type="text" id="kontak" name="kontak" maxlength="50"
                   value="{{ old('kontak', $opd->kontak) }}"
                   aria-describedby="kontak_help"
                   class="form-control @error('kontak') is-invalid @enderror">
            @error('kontak')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="kontak_help" class="form-text">Nomor telepon kantor atau alamat email resmi.</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3"
                      class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $opd->alamat) }}</textarea>
            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
    <a href="{{ route('opd.index') }}" class="btn btn-light-ds">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2" aria-hidden="true"></i> Simpan</button>
</div>

{{--
    Ringkasan error form: muncul bila validasi gagal, otomatis difokuskan,
    dan setiap butir menautkan ke field-nya. Pesan inline di bawah field tetap tampil.
    Pemakaian: <x-error-summary :labels="['nama_opd' => 'Nama OPD']" />
--}}
@props(['labels' => []])

@if ($errors->any())
    <div class="alert alert-danger ds-error-summary" role="alert" tabindex="-1" id="ringkasanError" aria-labelledby="ringkasanErrorJudul">
        <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i>
        <div>
            <p class="fw-semibold mb-1" id="ringkasanErrorJudul">
                {{ $errors->count() === 1 ? 'Ada 1 isian yang perlu diperbaiki:' : 'Ada '.$errors->count().' isian yang perlu diperbaiki:' }}
            </p>
            <ul class="mb-0 ps-3">
                @foreach ($errors->keys() as $field)
                    <li><a href="#{{ $field }}" class="alert-link fw-normal">{{ $errors->first($field) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
    @once
        @push('scripts')
            <script>
                (function () {
                    const ringkasan = document.getElementById('ringkasanError');
                    if (!ringkasan) return;
                    ringkasan.focus();
                    // Klik tautan → fokus ke field terkait (radio: pilihan pertama).
                    ringkasan.querySelectorAll('a[href^="#"]').forEach((a) => a.addEventListener('click', (e) => {
                        const nama = a.getAttribute('href').slice(1);
                        const field = document.getElementById(nama) || document.querySelector('[name="' + CSS.escape(nama) + '"]');
                        if (field) { e.preventDefault(); field.focus(); field.scrollIntoView({ block: 'center' }); }
                    }));
                })();
            </script>
        @endpush
    @endonce
@endif

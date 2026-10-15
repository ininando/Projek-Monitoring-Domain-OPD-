{{-- Badge status aktif/tidak aktif (domain atau SSL). Nilai null tampil sebagai "Belum diketahui". --}}
@props(['status', 'label' => null])

@php
    $teks = $label ?? ['aktif' => 'Aktif', 'tidak_aktif' => 'Tidak Aktif'][$status] ?? 'Belum diketahui';
    $kelas = match ($status) {
        'aktif' => 'ds-badge-success',
        'tidak_aktif' => 'ds-badge-neutral',
        default => 'ds-badge-neutral',
    };
@endphp

<span {{ $attributes->class(['ds-badge', $kelas]) }}>
    <i class="bi {{ $status === 'aktif' ? 'bi-circle-fill' : 'bi-circle' }}" style="font-size: .5rem" aria-hidden="true"></i>
    {{ $teks }}
</span>

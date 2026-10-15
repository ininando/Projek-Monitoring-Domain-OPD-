{{--
    Badge countdown expired. Semua logika (sisa hari, label, warna) berasal dari accessor model Domain.
    Pemakaian: <x-badge-expired :domain="$domain" />  ·  :with-label="false" untuk hanya countdown.
--}}
@props(['domain', 'withLabel' => true])

@php
    $ikon = ['Critical' => 'bi-exclamation-octagon-fill', 'Warning' => 'bi-exclamation-triangle-fill', 'Normal' => 'bi-check-circle-fill'][$domain->label_expired];
@endphp

<span {{ $attributes->class(['ds-badge', 'ds-badge-'.$domain->warna_label, 'tabular-nums']) }}>
    <i class="bi {{ $ikon }}" aria-hidden="true"></i>
    @if ($withLabel)
        <span>{{ $domain->label_expired }}</span><span aria-hidden="true">·</span>
    @endif
    <span>{{ $domain->teks_sisa_hari }}</span>
</span>

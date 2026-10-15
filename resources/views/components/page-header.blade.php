{{--
    Header halaman standar: breadcrumb, judul, deskripsi, dan slot tombol aksi.
    Contoh: <x-page-header title="Data OPD" :breadcrumbs="['Data OPD' => null]"> ...tombol... </x-page-header>
--}}
@props(['title', 'description' => null, 'breadcrumbs' => []])

<div class="ds-page-header">
    @if (! empty($breadcrumbs))
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @foreach ($breadcrumbs as $label => $url)
                    @if ($url && ! $loop->last)
                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-end">
        <div>
            <h1>{{ $title }}</h1>
            @if ($description)
                <p>{{ $description }}</p>
            @endif
        </div>
        @if ($slot->isNotEmpty())
            <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
        @endif
    </div>
</div>

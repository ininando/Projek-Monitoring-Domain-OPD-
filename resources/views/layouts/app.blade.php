<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@500;600&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <a href="#konten" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-primary" style="z-index: 1100">
        Lewati ke konten
    </a>

    {{-- Sidebar: tetap di desktop, offcanvas di tablet/ponsel --}}
    <aside class="ds-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Navigasi utama">
        <div class="offcanvas-body d-flex flex-column p-3 h-100">
            <div class="d-flex justify-content-between align-items-start px-1 pt-1 pb-3">
                <a href="{{ url('/') }}" class="ds-brand text-decoration-none">
                    {{-- Ganti dengan logo resmi: <img src="{{ asset('images/logo-diskominfo.png') }}"> --}}
                    <span class="ds-brand-mark" aria-hidden="true"><i class="bi bi-globe2"></i></span>
                    <span>
                        <span class="ds-brand-title d-block">Monitoring Domain OPD</span>
                        <span class="ds-brand-sub d-block">Diskominfo Kota Tomohon</span>
                    </span>
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none mt-2" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
            </div>

            @php
                // Menu hanya tampil jika route-nya sudah tersedia. Hak akses tetap ditegakkan oleh middleware.
                $menuGroups = [
                    'Menu Utama' => [
                        ['route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
                        ['route' => 'opd.index', 'pattern' => 'opd.*', 'icon' => 'bi-building', 'label' => 'Data OPD'],
                        ['route' => 'domain.index', 'pattern' => 'domain.*', 'icon' => 'bi-globe', 'label' => 'Data Domain'],
                        ['route' => 'pemetaan.index', 'pattern' => 'pemetaan.*', 'icon' => 'bi-diagram-3', 'label' => 'Pemetaan Domain'],
                        ['route' => 'laporan.index', 'pattern' => 'laporan.*', 'icon' => 'bi-file-earmark-text', 'label' => 'Laporan'],
                    ],
                    'Pengaturan' => [
                        ['route' => 'user.index', 'pattern' => 'user.*', 'icon' => 'bi-people', 'label' => 'Manajemen User', 'super_admin' => true],
                    ],
                ];
            @endphp

            <nav class="flex-grow-1">
                @foreach ($menuGroups as $grup => $items)
                    @php
                        $items = array_filter($items, fn ($item) => Route::has($item['route'])
                            && (empty($item['super_admin']) || auth()->user()?->isSuperAdmin()));
                    @endphp
                    @continue(empty($items))
                    <div class="ds-nav-label">{{ $grup }}</div>
                    <ul class="nav flex-column gap-1">
                        @foreach ($items as $item)
                            @php($aktif = request()->routeIs($item['pattern']))
                            <li class="nav-item">
                                <a href="{{ route($item['route']) }}" class="nav-link {{ $aktif ? 'active' : '' }}" @if ($aktif) aria-current="page" @endif>
                                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i> {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>

            <div class="ds-sidebar-footer pt-3 px-1">
                Pemerintah Kota Tomohon
            </div>
        </div>
    </aside>

    <div class="ds-main d-flex flex-column min-vh-100">
        <header class="ds-topbar d-flex align-items-center gap-2 px-3 px-md-4 sticky-top">
            <button class="btn btn-light-ds btn-icon d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                <i class="bi bi-list fs-5" aria-hidden="true"></i>
            </button>
            <span class="ds-heading fs-6 d-lg-none text-truncate">@yield('title', 'Dashboard')</span>
            <span class="d-none d-lg-inline text-muted small">Sistem Informasi Monitoring dan Pemetaan Domain OPD</span>

            @auth
                <div class="dropdown ms-auto">
                    <button class="btn btn-link text-decoration-none d-flex align-items-center gap-2 px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="ds-user-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-sm-block text-start lh-sm">
                            <span class="d-block fw-semibold text-body">{{ auth()->user()->name }}</span>
                            <span class="d-block small text-muted">{{ auth()->user()->role_label }}</span>
                        </span>
                        <i class="bi bi-chevron-down small text-muted" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="d-sm-none"><span class="dropdown-item-text small text-muted">{{ auth()->user()->role_label }}</span></li>
                        @if (Route::has('logout'))
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        @endif
                    </ul>
                </div>
            @endauth
        </header>

        <main id="konten" class="flex-grow-1 p-3 p-md-4" tabindex="-1">
            <div class="mx-auto" style="max-width: 1280px">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>

        <footer class="ds-footer text-center py-3 px-3">
            &copy; {{ date('Y') }} Dinas Komunikasi dan Informatika Kota Tomohon
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

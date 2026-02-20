<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FC Cogote Salado · Panel de Gestión</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">

    @vite(['resources/css/app.css', 'resources/css/admin/panel.css', 'resources/js/app.js', 'resources/js/admin/panel.js'])
</head>
<body class="admin-bg font-sans antialiased text-slate-100">
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-40 lg:hidden hidden" data-action="toggle-sidebar"></div>

<div class="flex min-h-screen">
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-72 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col admin-sidebar">
        <div class="flex items-center justify-between p-4 border-b border-white/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('storage/logo/logo_fccs_s_f.png') }}" alt="FC Cogote Salado" class="h-12 w-12 object-contain rounded-full bg-white/10 p-1" onerror="this.style.display='none'">
                <div>
                    <span class="text-lime-400 font-bold text-lg block leading-tight">FC Cogote</span>
                    <span class="text-amber-400 text-xs font-medium">Panel Admin</span>
                </div>
            </a>
            <button type="button" class="lg:hidden text-gray-400 hover:text-white p-1" data-action="toggle-sidebar">✕</button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="admin-nav-item {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">🏠 Dashboard</a>

            @php
                $isAdmin = Auth::user()?->isAdmin() ?? false;
                $menu = [
                    'plantel' => ['title' => '👥 Plantel', 'routes' => ['create' => '➕ Añadir Jugador', 'index' => '✏️ Editar / Eliminar Jugadores'], 'admin_only' => false],
                    'noticias' => ['title' => '📰 Noticias', 'routes' => ['create' => '➕ Crear Noticia', 'index' => '✏️ Editar / Eliminar Noticias'], 'admin_only' => false],
                    'avisos' => ['title' => '📢 Avisos', 'routes' => ['create' => '➕ Crear Aviso', 'index' => '✏️ Editar / Eliminar Avisos'], 'admin_only' => false],
                    'album' => ['title' => '📸 Álbum / Fotos', 'routes' => ['create' => '⬆️ Subir Fotos', 'index' => '🗑️ Eliminar Fotos'], 'admin_only' => false],
                    'directiva' => ['title' => '🏛️ Directiva', 'routes' => ['create' => '➕ Añadir Miembro', 'index' => '✏️ Editar / Eliminar Miembro'], 'admin_only' => true],
                    'partidos' => ['title' => '📅 Partidos', 'routes' => ['create' => '➕ Añadir Partido', 'index' => '✏️ Editar / Eliminar Partidos'], 'admin_only' => false],
                    'premios' => ['title' => '🏆 Premios', 'routes' => ['create' => '➕ Añadir Premio', 'index' => '✏️ Editar / Eliminar Premios'], 'admin_only' => false],
                    'temporadas' => ['title' => '⏳ Temporadas', 'routes' => ['create' => '➕ Crear Temporada', 'index' => '✏️ Editar / Eliminar Temporadas'], 'admin_only' => true],
                    'staff' => ['title' => '🤝 Ayudantes / Staff', 'routes' => ['create' => '➕ Añadir Staff', 'index' => '✏️ Editar / Eliminar Staff'], 'admin_only' => true],
                ];
            @endphp

            @foreach ($menu as $key => $item)
                @continue($item['admin_only'] && ! $isAdmin)
                <div class="accordion-group">
                    <button type="button" class="admin-accordion-button" data-accordion-trigger="{{ $key }}">
                        <span>{{ $item['title'] }}</span>
                        <span class="accordion-arrow" data-accordion-arrow="{{ $key }}" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none" class="accordion-arrow-icon">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                    <div class="accordion-content" data-accordion-content="{{ $key }}">
                        @foreach ($item['routes'] as $action => $label)
                            <a href="{{ route('admin.'.$key.'.'.$action) }}" class="admin-sub-link">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="border-t border-white/10 my-4"></div>

            @if($isAdmin)
                <a href="{{ route('admin.modificaciones.index') }}" class="admin-nav-item {{ request()->routeIs('admin.modificaciones.*') ? 'active-alt' : '' }}">
                    🧾 Historial de Cambios
                </a>
            @endif
        </nav>

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 text-sm">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
                <div class="min-w-0">
                    <p class="text-slate-200 font-medium truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@fccs.cl' }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="sticky top-0 z-30 admin-header border-b border-white/10">
            <div class="flex items-center justify-between px-4 lg:px-6 py-4">
                <div class="flex items-center gap-4">
                    <button type="button" class="lg:hidden text-gray-300 p-2 rounded-lg hover:bg-white/10" data-action="toggle-sidebar">☰</button>
                    <img src="{{ asset('storage/logo/logo_fccs_s_f.png') }}" alt="Logo" class="hidden sm:block h-8 w-8 rounded-full bg-white/10 p-1" onerror="this.style.display='none'">
                    <div>
                        <h1 class="text-lg lg:text-xl font-bold text-white">@yield('title', 'Dashboard')</h1>
                        <p class="text-xs text-gray-400 hidden sm:block">@yield('subtitle', 'Panel de Administración')</p>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" class="user-menu-trigger" data-action="toggle-user-menu">
                        <span class="avatar small">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                        <span class="hidden sm:inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <span class="user-menu-arrow" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none" class="user-menu-arrow-icon">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>

                    <div id="user-menu" class="user-menu hidden">
                        <a href="{{ route('fccs.home') }}" target="_blank" class="user-menu-item">🌐 Ver sitio</a>
                        <a href="{{ route('profile.edit') }}" class="user-menu-item">👤 Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="user-menu-item danger">🚪 Salir</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        <footer class="px-4 lg:px-6 py-4 border-t border-white/10 text-xs text-gray-500">
            © {{ date('Y') }} FC Cogote Salado.
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>

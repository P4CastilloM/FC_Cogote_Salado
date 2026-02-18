<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FC Cogote Salado') }} - Admin</title>

    @vite(['resources/css/app.css', 'resources/css/admin/panel.css', 'resources/js/app.js', 'resources/js/admin/panel.js'])
</head>
<body class="admin-bg font-sans antialiased text-slate-100">
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-40 lg:hidden hidden" data-action="toggle-sidebar"></div>

<div class="flex min-h-screen">
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-72 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col admin-sidebar">
        <div class="flex items-center justify-between p-4 border-b border-white/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('storage/logo/logo_fccs_s_f.png') }}" alt="FC Cogote Salado" class="h-12 w-auto" onerror="this.style.display='none'">
                <div>
                    <span class="text-lime-400 font-bold text-lg block leading-tight">FC Cogote</span>
                    <span class="text-amber-400 text-xs font-medium">Panel Admin</span>
                </div>
            </a>
            <button type="button" class="lg:hidden text-gray-400 hover:text-white p-1" data-action="toggle-sidebar">
                ✕
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="admin-nav-item {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

            @php
                $menu = [
                    'plantel' => ['title' => 'Plantel', 'routes' => ['create' => 'Añadir Jugador', 'index' => 'Editar / Eliminar Jugadores']],
                    'noticias' => ['title' => 'Noticias', 'routes' => ['create' => 'Crear Noticia', 'index' => 'Editar / Eliminar Noticias']],
                    'avisos' => ['title' => 'Avisos', 'routes' => ['create' => 'Crear Aviso', 'index' => 'Editar / Eliminar Avisos']],
                    'album' => ['title' => 'Álbum / Fotos', 'routes' => ['create' => 'Subir Fotos', 'index' => 'Eliminar Fotos']],
                    'directiva' => ['title' => 'Directiva', 'routes' => ['create' => 'Añadir Miembro', 'index' => 'Editar / Eliminar Miembro']],
                    'partidos' => ['title' => 'Partidos', 'routes' => ['create' => 'Añadir Partido', 'index' => 'Editar / Eliminar Partidos']],
                    'premios' => ['title' => 'Premios', 'routes' => ['create' => 'Añadir Premio', 'index' => 'Editar / Eliminar Premios']],
                    'temporadas' => ['title' => 'Temporadas', 'routes' => ['create' => 'Crear Temporada', 'index' => 'Editar / Eliminar Temporadas']],
                    'staff' => ['title' => 'Ayudantes / Staff', 'routes' => ['create' => 'Añadir Staff', 'index' => 'Editar / Eliminar Staff']],
                ];
            @endphp

            @foreach ($menu as $key => $item)
                <div class="accordion-group">
                    <button type="button" class="admin-accordion-button" data-accordion-trigger="{{ $key }}">
                        <span>{{ $item['title'] }}</span>
                        <span class="accordion-arrow" data-accordion-arrow="{{ $key }}">⌄</span>
                    </button>
                    <div class="accordion-content" data-accordion-content="{{ $key }}">
                        @foreach ($item['routes'] as $action => $label)
                            <a href="{{ route('admin.'.$key.'.'.$action) }}" class="admin-sub-link">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="border-t border-white/10 my-4"></div>

            <a href="{{ route('admin.modificaciones.index') }}" class="admin-nav-item {{ request()->routeIs('admin.modificaciones.*') ? 'active-alt' : '' }}">
                Historial de Cambios
            </a>
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
                    <div>
                        <h1 class="text-lg lg:text-xl font-bold text-white">@yield('title', 'Dashboard')</h1>
                        <p class="text-xs text-gray-400 hidden sm:block">@yield('subtitle', 'Panel de Administración')</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('fccs.home') }}" target="_blank" class="text-sm text-gray-300 hover:text-white">Ver sitio</a>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-300 hover:text-white">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-300 hover:text-red-200">Salir</button>
                    </form>
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

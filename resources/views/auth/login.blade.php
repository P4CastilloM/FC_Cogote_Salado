<!doctype html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login - FC Cogote Salado</title>
    @include('public.partials.seo-meta', [
        'seoTitle' => 'Login - FC Cogote Salado',
        'seoDescription' => 'Accede al panel de administración de FC Cogote Salado.',
        'seoUrl' => route('login'),
    ])

    @vite(['resources/css/app.css', 'resources/css/auth-login.css', 'resources/js/app.js', 'resources/js/auth-login.js'])
</head>
<body class="h-full login-page overflow-auto text-slate-100">
<div class="min-h-full w-full flex items-center justify-center p-4 sm:p-6 md:p-8 relative">
    <div class="glow-orb" style="top: 20%; left: 20%;"></div>
    <div class="glow-orb secondary" style="top: 70%; left: 80%;"></div>

    <div class="login-card w-full max-w-md rounded-3xl shadow-2xl p-6 sm:p-8 md:p-10 relative z-10">
        <div class="login-logo-wrap flex justify-center mb-6">
            <div class="relative">
                <div class="absolute inset-0 bg-club-gold/20 blur-2xl rounded-full"></div>
                <img
                    src="{{ asset('storage/logo/logo_fccs_s_f.png') }}"
                    alt="FC Cogote Salado Logo"
                    class="relative w-24 h-24 sm:w-28 sm:h-28 object-contain rounded-full border-2 border-club-gold/30 shadow-lg bg-white/10"
                    onerror="this.style.display='none'"
                >
            </div>
        </div>

        <h1 class="login-font-heading font-bold text-3xl text-center mb-2 shimmer-text">FC Cogote Salado</h1>
        <h2 class="login-font-heading font-semibold text-xl text-slate-300 text-center mb-1">Acceso Privado</h2>
        <p class="text-slate-400 text-sm text-center mb-7">Panel de Administración</p>

        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                <ul class="list-disc ms-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-slate-300 ml-1">Correo electrónico</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 ps-4 flex items-center text-slate-500">@</span>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="tu@email.com"
                        class="login-input w-full ps-12 pe-4 py-3.5 bg-black/20 border border-white/15 rounded-xl text-slate-100 placeholder-slate-400 focus:outline-none focus:border-club-gold focus:ring-2 focus:ring-club-gold/20"
                    >
                </div>
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-slate-300 ml-1">Contraseña</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 ps-4 flex items-center text-slate-500">•</span>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="login-input w-full ps-12 pe-12 py-3.5 bg-black/20 border border-white/15 rounded-xl text-slate-100 placeholder-slate-400 focus:outline-none focus:border-club-gold focus:ring-2 focus:ring-club-gold/20"
                    >
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pe-4 text-slate-400 hover:text-club-gold transition">
                        <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029M9.88 9.88l4.24 4.24M3 3l18 18"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded border-white/30 bg-black/20 text-club-gold focus:ring-club-gold/40">
                    Recordarme
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-club-gold hover:text-yellow-300 underline underline-offset-4">¿Olvidaste tu contraseña?</a>
                @endif
            </div>

            <button type="submit" class="login-submit w-full py-4 bg-gradient-to-r from-club-gold to-lime-500 text-club-dark font-semibold text-lg rounded-xl shadow-lg">
                <span id="submit-text">Iniciar sesión</span>
            </button>
        </form>

        <a href="{{ route('fccs.home') }}" class="mt-6 flex items-center justify-center w-full py-3 bg-white/5 border border-white/20 text-white font-medium rounded-xl hover:bg-white/10 transition-all">
            Volver al inicio
        </a>

        <p class="text-center text-slate-400 text-xs mt-6">© {{ date('Y') }} FC Cogote Salado · Área privada</p>
    </div>
</div>
</body>
</html>

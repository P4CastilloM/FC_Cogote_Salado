<header class="fixed top-0 left-0 right-0 z-50 bg-club-dark/95 backdrop-blur-sm border-b border-club-gold/20">
  <div class="max-w-7xl mx-auto px-4">
    {{-- ✅ ALTURA FIJA DEL HEADER (evita que el texto “salte” entre páginas) --}}
    <div class="h-16 flex items-center justify-between">

      {{-- LOGO + TEXTO --}}
      <div class="flex items-center gap-3 h-full">
        <div id="club-logo"
             class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center overflow-hidden border border-club-gold/30">
          <img src="{{ asset('storage/logo/logo_fccs_s_f.png') }}"
               alt="Logo FC Cogote Salado"
               class="w-full h-full object-cover">
        </div>

        {{-- ✅ TEXTO NORMALIZADO (mismo tamaño + mismo alto SIEMPRE) --}}
        <span class="font-bebas text-lg tracking-wider text-club-gold hidden sm:block leading-none">
          FC COGOTE SALADO
        </span>
      </div>

      {{-- NAV DESKTOP --}}
      <nav class="hidden lg:flex items-center gap-1 h-full">
        <a href="{{ route('fccs.home') }}#inicio"
           class="nav-link px-4 h-9 rounded-full text-sm font-semibold transition-all flex items-center
           {{ request()->routeIs('fccs.home') ? 'active' : '' }}">
          Inicio
        </a>

        <a href="{{ route('fccs.plantel') }}"
           class="nav-link px-4 h-9 rounded-full text-sm font-semibold transition-all flex items-center {{ request()->routeIs('fccs.plantel') ? 'active' : '' }}">
          Plantel
        </a>

        <a href="{{ route('fccs.calendario') }}"
           class="nav-link px-4 h-9 rounded-full text-sm font-semibold transition-all flex items-center {{ request()->routeIs('fccs.calendario') ? 'active' : '' }}">
          Calendario
        </a>

        <a href="{{ route('fccs.directiva') }}"
           class="nav-link px-4 h-9 rounded-full text-sm font-semibold transition-all flex items-center
           {{ request()->routeIs('fccs.directiva') ? 'active' : '' }}">
          Directiva
        </a>

        <a href="{{ route('fccs.noticias.index') }}"
           class="nav-link px-4 h-9 rounded-full text-sm font-semibold transition-all flex items-center
           {{ request()->routeIs('fccs.noticias.*') ? 'active' : '' }}">
          Noticias
        </a>

        <a href="{{ route('fccs.fotos') }}"
           class="nav-link px-4 h-9 rounded-full text-sm font-semibold transition-all flex items-center
           {{ request()->routeIs('fccs.fotos') ? 'active' : '' }}">
          Fotos
        </a>

      </nav>

      {{-- BOTÓN MENÚ MÓVIL --}}
      <button id="mobile-menu-btn"
              class="lg:hidden w-10 h-10 flex items-center justify-center rounded-full bg-club-gold/20 hover:bg-club-gold/30 transition-colors">
        <svg id="menu-icon" class="w-6 h-6 text-club-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg id="close-icon" class="w-6 h-6 text-club-gold hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- NAV MÓVIL --}}
    <nav id="mobile-menu" class="lg:hidden hidden pb-3">
      <div class="flex flex-col gap-1">
        <a href="{{ route('fccs.home') }}#inicio" class="nav-link-mobile px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('fccs.home') ? 'active' : '' }}">
          <span class="flex items-center gap-3"><span class="text-lg">🏠</span> Inicio</span>
        </a>
        <a href="{{ route('fccs.plantel') }}" class="nav-link-mobile px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('fccs.plantel') ? 'active' : '' }}">
          <span class="flex items-center gap-3"><span class="text-lg">👥</span> Plantel</span>
        </a>
        <a href="{{ route('fccs.calendario') }}" class="nav-link-mobile px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('fccs.calendario') ? 'active' : '' }}">
          <span class="flex items-center gap-3"><span class="text-lg">📅</span> Calendario</span>
        </a>
        <a href="{{ route('fccs.directiva') }}" class="nav-link-mobile px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('fccs.directiva') ? 'active' : '' }}">
          <span class="flex items-center gap-3"><span class="text-lg">🏛️</span> Directiva</span>
        </a>
        <a href="{{ route('fccs.noticias.index') }}" class="nav-link-mobile px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('fccs.noticias.*') ? 'active' : '' }}">
          <span class="flex items-center gap-3"><span class="text-lg">📰</span> Noticias</span>
        </a>

        <a href="{{ route('fccs.fotos') }}" class="nav-link-mobile px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('fccs.fotos') ? 'active' : '' }}">
          <span class="flex items-center gap-3"><span class="text-lg">📸</span> Fotos</span>
        </a>
      </div>
    </nav>
  </div>
</header>

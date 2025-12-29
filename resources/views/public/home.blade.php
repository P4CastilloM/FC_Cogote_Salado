{{-- =========================================================
   ✅ FC COGOTE SALADO - HOME (Blade + Vite)
   - Este archivo es un .blade.php
   - Rutas de imágenes: {{ asset('storage/...') }}
   - CSS/JS: @vite(['resources/css/public/home.css','resources/js/public/home.js'])

   📌 DÓNDE CAMBIAR COSAS:
   - Metas/preview WhatsApp: sección "METAS (OG/Twitter)"
   - Logo/Favicon: sección "ICONOS / LOGOS"
   - Textos principales: dentro del HTML (IDs: hero-title, hero-subtitle, etc.)
   - Colores Tailwind: en tailwind.config (si usas CDN)
========================================================= --}}

<!doctype html>
<html lang="es" class="h-full">
<head>
  {{-- =========================================================
     ✅ METAS BÁSICAS
  ========================================================== --}}
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>FC Cogote Salado</title>
  <meta name="description" content="FC Cogote Salado — Más que amigos, familia. Noticias, plantel, fotos y avisos del equipo.">

  {{-- =========================================================
     ✅ METAS (WhatsApp / Facebook / Open Graph)
     📌 Cambia el og:url si tu ruta final es distinta
     📌 La imagen preview es el logo que me indicaste
  ========================================================== --}}
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="FC Cogote Salado">
  <meta property="og:title" content="FC Cogote Salado">
  <meta property="og:description" content="Más que amigos, familia. Noticias, plantel, fotos y avisos del equipo.">
  <meta property="og:url" content="{{ url('/fccogotesalado') }}">
  <meta property="og:image" content="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <meta property="og:image:secure_url" content="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">

  {{-- =========================================================
     ✅ METAS (Twitter)
  ========================================================== --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="FC Cogote Salado">
  <meta name="twitter:description" content="Más que amigos, familia. Noticias, plantel, fotos y avisos del equipo.">
  <meta name="twitter:image" content="{{ asset('storage/fotos/logo_fccs_s_f.png') }}">

  {{-- =========================================================
     ✅ SEO
  ========================================================== --}}
  <link rel="canonical" href="{{ url('/fccogotesalado') }}">
  <meta name="theme-color" content="#34205C">

  {{-- =========================================================
     ✅ ICONOS / LOGOS
     📌 Favicon + ícono (usa tu logo)
  ========================================================== --}}
  <link rel="icon" type="image/png" href="{{ asset('storage/fotos/logo_fccs_s_f.png') }}">

  {{-- =========================================================
     ✅ FUENTES
  ========================================================== --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">


  {{-- =========================================================
     ✅ SDKs (los que ya estabas usando)
  ========================================================== --}}
  <script src="/_sdk/element_sdk.js"></script>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>

  {{-- =========================================================
     ✅ VITE (home.css + home.js)
     📌 Tus archivos están en:
     - resources/css/public/home.css
     - resources/js/public/home.js
  ========================================================== --}}
  @vite(['resources/css/app.css','resources/css/public/home.css', 'resources/js/public/home.js'])
</head>

<body class="h-full font-inter bg-club-dark text-white">
  <div id="app" class="h-full overflow-auto">

    @include('public.partials.header')

    {{-- =========================================================
       ✅ HERO SECTION (SLIDER)
    ========================================================== --}}
    <section id="inicio" class="relative h-screen min-h-[600px] overflow-hidden">

      {{-- Slides de fondo --}}
      <div id="hero-slides" class="absolute inset-0">
        <div class="hero-slide active">
          <div class="absolute inset-0 bg-gradient-to-br from-club-dark via-club-gray to-club-dark">
            <div class="absolute inset-0 opacity-30">
              <img
                src="{{ asset('storage/f_inicio/equipo1.jpeg') }}"
                alt="FC Cogote Salado - Slide 1"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

        <div class="hero-slide">
          <div class="absolute inset-0 bg-gradient-to-tr from-club-gray/80 via-club-dark to-club-gold/30">
            <div class="absolute inset-0 opacity-20">
              <img
                src="{{ asset('storage/f_inicio/equipo2.jpeg') }}"
                alt="FC Cogote Salado - Slide 2"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

        <div class="hero-slide">
          <div class="absolute inset-0 bg-gradient-to-bl from-club-gold/40 via-club-dark to-club-red/60">
            <div class="absolute inset-0 opacity-25">
              <img
                src="{{ asset('storage/f_inicio/equipo3.jpg') }}"
                alt="FC Cogote Salado - Slide 3"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>
      </div>

      {{-- Overlay oscuro --}}
      <div class="absolute inset-0 bg-black/40"></div>

      {{-- Contenido del hero --}}
      <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6 pt-20">

        {{-- Logo grande (hero) --}}
        <div class="mb-6">
          <div class="w-32 h-32 mx-auto rounded-full bg-gradient-to-br from-club-gold to-club-red p-1 shadow-2xl">
            <div class="w-full h-full rounded-full bg-club-dark flex items-center justify-center overflow-hidden">
              <img
                src="{{ asset('storage/logo/logo_fccs_s_f.png') }}"
                alt="Logo FC Cogote Salado"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

        <h1 id="hero-title" class="font-bebas text-5xl md:text-7xl lg:text-8xl tracking-wider mb-4 text-white drop-shadow-lg">
          MÁS QUE AMIGOS, <span class="text-club-gold">FAMILIA</span>
        </h1>

        <p id="hero-subtitle" class="text-lg md:text-xl text-gray-200 max-w-2xl mb-8">
          Unidos dentro y fuera de la cancha. Donde la pasión por el fútbol nos une.
        </p>

        {{-- Indicadores --}}
        <div class="flex gap-2 mb-8">
          <button class="carousel-dot active w-3 h-3 bg-club-gold rounded-full" data-slide="0"></button>
          <button class="carousel-dot w-3 h-3 bg-white/40 rounded-full" data-slide="1"></button>
          <button class="carousel-dot w-3 h-3 bg-white/40 rounded-full" data-slide="2"></button>
        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-8 scroll-indicator">
          <svg class="w-8 h-8 text-club-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
          </svg>
        </div>
      </div>
    </section>

    {{-- =========================================================
       ✅ AVISOS (CARRUSEL)
    ========================================================== --}}
    <section class="py-16 bg-gradient-to-b from-club-dark to-club-gray">
      <div class="max-w-7xl mx-auto px-4">

        <div class="flex items-center justify-between mb-8">
          <h2 id="avisos-title" class="font-bebas text-3xl md:text-4xl tracking-wider">
            <span class="text-club-gold">📢</span> AVISOS IMPORTANTES
          </h2>

          {{-- Controles PC --}}
          <div class="hidden md:flex gap-2">
            <button id="aviso-prev" class="w-10 h-10 rounded-full bg-club-gold/20 hover:bg-club-gold/40 flex items-center justify-center transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button id="aviso-next" class="w-10 h-10 rounded-full bg-club-gold/20 hover:bg-club-gold/40 flex items-center justify-center transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="overflow-hidden" id="avisos-container">
          <div id="avisos-carousel" class="aviso-carousel">

            {{-- Aviso 1 --}}
            <div class="aviso-card">
              <div class="bg-gradient-to-br from-club-red to-club-red/80 rounded-2xl p-6 h-full border border-club-gold/20">
                <div class="flex items-start gap-4">
                  <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">⚽</span>
                  </div>
                  <div>
                    <span class="text-xs text-club-gold font-semibold uppercase tracking-wider">Próximo Partido</span>
                    <h3 class="font-bebas text-xl mt-1 mb-2">ENTRENAMIENTO ESTE SÁBADO</h3>
                    <p class="text-gray-200 text-sm">Nos vemos en la cancha a las 10:00 AM. ¡No falten!</p>
                    <p class="text-club-gold text-xs mt-3 font-semibold">📍 Cancha Principal</p>
                  </div>
                </div>
              </div>
            </div>

            {{-- Aviso 2 --}}
            <div class="aviso-card">
              <div class="bg-gradient-to-br from-club-gold/80 to-club-gold/60 rounded-2xl p-6 h-full border border-white/20 text-club-dark">
                <div class="flex items-start gap-4">
                  <div class="w-12 h-12 rounded-full bg-club-dark/20 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">💰</span>
                  </div>
                  <div>
                    <span class="text-xs text-club-dark/70 font-semibold uppercase tracking-wider">Recordatorio</span>
                    <h3 class="font-bebas text-xl mt-1 mb-2">CUOTAS MENSUALES</h3>
                    <p class="text-club-dark/80 text-sm">Recuerda pagar tu cuota antes del día 15.</p>
                    <p class="text-club-dark text-xs mt-3 font-semibold">💳 Transferencia o Efectivo</p>
                  </div>
                </div>
              </div>
            </div>

            {{-- Aviso 3 --}}
            <div class="aviso-card">
              <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6 h-full border border-club-gold/20">
                <div class="flex items-start gap-4">
                  <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">🎉</span>
                  </div>
                  <div>
                    <span class="text-xs text-green-200 font-semibold uppercase tracking-wider">Evento</span>
                    <h3 class="font-bebas text-xl mt-1 mb-2">CARNE ASADA FIN DE MES</h3>
                    <p class="text-gray-200 text-sm">Celebramos los partidos ganados con una convivencia.</p>
                    <p class="text-green-200 text-xs mt-3 font-semibold">🗓️ Último sábado del mes</p>
                  </div>
                </div>
              </div>
            </div>

            {{-- Aviso 4 --}}
            <div class="aviso-card">
              <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-2xl p-6 h-full border border-club-gold/20">
                <div class="flex items-start gap-4">
                  <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">👕</span>
                  </div>
                  <div>
                    <span class="text-xs text-purple-200 font-semibold uppercase tracking-wider">Nuevo</span>
                    <h3 class="font-bebas text-xl mt-1 mb-2">UNIFORMES DISPONIBLES</h3>
                    <p class="text-gray-200 text-sm">Ya llegaron los nuevos uniformes de la temporada.</p>
                    <p class="text-purple-200 text-xs mt-3 font-semibold">📦 Recoger con el capitán</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- Dots móvil --}}
        <div id="aviso-dots" class="flex justify-center gap-2 mt-6 md:hidden">
          <button class="carousel-dot active w-2 h-2 bg-club-gold rounded-full" data-aviso="0"></button>
          <button class="carousel-dot w-2 h-2 bg-white/40 rounded-full" data-aviso="1"></button>
          <button class="carousel-dot w-2 h-2 bg-white/40 rounded-full" data-aviso="2"></button>
          <button class="carousel-dot w-2 h-2 bg-white/40 rounded-full" data-aviso="3"></button>
        </div>

      </div>
    </section>

    {{-- =========================================================
       ✅ NOTICIAS
    ========================================================== --}}
    <section id="noticias" class="py-16 bg-club-gray">
      <div class="max-w-7xl mx-auto px-4">
        <h2 id="noticias-title" class="font-bebas text-3xl md:text-4xl tracking-wider mb-8">
          <span class="text-club-gold">📰</span> ÚLTIMAS NOTICIAS
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

          {{-- Noticia 1 --}}
          <article class="news-card group cursor-pointer">
            <div class="relative rounded-2xl overflow-hidden bg-club-dark border border-club-gold/10 hover:border-club-gold/30 transition-all">
              <div class="h-48 bg-gradient-to-br from-club-red to-club-gold/50 relative overflow-hidden">
                <svg class="absolute inset-0 w-full h-full opacity-30" viewBox="0 0 100 100">
                  <circle cx="70" cy="30" r="40" fill="#fff" opacity="0.1" />
                  <circle cx="20" cy="80" r="30" fill="#d4af37" opacity="0.2" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">🏆</span></div>
                <div class="news-overlay absolute inset-0 bg-club-gold/80 flex items-center justify-center opacity-0 transition-opacity">
                  <span class="font-bebas text-xl text-club-dark">LEER MÁS →</span>
                </div>
              </div>
              <div class="p-5">
                <span class="text-xs text-club-gold font-semibold">VICTORIA • 15 ENE 2025</span>
                <h3 class="font-bebas text-xl mt-2 mb-2 group-hover:text-club-gold transition-colors">¡GANAMOS EL TORNEO RELÁMPAGO!</h3>
                <p class="text-gray-400 text-sm line-clamp-2">El equipo demostró su garra y se llevó el primer lugar del torneo local.</p>
              </div>
            </div>
          </article>

          {{-- Noticia 2 --}}
          <article class="news-card group cursor-pointer">
            <div class="relative rounded-2xl overflow-hidden bg-club-dark border border-club-gold/10 hover:border-club-gold/30 transition-all">
              <div class="h-48 bg-gradient-to-br from-blue-600 to-purple-600 relative overflow-hidden">
                <svg class="absolute inset-0 w-full h-full opacity-30" viewBox="0 0 100 100">
                  <rect x="10" y="10" width="30" height="30" fill="#fff" opacity="0.1" rx="5" />
                  <rect x="60" y="60" width="25" height="25" fill="#d4af37" opacity="0.2" rx="5" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">🤝</span></div>
                <div class="news-overlay absolute inset-0 bg-club-gold/80 flex items-center justify-center opacity-0 transition-opacity">
                  <span class="font-bebas text-xl text-club-dark">LEER MÁS →</span>
                </div>
              </div>
              <div class="p-5">
                <span class="text-xs text-club-gold font-semibold">FICHAJES • 10 ENE 2025</span>
                <h3 class="font-bebas text-xl mt-2 mb-2 group-hover:text-club-gold transition-colors">NUEVOS INTEGRANTES AL EQUIPO</h3>
                <p class="text-gray-400 text-sm line-clamp-2">Damos la bienvenida a 3 nuevos jugadores que se unen a la familia.</p>
              </div>
            </div>
          </article>

          {{-- Noticia 3 --}}
          <article class="news-card group cursor-pointer">
            <div class="relative rounded-2xl overflow-hidden bg-club-dark border border-club-gold/10 hover:border-club-gold/30 transition-all">
              <div class="h-48 bg-gradient-to-br from-green-600 to-teal-600 relative overflow-hidden">
                <svg class="absolute inset-0 w-full h-full opacity-30" viewBox="0 0 100 100">
                  <polygon points="50,10 90,90 10,90" fill="#fff" opacity="0.1" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">📸</span></div>
                <div class="news-overlay absolute inset-0 bg-club-gold/80 flex items-center justify-center opacity-0 transition-opacity">
                  <span class="font-bebas text-xl text-club-dark">LEER MÁS →</span>
                </div>
              </div>
              <div class="p-5">
                <span class="text-xs text-club-gold font-semibold">GALERÍA • 05 ENE 2025</span>
                <h3 class="font-bebas text-xl mt-2 mb-2 group-hover:text-club-gold transition-colors">FOTOS DEL ÚLTIMO PARTIDO</h3>
                <p class="text-gray-400 text-sm line-clamp-2">Revive los mejores momentos del partido contra Los Tigres.</p>
              </div>
            </div>
          </article>

        </div>
      </div>
    </section>

    {{-- =========================================================
       ✅ PLANTEL (DESTACADOS)
    ========================================================== --}}
    <section id="plantel" class="py-16 bg-gradient-to-b from-club-gray to-club-dark">
      <div class="max-w-7xl mx-auto px-4">
        <h2 id="destacados-title" class="font-bebas text-3xl md:text-4xl tracking-wider mb-8">
          <span class="text-club-gold">⭐</span> JUGADORES DESTACADOS
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">

          {{-- Jugador 1 --}}
          <div class="player-card group relative rounded-2xl overflow-hidden bg-gradient-to-b from-club-gold/20 to-club-dark border border-club-gold/20 hover:border-club-gold/50 transition-all">
            <div class="aspect-[3/4] relative">
              <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-club-dark/90"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-club-gold/30 flex items-center justify-center"><span class="text-4xl md:text-5xl">👨</span></div>
              </div>
              <div class="player-info absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                <div class="text-club-gold font-bebas text-3xl md:text-4xl">#10</div>
                <h3 class="font-bebas text-lg md:text-xl">CARLOS "EL CRACK"</h3>
                <p class="text-gray-400 text-xs md:text-sm">Mediocampista</p>
                <div class="flex gap-2 mt-2"><span class="text-xs bg-club-gold/20 px-2 py-1 rounded">⚽ 12 Goles</span></div>
              </div>
            </div>
          </div>

          {{-- Jugador 2 --}}
          <div class="player-card group relative rounded-2xl overflow-hidden bg-gradient-to-b from-club-red/20 to-club-dark border border-club-gold/20 hover:border-club-gold/50 transition-all">
            <div class="aspect-[3/4] relative">
              <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-club-dark/90"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-club-red/30 flex items-center justify-center"><span class="text-4xl md:text-5xl">🧔</span></div>
              </div>
              <div class="player-info absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                <div class="text-club-gold font-bebas text-3xl md:text-4xl">#1</div>
                <h3 class="font-bebas text-lg md:text-xl">MIGUEL "MURALLA"</h3>
                <p class="text-gray-400 text-xs md:text-sm">Portero</p>
                <div class="flex gap-2 mt-2"><span class="text-xs bg-club-gold/20 px-2 py-1 rounded">🧤 8 P. Imbatido</span></div>
              </div>
            </div>
          </div>

          {{-- Jugador 3 --}}
          <div class="player-card group relative rounded-2xl overflow-hidden bg-gradient-to-b from-green-600/20 to-club-dark border border-club-gold/20 hover:border-club-gold/50 transition-all">
            <div class="aspect-[3/4] relative">
              <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-club-dark/90"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-green-600/30 flex items-center justify-center"><span class="text-4xl md:text-5xl">👦</span></div>
              </div>
              <div class="player-info absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                <div class="text-club-gold font-bebas text-3xl md:text-4xl">#7</div>
                <h3 class="font-bebas text-lg md:text-xl">DAVID "RAYO"</h3>
                <p class="text-gray-400 text-xs md:text-sm">Extremo Derecho</p>
                <div class="flex gap-2 mt-2"><span class="text-xs bg-club-gold/20 px-2 py-1 rounded">🎯 15 Asistencias</span></div>
              </div>
            </div>
          </div>

          {{-- Jugador 4 --}}
          <div class="player-card group relative rounded-2xl overflow-hidden bg-gradient-to-b from-purple-600/20 to-club-dark border border-club-gold/20 hover:border-club-gold/50 transition-all">
            <div class="aspect-[3/4] relative">
              <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-club-dark/90"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-purple-600/30 flex items-center justify-center"><span class="text-4xl md:text-5xl">🧑</span></div>
              </div>
              <div class="player-info absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                <div class="text-club-gold font-bebas text-3xl md:text-4xl">#4</div>
                <h3 class="font-bebas text-lg md:text-xl">PEDRO "TANQUE"</h3>
                <p class="text-gray-400 text-xs md:text-sm">Defensa Central</p>
                <div class="flex gap-2 mt-2"><span class="text-xs bg-club-gold/20 px-2 py-1 rounded">🛡️ MVP Defensa</span></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    {{-- =========================================================
       ✅ DIRECTIVA
    ========================================================== --}}
    <section id="directiva" class="py-16 bg-club-dark">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="font-bebas text-3xl md:text-4xl tracking-wider mb-8"><span class="text-club-gold">🏛️</span> DIRECTIVA</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-gradient-to-br from-club-gold/20 to-club-dark rounded-2xl p-6 border border-club-gold/30 text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-club-gold/30 flex items-center justify-center mb-4"><span class="text-5xl">👔</span></div>
            <span class="text-club-gold text-xs font-semibold uppercase tracking-wider">Presidente</span>
            <h3 class="font-bebas text-2xl mt-1">JUAN PÉREZ</h3>
            <p class="text-gray-400 text-sm mt-2">Fundador del club y líder desde 2020</p>
          </div>

          <div class="bg-gradient-to-br from-club-red/20 to-club-dark rounded-2xl p-6 border border-club-gold/20 text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-club-red/30 flex items-center justify-center mb-4"><span class="text-5xl">⚽</span></div>
            <span class="text-club-gold text-xs font-semibold uppercase tracking-wider">Capitán</span>
            <h3 class="font-bebas text-2xl mt-1">CARLOS GARCÍA</h3>
            <p class="text-gray-400 text-sm mt-2">Lidera al equipo dentro de la cancha</p>
          </div>

          <div class="bg-gradient-to-br from-green-600/20 to-club-dark rounded-2xl p-6 border border-club-gold/20 text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-green-600/30 flex items-center justify-center mb-4"><span class="text-5xl">💰</span></div>
            <span class="text-club-gold text-xs font-semibold uppercase tracking-wider">Tesorero</span>
            <h3 class="font-bebas text-2xl mt-1">MIGUEL LÓPEZ</h3>
            <p class="text-gray-400 text-sm mt-2">Administra las finanzas del club</p>
          </div>
        </div>
      </div>
    </section>

    {{-- =========================================================
       ✅ GALERÍA
    ========================================================== --}}
    <section id="fotos" class="py-16 bg-club-gray">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="font-bebas text-3xl md:text-4xl tracking-wider mb-8"><span class="text-club-gold">📸</span> GALERÍA DE FOTOS</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-club-red to-club-gold/50 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">🏆</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">TORNEO 2024</span>
            </div>
          </div>

          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-blue-600 to-purple-600 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">👥</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">EQUIPO</span>
            </div>
          </div>

          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-green-600 to-teal-600 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">🎉</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">CELEBRACIÓN</span>
            </div>
          </div>

          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-orange-500 to-red-600 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">⚽</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">PARTIDO</span>
            </div>
          </div>

          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-pink-500 to-purple-600 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">🍖</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">CONVIVENCIA</span>
            </div>
          </div>

          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-yellow-500 to-orange-500 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">👕</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">UNIFORMES</span>
            </div>
          </div>

          <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-indigo-600 to-blue-600 relative group cursor-pointer">
            <div class="absolute inset-0 flex items-center justify-center"><span class="text-6xl">🥅</span></div>
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <span class="text-club-gold font-bebas text-lg">ENTRENO</span>
            </div>
          </div>

          {{-- Ver más --}}
          <a href="https://www.instagram.com/fc_cogote_salado?igsh=dmptcDF1M2x0YXp3" target="_blank" rel="noopener noreferrer"
             class="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 relative group cursor-pointer flex items-center justify-center">
            <div class="text-center">
              <svg class="w-10 h-10 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
              <span class="font-bebas text-lg">VER MÁS</span>
            </div>
          </a>

        </div>
      </div>
    </section>

    {{-- =========================================================
       ✅ CTA INSTAGRAM
    ========================================================== --}}
    <section class="py-20 bg-club-dark relative overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 400 200">
          <defs>
            <linearGradient id="igGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#833ab4" />
              <stop offset="50%" stop-color="#fd1d1d" />
              <stop offset="100%" stop-color="#fcb045" />
            </linearGradient>
          </defs>
          <circle cx="50" cy="100" r="80" fill="url(#igGrad)" opacity="0.5" />
          <circle cx="350" cy="100" r="100" fill="url(#igGrad)" opacity="0.3" />
          <circle cx="200" cy="50" r="60" fill="url(#igGrad)" opacity="0.4" />
        </svg>
      </div>

      <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <div class="mb-8">
          <div class="instagram-glow inline-block p-1 rounded-3xl bg-gradient-to-r from-purple-600 via-pink-500 to-orange-400">
            <div class="bg-club-dark rounded-3xl px-8 py-6">
              <svg class="w-16 h-16 mx-auto mb-4" fill="url(#instagramGradient)" viewBox="0 0 24 24">
                <defs>
                  <linearGradient id="instagramGradient" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#fcb045" />
                    <stop offset="50%" stop-color="#fd1d1d" />
                    <stop offset="100%" stop-color="#833ab4" />
                  </linearGradient>
                </defs>
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>

              <h2 class="font-bebas text-3xl md:text-5xl tracking-wider mb-2">
                ¡SÍGUENOS EN
                <span class="bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400 bg-clip-text text-transparent">INSTAGRAM</span>!
              </h2>

              <p class="text-gray-300 mb-6">@fc_cogote_salado</p>

              <a href="https://www.instagram.com/fc_cogote_salado?igsh=dmptcDF1M2x0YXp3" target="_blank" rel="noopener noreferrer"
                 class="inline-flex items-center gap-3 bg-gradient-to-r from-purple-600 via-pink-500 to-orange-400 text-white font-bold px-8 py-4 rounded-full text-lg hover:scale-105 transition-transform shadow-lg">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058 1.265-.07 1.644-.07 4.849 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                </svg>
                SEGUIR AHORA
              </a>
            </div>
          </div>
        </div>

        <p class="text-gray-400 text-sm">📷 Fotos • 🎥 Videos • 📊 Resultados • 🎉 Momentos</p>
      </div>
    </section>

    {{-- =========================================================
       ✅ FOOTER
    ========================================================== --}}
    <footer class="bg-club-dark border-t border-club-gold/20 py-8">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-4">

          <div class="w-10 h-10 rounded-full bg-white/10 border border-club-gold/30 overflow-hidden">
            <img src="{{ asset('storage/fotos/logo_fccs_s_f.png') }}" alt="Logo" class="w-full h-full object-cover">
          </div>

          <span class="font-bebas text-lg tracking-wider text-club-gold">FC COGOTE SALADO</span>
        </div>

        <p class="text-gray-500 text-sm">© 2025 FC Cogote Salado. Más que amigos, familia.</p>
      </div>
    </footer>

  </div>

  {{-- =========================================================
     ✅ IMPORTANTE
     No pongas <script> grande aquí porque el JS va en:
     resources/js/public/home.js (cargado con @vite)
  ========================================================== --}}
</body>
</html>

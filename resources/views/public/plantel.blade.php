<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plantel - FC Cogote Salado</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css', 'resources/js/public/home.js'])
  <style>
    .player-enter { animation: playerEnter .5s cubic-bezier(.34,1.56,.64,1) forwards; }
    @keyframes playerEnter { from { opacity: 0; transform: scale(.85) translateY(16px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .bench-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
    .bench-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,.05); border-radius: 999px; }
    .bench-scroll::-webkit-scrollbar-thumb { background: rgba(132,204,22,.4); border-radius: 999px; }
    .progress-bar { animation: progress 10s linear forwards; }
    @keyframes progress { from { width: 0%; } to { width: 100%; } }
    .field-pattern {
      background:
        linear-gradient(90deg, transparent 49.5%, rgba(255,255,255,.12) 49.5%, rgba(255,255,255,.12) 50.5%, transparent 50.5%),
        linear-gradient(transparent 49.5%, rgba(255,255,255,.12) 49.5%, rgba(255,255,255,.12) 50.5%, transparent 50.5%),
        radial-gradient(circle at center, transparent 18%, rgba(255,255,255,.12) 18%, rgba(255,255,255,.12) 19%, transparent 19%),
        linear-gradient(135deg, #2d5016 0%, #3d6b1e 50%, #2d5016 100%);
    }
    .glow-active { box-shadow: 0 0 20px rgba(132,204,22,.45), 0 0 40px rgba(132,204,22,.2); }
    .stat-card { backdrop-filter: blur(10px); }
  </style>
</head>
<body class="h-full bg-club-dark font-inter text-white overflow-auto">
  @include('public.partials.header')

  @php
    $featuredInit = ($jugadores ?? collect())->first();
    $initials = $featuredInit ? strtoupper(collect(explode(' ', trim((string) $featuredInit->display_name)))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('')) : 'JG';
  @endphp

  <main id="main-content" class="w-full pt-24">
    <section class="relative py-12 md:py-16 px-4 text-center overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-b from-club-gray/50 to-transparent"></div>
      <div class="relative z-10 max-w-4xl mx-auto">
        <span id="season-badge" class="inline-block px-4 py-1.5 bg-lime-500/20 text-lime-400 text-sm font-semibold rounded-full mb-4 border border-lime-500/30">Temporada {{ date('Y') }}</span>
        <h1 id="page-title" class="font-bebas text-5xl md:text-7xl tracking-wider mb-3 bg-gradient-to-r from-white via-amber-300 to-white bg-clip-text text-transparent">Plantel</h1>
        <p id="page-subtitle" class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">Conoce a quienes dejan todo por la camiseta</p>
      </div>
    </section>

    <section class="px-4 mb-6">
      <div class="max-w-6xl mx-auto">
        <div id="position-filters" class="flex flex-wrap justify-center gap-2"></div>
      </div>
    </section>

    <section class="px-4 pb-12">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
          <div class="lg:order-2 lg:flex-1">
            <div class="relative">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <span class="text-sm text-gray-300 font-medium">En Cancha</span>
                  <div class="flex items-center gap-2">
                    <button id="autoplay-toggle" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors" title="Pausar/Reanudar">
                      <svg id="pause-icon" class="w-4 h-4 text-lime-400" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                      <svg id="play-icon" class="w-4 h-4 text-lime-400 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <span id="autoplay-status" class="text-xs text-gray-400">Auto: ON</span>
                  </div>
                </div>
                <div class="flex-1 max-w-32 h-1 bg-white/10 rounded-full overflow-hidden ml-4">
                  <div id="progress-bar" class="h-full bg-lime-500 rounded-full progress-bar"></div>
                </div>
              </div>

              <div class="field-pattern rounded-2xl p-6 md:p-10 min-h-[420px] md:min-h-[520px] flex items-center justify-center relative overflow-hidden border border-white/10">
                <div class="absolute inset-4 border-2 border-white/20 rounded-lg pointer-events-none"></div>
                <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-white/15 pointer-events-none"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 md:w-32 md:h-32 border-2 border-white/15 rounded-full pointer-events-none"></div>

                <div id="featured-player" class="relative z-10 text-center w-full">
                  @if($featuredInit)
                    <div class="player-enter max-w-2xl mx-auto">
                      <div class="flex flex-col items-center text-center">
                        <div class="relative mb-5">
                          <div class="w-36 h-48 md:w-44 md:h-60 rounded-2xl overflow-hidden border-4 border-lime-400 bg-[#241337]/90 shadow-2xl">
                            @if(!empty($featuredInit->foto_url))
                              <img src="{{ $featuredInit->foto_url }}" alt="{{ $featuredInit->display_name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @endif
                            <div class="w-full h-full items-center justify-center @if(empty($featuredInit->foto_url)) flex @else hidden @endif">
                              <span class="font-bebas text-6xl text-white">{{ $initials }}</span>
                            </div>
                          </div>
                          <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-amber-400 text-[#241337] px-4 py-1 rounded-full font-bebas text-3xl">{{ $featuredInit->numero_camiseta ?? '-' }}</div>
                        </div>
                        <h2 class="font-bebas text-4xl md:text-5xl text-white leading-none">{{ $featuredInit->display_name }}</h2>
                        <span class="mt-2 inline-block px-4 py-1 rounded-full border border-amber-300/50 bg-amber-400/15 text-amber-300 font-semibold">{{ $featuredInit->posicion_label }}</span>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 max-w-lg w-full">
                          <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">⚽</span><span class="font-bebas text-2xl text-lime-400">{{ (int) ($featuredInit->goles ?? 0) }}</span><span class="text-xs text-gray-300 block">Goles</span></div>
                          <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">🎯</span><span class="font-bebas text-2xl text-amber-300">{{ (int) ($featuredInit->asistencia ?? 0) }}</span><span class="text-xs text-gray-300 block">Asistencias</span></div>
                          <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">🏟️</span><span class="font-bebas text-2xl text-white">{{ (int) ($featuredInit->partidos ?? 0) }}</span><span class="text-xs text-gray-300 block">Partidos</span></div>
                          <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">⭐</span><span class="font-bebas text-2xl text-purple-300">{{ number_format((float) ($featuredInit->rating ?? 0), 1) }}</span><span class="text-xs text-gray-300 block">Rating</span></div>
                        </div>
                      </div>
                    </div>
                  @else
                    <div class="text-center text-gray-200"><p class="font-bebas text-2xl">No hay jugadores cargados</p></div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="lg:order-1 lg:w-80 xl:w-96">
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-bebas text-2xl tracking-wider text-amber-300 flex items-center gap-2">👥 Banca</h2>
              <span id="player-count" class="text-sm text-gray-300"></span>
            </div>
            <div id="bench-container" class="flex lg:flex-col gap-3 overflow-x-auto lg:overflow-x-visible lg:overflow-y-auto lg:max-h-[520px] pb-4 lg:pb-0 lg:pr-2 bench-scroll snap-x lg:snap-none snap-mandatory"></div>
          </div>
        </div>
      </div>
    </section>

    <section class="px-4 pb-16">
      <div class="max-w-4xl mx-auto">
        <div class="bg-white/10 rounded-2xl p-6 md:p-8 border border-white/10">
          <h3 class="font-bebas text-2xl tracking-wider text-center mb-6 text-gray-200">Estadísticas del Plantel</h3>
          <div id="team-stats" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>
      </div>
    </section>
  </main>

  <script>
    const rawPlayers = @json($jugadores);
    const players = (rawPlayers || []).map((p) => ({
      id: p.rut,
      nombre: p.display_name || p.nombre || 'Jugador',
      numero: p.numero_camiseta || '-',
      posicion: p.posicion_label || 'Sin posición',
      goles: Number(p.goles || 0),
      asistencias: Number(p.asistencia || 0),
      partidos: Number(p.partidos || 0),
      rating: Number(p.rating || 0),
      foto: p.foto_url || null,
    }));

    const positions = ['Todos', ...new Set(players.map((p) => p.posicion))];
    const positionColors = {
      'Arquero': 'bg-yellow-500/20 text-yellow-300 border-yellow-500/40',
      'Defensa': 'bg-blue-500/20 text-blue-300 border-blue-500/40',
      'Mediocampista': 'bg-purple-500/20 text-purple-300 border-purple-500/40',
      'Delantero': 'bg-red-500/20 text-red-300 border-red-500/40',
    };

    let activePlayerIndex = 0;
    let currentFilter = 'Todos';
    let filteredPlayers = [...players];
    let autoplayInterval = null;
    let autoplayPaused = false;

    function getInitials(name) {
      return String(name).trim().split(/\s+/).filter(Boolean).map((n) => n[0]).join('').substring(0, 2).toUpperCase();
    }

    function getPositionStyle(pos) {
      return positionColors[pos] || 'bg-gray-500/20 text-gray-300 border-gray-500/40';
    }

    function renderFilters() {
      const container = document.getElementById('position-filters');
      if (!container) return;
      container.innerHTML = positions.map((pos) => `
        <button onclick="filterByPosition('${pos}')" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 border ${
          currentFilter === pos
            ? 'bg-lime-500 text-[#241337] border-lime-500'
            : 'bg-white/10 text-gray-100 border-white/10 hover:bg-white/20 hover:border-lime-500/50'
        }">${pos}</button>
      `).join('');
    }

    function renderBench() {
      const container = document.getElementById('bench-container');
      const playerCount = document.getElementById('player-count');
      if (!container || !playerCount) return;

      playerCount.textContent = `${filteredPlayers.length} jugadores`;
      container.innerHTML = filteredPlayers.map((player, index) => `
        <button onclick="setActivePlayer(${index})" class="flex-shrink-0 w-64 lg:w-full snap-start bg-white/10 hover:bg-white/20 rounded-xl p-4 border transition-all duration-300 cursor-pointer group ${
          index === activePlayerIndex ? 'border-lime-500 glow-active' : 'border-white/10 hover:border-lime-500/50'
        }">
          <div class="flex items-center gap-4">
            <div class="relative">
              <div class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-br from-[#2b1b45] to-[#241337] border-2 ${
                index === activePlayerIndex ? 'border-lime-500' : 'border-white/20'
              } flex items-center justify-center">
                ${player.foto
                  ? `<img src="${player.foto}" alt="${player.nombre}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><span class="hidden font-bebas text-lg text-white">${getInitials(player.nombre)}</span>`
                  : `<span class="font-bebas text-lg text-white">${getInitials(player.nombre)}</span>`}
              </div>
              <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-400 text-[#241337] text-xs font-bold flex items-center justify-center">${player.numero}</span>
            </div>
            <div class="flex-1 text-left min-w-0">
              <h3 class="font-bebas text-2xl leading-none truncate ${index === activePlayerIndex ? 'text-lime-400' : 'text-white'}">${player.nombre}</h3>
              <span class="inline-block px-2 py-0.5 text-xs rounded-full border ${getPositionStyle(player.posicion)} mt-1">${player.posicion}</span>
            </div>
            <div class="flex flex-col items-end gap-1 text-xs"><span class="text-gray-300">⚽ ${player.goles}</span><span class="text-gray-300">🎯 ${player.asistencias}</span></div>
          </div>
        </button>
      `).join('');
    }

    function renderFeaturedPlayer() {
      const container = document.getElementById('featured-player');
      if (!container) return;
      const player = filteredPlayers[activePlayerIndex];

      if (!player) {
        container.innerHTML = `<div class="text-center text-gray-200"><p class="font-bebas text-2xl">No hay jugadores</p><p class="text-sm mt-2">Selecciona otra posición</p></div>`;
        return;
      }

      const initials = getInitials(player.nombre);
      container.innerHTML = `
        <div class="player-enter max-w-2xl mx-auto">
          <div class="flex flex-col items-center text-center">
            <div class="relative mb-5">
              <div class="w-36 h-48 md:w-44 md:h-60 rounded-2xl overflow-hidden border-4 border-lime-400 bg-[#241337]/90 shadow-2xl">
                ${player.foto
                  ? `<img src="${player.foto}" alt="${player.nombre}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
                  : ''}
                <div class="w-full h-full items-center justify-center ${player.foto ? 'hidden' : 'flex'}"><span class="font-bebas text-6xl text-white">${initials}</span></div>
              </div>
              <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-amber-400 text-[#241337] px-4 py-1 rounded-full font-bebas text-3xl">${player.numero}</div>
            </div>

            <h2 class="font-bebas text-4xl md:text-5xl text-white leading-none">${player.nombre}</h2>
            <span class="mt-2 inline-block px-4 py-1 rounded-full border ${getPositionStyle(player.posicion)} font-semibold">${player.posicion}</span>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 max-w-lg w-full">
              <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">⚽</span><span class="font-bebas text-2xl text-lime-400">${player.goles}</span><span class="text-xs text-gray-300 block">Goles</span></div>
              <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">🎯</span><span class="font-bebas text-2xl text-amber-300">${player.asistencias}</span><span class="text-xs text-gray-300 block">Asistencias</span></div>
              <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">🏟️</span><span class="font-bebas text-2xl text-white">${player.partidos}</span><span class="text-xs text-gray-300 block">Partidos</span></div>
              <div class="stat-card bg-black/40 rounded-xl p-4 border border-white/10"><span class="text-2xl mb-1 block">⭐</span><span class="font-bebas text-2xl text-purple-300">${Number(player.rating || 0).toFixed(1)}</span><span class="text-xs text-gray-300 block">Rating</span></div>
            </div>

            <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-lime-500/20 rounded-full border border-lime-500/30"><span class="w-2 h-2 rounded-full bg-lime-500 animate-pulse"></span><span class="text-lime-300 text-sm font-medium">En cancha</span></div>
          </div>
        </div>
      `;
    }

    function renderTeamStats() {
      const c = document.getElementById('team-stats');
      if (!c) return;
      const totalGoles = players.reduce((a, p) => a + p.goles, 0);
      const totalAsistencias = players.reduce((a, p) => a + p.asistencias, 0);
      const avgRating = players.length ? (players.reduce((a, p) => a + p.rating, 0) / players.length).toFixed(1) : '0.0';
      c.innerHTML = `
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-bebas text-4xl text-lime-400">${players.length}</span><span class="text-sm text-gray-300 block mt-1">Jugadores</span></div>
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-bebas text-4xl text-amber-300">${totalGoles}</span><span class="text-sm text-gray-300 block mt-1">Goles Totales</span></div>
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-bebas text-4xl text-white">${totalAsistencias}</span><span class="text-sm text-gray-300 block mt-1">Asistencias</span></div>
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-bebas text-4xl text-purple-300">${avgRating}</span><span class="text-sm text-gray-300 block mt-1">Rating Promedio</span></div>
      `;
    }

    function setActivePlayer(index) {
      activePlayerIndex = index;
      renderBench();
      renderFeaturedPlayer();
      resetAutoplay();
    }
    window.setActivePlayer = setActivePlayer;

    function filterByPosition(pos) {
      currentFilter = pos;
      filteredPlayers = pos === 'Todos' ? [...players] : players.filter((p) => p.posicion === pos);
      activePlayerIndex = 0;
      renderFilters();
      renderBench();
      renderFeaturedPlayer();
      resetAutoplay();
    }
    window.filterByPosition = filterByPosition;

    function nextPlayer() {
      if (filteredPlayers.length === 0) return;
      activePlayerIndex = (activePlayerIndex + 1) % filteredPlayers.length;
      renderBench();
      renderFeaturedPlayer();
    }

    function stopAutoplay() {
      if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
      }
    }

    function resetProgressBar() {
      const pb = document.getElementById('progress-bar');
      if (!pb) return;
      pb.style.animation = 'none';
      pb.offsetHeight;
      pb.style.animation = 'progress 10s linear forwards';
    }

    function startAutoplay() {
      stopAutoplay();
      if (autoplayPaused || filteredPlayers.length <= 1) return;
      resetProgressBar();
      autoplayInterval = setInterval(() => {
        nextPlayer();
        resetProgressBar();
      }, 10000);
    }

    function resetAutoplay() {
      if (!autoplayPaused) startAutoplay();
    }

    function toggleAutoplay() {
      autoplayPaused = !autoplayPaused;
      document.getElementById('pause-icon')?.classList.toggle('hidden', autoplayPaused);
      document.getElementById('play-icon')?.classList.toggle('hidden', !autoplayPaused);
      const status = document.getElementById('autoplay-status');
      if (status) status.textContent = autoplayPaused ? 'Auto: OFF' : 'Auto: ON';
      if (autoplayPaused) stopAutoplay(); else startAutoplay();
    }

    function init() {
      renderFilters();
      renderBench();
      renderFeaturedPlayer();
      renderTeamStats();
      startAutoplay();
      document.getElementById('autoplay-toggle')?.addEventListener('click', toggleAutoplay);
    }

    init();
  </script>
</body>
</html>

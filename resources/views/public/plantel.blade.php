<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plantel - FC Cogote Salado</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css', 'resources/js/public/home.js'])
  <style>
    .player-enter { animation: playerEnter .5s cubic-bezier(.34,1.56,.64,1) forwards; }
    @keyframes playerEnter { from { opacity:0; transform: scale(.85) translateY(16px);} to { opacity:1; transform: scale(1) translateY(0);} }
    .bench-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
    .bench-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,.05); border-radius: 10px; }
    .bench-scroll::-webkit-scrollbar-thumb { background: rgba(132, 204, 22, .4); border-radius: 10px; }
    .progress-bar { animation: progress 10s linear forwards; }
    @keyframes progress { from { width:0%; } to { width:100%; } }
    .field-pattern {
      background:
        linear-gradient(90deg, transparent 49.5%, rgba(255,255,255,.12) 49.5%, rgba(255,255,255,.12) 50.5%, transparent 50.5%),
        linear-gradient(transparent 49.5%, rgba(255,255,255,.12) 49.5%, rgba(255,255,255,.12) 50.5%, transparent 50.5%),
        radial-gradient(circle at center, transparent 18%, rgba(255,255,255,.12) 18%, rgba(255,255,255,.12) 19%, transparent 19%),
        linear-gradient(135deg, #2d5016 0%, #3d6b1e 50%, #2d5016 100%);
    }
    .glow-active { box-shadow: 0 0 20px rgba(132,204,22,.45), 0 0 40px rgba(132,204,22,.2); }
    .stat-card { backdrop-filter: blur(10px); }
    .font-display { font-family: 'Oswald', sans-serif; }
    .font-body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="h-full bg-[#241337] font-body text-white overflow-auto">
  @include('public.partials.header')

  <main id="main-content" class="w-full pt-24">
    <section class="relative py-12 md:py-16 px-4 text-center overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-b from-[#2b1b45]/50 to-transparent"></div>
      <div class="relative z-10 max-w-4xl mx-auto">
        <span id="season-badge" class="inline-block px-4 py-1.5 bg-lime-500/20 text-lime-400 text-sm font-semibold rounded-full mb-4 border border-lime-500/30">Temporada {{ date('Y') }}</span>
        <h1 id="page-title" class="font-display text-5xl md:text-7xl font-bold uppercase tracking-wider mb-3 bg-gradient-to-r from-white via-amber-300 to-white bg-clip-text text-transparent">Plantel</h1>
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
                  <span class="text-sm text-gray-400 font-medium">En Cancha</span>
                  <div class="flex items-center gap-2">
                    <button id="autoplay-toggle" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors group" title="Pausar/Reanudar">
                      <svg id="pause-icon" class="w-4 h-4 text-lime-400" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" /></svg>
                      <svg id="play-icon" class="w-4 h-4 text-lime-400 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                    </button>
                    <span id="autoplay-status" class="text-xs text-gray-500">Auto: ON</span>
                  </div>
                </div>
                <div class="flex-1 max-w-32 h-1 bg-white/10 rounded-full overflow-hidden ml-4">
                  <div id="progress-bar" class="h-full bg-lime-500 rounded-full progress-bar"></div>
                </div>
              </div>

              <div class="field-pattern rounded-2xl p-6 md:p-10 min-h-[400px] md:min-h-[500px] flex items-center justify-center relative overflow-hidden border border-white/10">
                <div class="absolute inset-4 border-2 border-white/20 rounded-lg pointer-events-none"></div>
                <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-white/15 pointer-events-none"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 md:w-32 md:h-32 border-2 border-white/15 rounded-full pointer-events-none"></div>
                <div id="featured-player" class="relative z-10 text-center"></div>
              </div>
            </div>
          </div>

          <div class="lg:order-1 lg:w-80 xl:w-96">
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-display text-xl uppercase tracking-wider text-amber-300 flex items-center gap-2">👥 Banca</h2>
              <span id="player-count" class="text-sm text-gray-400"></span>
            </div>
            <div id="bench-container" class="flex lg:flex-col gap-3 overflow-x-auto lg:overflow-x-visible lg:overflow-y-auto lg:max-h-[500px] pb-4 lg:pb-0 lg:pr-2 bench-scroll snap-x lg:snap-none snap-mandatory"></div>
          </div>
        </div>
      </div>
    </section>

    <section class="px-4 pb-16">
      <div class="max-w-4xl mx-auto">
        <div class="bg-white/10 rounded-2xl p-6 md:p-8 border border-white/10">
          <h3 class="font-display text-lg uppercase tracking-wider text-center mb-6 text-gray-300">Estadísticas del Plantel</h3>
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
      'Arquero': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
      'Defensa': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
      'Mediocampista': 'bg-purple-500/20 text-purple-400 border-purple-500/30',
      'Delantero': 'bg-red-500/20 text-red-400 border-red-500/30',
    };

    let activePlayerIndex = 0;
    let currentFilter = 'Todos';
    let filteredPlayers = [...players];
    let autoplayInterval = null;
    let autoplayPaused = false;

    function getInitials(name) {
      return String(name).split(' ').map((n) => n[0]).join('').substring(0, 2).toUpperCase();
    }

    function getPositionStyle(pos) {
      return positionColors[pos] || 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }

    function renderFilters() {
      const container = document.getElementById('position-filters');
      container.innerHTML = positions.map((pos) => `
        <button onclick="filterByPosition('${pos}')" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 border ${
          currentFilter === pos
            ? 'bg-lime-500 text-[#241337] border-lime-500'
            : 'bg-white/10 text-gray-300 border-white/10 hover:bg-white/20 hover:border-lime-500/50'
        }">${pos}</button>
      `).join('');
    }

    function renderBench() {
      const container = document.getElementById('bench-container');
      const playerCount = document.getElementById('player-count');
      playerCount.textContent = `${filteredPlayers.length} jugadores`;

      container.innerHTML = filteredPlayers.map((player, index) => `
        <button onclick="setActivePlayer(${index})" class="flex-shrink-0 w-64 lg:w-full snap-start bg-white/10 hover:bg-white/20 rounded-xl p-4 border transition-all duration-300 cursor-pointer group ${
          index === activePlayerIndex ? 'border-lime-500 glow-active' : 'border-white/10 hover:border-lime-500/50'
        }">
          <div class="flex items-center gap-4">
            <div class="relative">
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-[#2b1b45] to-[#241337] overflow-hidden flex items-center justify-center border-2 ${index === activePlayerIndex ? 'border-lime-500' : 'border-white/20'}">
                ${player.foto ? `<img src="${player.foto}" alt="${player.nombre}" class="w-full h-full object-cover">` : `<span class="font-display text-lg font-bold text-white">${getInitials(player.nombre)}</span>`}
              </div>
              <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-400 text-[#241337] text-xs font-bold flex items-center justify-center">${player.numero}</span>
            </div>
            <div class="flex-1 text-left min-w-0">
              <h3 class="font-display text-base font-semibold truncate ${index === activePlayerIndex ? 'text-lime-400' : 'text-white'}">${player.nombre}</h3>
              <span class="inline-block px-2 py-0.5 text-xs rounded-full border ${getPositionStyle(player.posicion)} mt-1">${player.posicion}</span>
            </div>
            <div class="flex flex-col items-end gap-1 text-xs"><span class="text-gray-400">⚽ ${player.goles}</span><span class="text-gray-400">🎯 ${player.asistencias}</span></div>
          </div>
        </button>
      `).join('');
    }

    function renderFeaturedPlayer() {
      const container = document.getElementById('featured-player');
      const player = filteredPlayers[activePlayerIndex];
      if (!player) {
        container.innerHTML = `<div class="text-center text-gray-400"><p class="font-display text-xl">No hay jugadores</p></div>`;
        return;
      }

      container.innerHTML = `
        <div class="player-enter max-w-sm mx-auto">
          <article class="group relative rounded-2xl overflow-hidden bg-gradient-to-b from-amber-300/30 to-[#241337] border border-amber-300/25">
            <div class="aspect-[3/4] relative">
              ${player.foto ? `<img src="${player.foto}" alt="${player.nombre}" class="absolute inset-0 w-full h-full object-cover">` : ''}
              <div class="absolute inset-0 flex items-center justify-center ${player.foto ? 'hidden' : ''}">
                <div class="w-24 h-24 rounded-full bg-amber-300/30 flex items-center justify-center"><span class="text-5xl">👤</span></div>
              </div>
              <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-[#241337]/95"></div>
              <div class="absolute top-4 right-4 px-3 py-1 rounded-full border ${getPositionStyle(player.posicion)} text-xs font-semibold">${player.posicion}</div>
              <div class="absolute bottom-0 left-0 right-0 p-5 text-left">
                <div class="text-amber-300 font-display text-5xl leading-none">#${player.numero}</div>
                <h2 class="font-display text-3xl leading-tight mt-1">${player.nombre}</h2>
                <div class="grid grid-cols-4 gap-2 mt-4">
                  <div class="stat-card bg-black/35 rounded-lg p-2 text-center border border-white/10"><span class="text-[10px] text-gray-400 block">⚽</span><span class="font-display text-lg text-lime-400">${player.goles}</span></div>
                  <div class="stat-card bg-black/35 rounded-lg p-2 text-center border border-white/10"><span class="text-[10px] text-gray-400 block">🎯</span><span class="font-display text-lg text-amber-300">${player.asistencias}</span></div>
                  <div class="stat-card bg-black/35 rounded-lg p-2 text-center border border-white/10"><span class="text-[10px] text-gray-400 block">🏟️</span><span class="font-display text-lg text-white">${player.partidos}</span></div>
                  <div class="stat-card bg-black/35 rounded-lg p-2 text-center border border-white/10"><span class="text-[10px] text-gray-400 block">⭐</span><span class="font-display text-lg text-purple-300">${player.rating.toFixed(1)}</span></div>
                </div>
              </div>
            </div>
          </article>
        </div>
      `;
    }

    function renderTeamStats() {
      const c = document.getElementById('team-stats');
      const totalGoles = players.reduce((a,p) => a + p.goles, 0);
      const totalAsistencias = players.reduce((a,p) => a + p.asistencias, 0);
      const avgRating = players.length ? (players.reduce((a,p) => a + p.rating, 0) / players.length).toFixed(1) : '0.0';
      c.innerHTML = `
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-display text-3xl font-bold text-lime-400">${players.length}</span><span class="text-sm text-gray-400 block mt-1">Jugadores</span></div>
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-display text-3xl font-bold text-amber-300">${totalGoles}</span><span class="text-sm text-gray-400 block mt-1">Goles Totales</span></div>
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-display text-3xl font-bold text-white">${totalAsistencias}</span><span class="text-sm text-gray-400 block mt-1">Asistencias</span></div>
        <div class="text-center p-4 rounded-xl bg-white/10"><span class="font-display text-3xl font-bold text-purple-300">${avgRating}</span><span class="text-sm text-gray-400 block mt-1">Rating Promedio</span></div>
      `;
    }

    function setActivePlayer(index) { activePlayerIndex = index; renderBench(); renderFeaturedPlayer(); resetAutoplay(); }
    window.setActivePlayer = setActivePlayer;

    function filterByPosition(pos) {
      currentFilter = pos;
      filteredPlayers = pos === 'Todos' ? [...players] : players.filter((p) => p.posicion === pos);
      activePlayerIndex = 0;
      renderFilters(); renderBench(); renderFeaturedPlayer(); resetAutoplay();
    }
    window.filterByPosition = filterByPosition;

    function nextPlayer() {
      if (filteredPlayers.length === 0) return;
      activePlayerIndex = (activePlayerIndex + 1) % filteredPlayers.length;
      renderBench(); renderFeaturedPlayer();
    }

    function stopAutoplay() { if (autoplayInterval) { clearInterval(autoplayInterval); autoplayInterval = null; } }
    function resetProgressBar() {
      const pb = document.getElementById('progress-bar');
      pb.style.animation = 'none'; pb.offsetHeight; pb.style.animation = 'progress 10s linear forwards';
    }
    function startAutoplay() {
      stopAutoplay();
      if (autoplayPaused || filteredPlayers.length <= 1) return;
      resetProgressBar();
      autoplayInterval = setInterval(() => { nextPlayer(); resetProgressBar(); }, 10000);
    }
    function resetAutoplay() { if (!autoplayPaused) startAutoplay(); }
    function toggleAutoplay() {
      autoplayPaused = !autoplayPaused;
      document.getElementById('pause-icon').classList.toggle('hidden', autoplayPaused);
      document.getElementById('play-icon').classList.toggle('hidden', !autoplayPaused);
      document.getElementById('autoplay-status').textContent = autoplayPaused ? 'Auto: OFF' : 'Auto: ON';
      if (autoplayPaused) stopAutoplay(); else startAutoplay();
    }

    function init() {
      renderFilters(); renderBench(); renderFeaturedPlayer(); renderTeamStats(); startAutoplay();
      document.getElementById('autoplay-toggle')?.addEventListener('click', toggleAutoplay);
    }
    init();
  </script>
</body>
</html>

<!doctype html>
<html lang="es-CL" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendario - FC Cogote Salado</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css', 'resources/js/public/home.js'])
  <style>
    .glass-card { background: rgba(255,255,255,.06); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.1); }
    .day-hover:hover { transform: translateY(-2px); background: rgba(132, 204, 22, 0.1); }
    .day-selected { box-shadow: 0 0 18px rgba(132, 204, 22, .45); }
    .info-icon-box { width:2rem; height:2rem; border-radius:.7rem; display:flex; align-items:center; justify-content:center; font-size:.95rem; }
  </style>
</head>
<body class="h-full bg-club-dark font-inter text-white overflow-auto">
  @include('public.partials.header')

  <main class="w-full min-h-full pt-28">
    <section class="py-10 md:py-14 px-4 text-center">
      <div class="max-w-4xl mx-auto">
        <span id="season-badge" class="inline-block px-4 py-1.5 bg-lime-500/20 text-lime-400 text-sm font-semibold rounded-full mb-4 border border-lime-500/30">Temporada {{ date('Y') }}</span>
        <h1 id="page-title" class="text-4xl md:text-5xl lg:text-6xl font-bold mb-3 bg-gradient-to-r from-white via-amber-300 to-lime-400 bg-clip-text text-transparent">Calendario</h1>
        <p id="page-subtitle" class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">Próximas fechas y encuentros del club</p>
      </div>
    </section>

    <section class="px-4 pb-16 max-w-6xl mx-auto">
      <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
        <div class="flex-1 glass-card rounded-2xl p-4 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <button id="prev-month" class="p-2 md:p-3 rounded-xl bg-white/5 hover:bg-lime-500/20 border border-white/10">◀</button>
            <h2 id="current-month" class="text-xl md:text-2xl font-bold text-white"></h2>
            <button id="next-month" class="p-2 md:p-3 rounded-xl bg-white/5 hover:bg-lime-500/20 border border-white/10">▶</button>
          </div>

          <div class="grid grid-cols-7 gap-1 mb-2 text-center text-xs md:text-sm font-semibold">
            <div class="py-2 text-amber-300">LUN</div><div class="py-2 text-amber-300">MAR</div><div class="py-2 text-amber-300">MIÉ</div><div class="py-2 text-amber-300">JUE</div><div class="py-2 text-amber-300">VIE</div><div class="py-2 text-lime-400">SÁB</div><div class="py-2 text-lime-400">DOM</div>
          </div>
          <div id="calendar-grid" class="grid grid-cols-7 gap-1"></div>
        </div>

        <div class="lg:w-96 glass-card rounded-2xl p-4 md:p-6">
          <h3 class="text-2xl font-bebas tracking-wide text-white mb-4 flex items-center gap-2">📅 <span>Detalle del día</span></h3>
          <div id="event-panel">
            <div id="no-selection" class="text-center py-12 text-gray-400"><div class="text-3xl mb-2">🗓️</div><p>Selecciona un día para ver los eventos</p></div>
            <div id="event-card" class="hidden"></div>
            <div id="no-event" class="hidden text-center py-12">
              <div class="text-3xl mb-2">⚽</div>
              <p id="no-event-date" class="text-white font-medium mb-2"></p>
              <p class="text-gray-400 text-sm">No hay partidos programados</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    const partidos = @json($partidosData ?? []);

    let currentDate = new Date();
    let selectedDate = null;

    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const diasSemana = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];

    const getMatchForDate = (dateStr) => partidos.find((p) => p.fecha === dateStr);
    const getDaysInMonth = (y,m) => new Date(y, m + 1, 0).getDate();
    const getFirstDayOfMonth = (y,m) => { const d = new Date(y,m,1).getDay(); return d === 0 ? 6 : d - 1; };
    const formatDate = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const formatDisplayDate = (dateStr) => {
      const d = new Date(dateStr + 'T12:00:00');
      return `${diasSemana[d.getDay()]} ${d.getDate()} de ${meses[d.getMonth()]} ${d.getFullYear()}`;
    };

    function renderCalendar() {
      const grid = document.getElementById('calendar-grid');
      const monthLabel = document.getElementById('current-month');
      if (!grid || !monthLabel) return;

      const year = currentDate.getFullYear();
      const month = currentDate.getMonth();
      monthLabel.textContent = `${meses[month]} ${year}`;

      const daysInMonth = getDaysInMonth(year, month);
      const firstDay = getFirstDayOfMonth(year, month);
      const daysInPrevMonth = getDaysInMonth(year, month - 1);
      const todayStr = formatDate(new Date());

      let html = '';
      for (let i = firstDay - 1; i >= 0; i--) html += `<div class="aspect-square flex items-center justify-center text-gray-600 text-sm rounded-xl">${daysInPrevMonth - i}</div>`;
      for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const match = getMatchForDate(dateStr);
        const isToday = dateStr === todayStr;
        const isSelected = selectedDate === dateStr;
        let classes = 'aspect-square flex flex-col items-center justify-center text-sm md:text-base rounded-xl cursor-pointer transition-all duration-300 day-hover relative ';
        classes += isToday ? 'bg-amber-400/20 text-amber-300 font-bold border border-amber-300/50' : (isSelected ? 'bg-lime-500/30 text-lime-300 font-bold border border-lime-400 day-selected' : 'text-white');
        html += `<div class="${classes}" onclick="selectDate('${dateStr}')"><span>${day}</span>${match ? '<span class="absolute bottom-1 w-2 h-2 rounded-full bg-lime-400"></span>' : ''}</div>`;
      }
      const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
      for (let day = 1; day <= totalCells - (firstDay + daysInMonth); day++) html += `<div class="aspect-square flex items-center justify-center text-gray-600 text-sm rounded-xl">${day}</div>`;
      grid.innerHTML = html;
    }

    function selectDate(dateStr) { selectedDate = dateStr; renderCalendar(); showEventPanel(dateStr); }
    window.selectDate = selectDate;

    function showEventPanel(dateStr) {
      const noSelection = document.getElementById('no-selection');
      const eventCard = document.getElementById('event-card');
      const noEvent = document.getElementById('no-event');
      const noEventDate = document.getElementById('no-event-date');
      if (!noSelection || !eventCard || !noEvent || !noEventDate) return;

      const match = getMatchForDate(dateStr);
      noSelection.classList.add('hidden');
      if (match) {
        noEvent.classList.add('hidden');
        eventCard.classList.remove('hidden');
        const safeAddress = (match.direccion || '').replace(/'/g, "\\'");
        const mapsQuery = encodeURIComponent(match.direccion || match.ubicacion || '');

        eventCard.innerHTML = `
          <div class="space-y-4">
            <div class="flex items-center gap-3 pb-4 border-b border-white/10">
              <div class="w-12 h-12 rounded-xl bg-lime-500/20 flex items-center justify-center text-xl">⚽</div>
              <div>
                <p class="text-xs text-lime-400 font-semibold uppercase tracking-wider">Próximo partido</p>
                <p class="text-2xl font-bebas leading-none text-white">vs ${match.rival}</p>
              </div>
            </div>

            <div class="space-y-3 text-sm text-gray-200">
              <div class="flex items-start gap-3">
                <div class="info-icon-box bg-amber-500/20">📅</div>
                <div><p class="text-xs text-gray-400">Fecha</p><p class="text-white text-lg font-semibold leading-tight">${formatDisplayDate(match.fecha)}</p></div>
              </div>
              <div class="flex items-start gap-3">
                <div class="info-icon-box bg-lime-500/20">🕒</div>
                <div><p class="text-xs text-gray-400">Hora</p><p class="text-white text-2xl font-bebas leading-none">${match.hora || '--:--'} hrs</p></div>
              </div>
              <div class="flex items-start gap-3">
                <div class="info-icon-box bg-white/10">📍</div>
                <div><p class="text-xs text-gray-400">Ubicación</p><p class="text-white text-lg font-semibold leading-tight">${match.ubicacion}</p></div>
              </div>
              <div class="flex items-start gap-3">
                <div class="info-icon-box bg-white/10">🗺️</div>
                <div><p class="text-xs text-gray-400">Dirección</p><p class="text-white text-lg font-semibold leading-tight">${match.direccion || 'Por confirmar'}</p></div>
              </div>
            </div>

            <div class="space-y-2 pt-2">
              <button onclick="copyAddress('${safeAddress}')" class="w-full py-3 px-4 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-xl transition">📋 Copiar dirección</button>
              <a href="https://www.google.com/maps/search/?api=1&query=${mapsQuery}" target="_blank" rel="noopener noreferrer" class="w-full py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition flex items-center justify-center">🧭 Abrir en Maps</a>
            </div>
          </div>
        `;
      } else {
        eventCard.classList.add('hidden');
        noEvent.classList.remove('hidden');
        noEventDate.textContent = formatDisplayDate(dateStr);
      }
    }

    async function copyAddress(address) {
      if (!address) return;
      try {
        await navigator.clipboard.writeText(address);
      } catch (error) {
        console.error('No se pudo copiar la dirección', error);
      }
    }

    document.getElementById('prev-month')?.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
    document.getElementById('next-month')?.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

    if (partidos.length > 0) {
      currentDate = new Date(partidos[0].fecha + 'T12:00:00');
    }
    renderCalendar();
  </script>
</body>
</html>

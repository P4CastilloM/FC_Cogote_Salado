@extends('layouts.admin')

@section('title', 'Crear Plantilla')
@section('subtitle', 'Arma la plantilla del partido y descarga la cancha en PNG')

@section('content')
  <style>
    .lineup-glass {
      background: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .field-container {
      background: linear-gradient(135deg, #1a5f2a 0%, #228b22 30%, #2d8f3d 60%, #1a5f2a 100%);
      position: relative;
    }

    .field-player {
      position: absolute;
      cursor: grab;
      z-index: 10;
      user-select: none;
    }

    .field-player.dragging,
    .bench-player.dragging {
      opacity: .6;
      cursor: grabbing;
    }

    .drop-zone-active {
      box-shadow: inset 0 0 32px rgba(132, 204, 22, .35);
    }

    .lineup-exporting .no-export {
      display: none !important;
    }
  </style>

  <section class="max-w-7xl mx-auto">
    <div class="lineup-glass rounded-2xl p-3 md:p-4 mb-4">
      <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-lime-400 to-amber-400 flex items-center justify-center">⚽</div>
          <div>
            <h2 id="lineup-title" class="font-bebas text-2xl tracking-wide text-white">PLANTILLA DEL PARTIDO</h2>
            <p class="text-xs text-slate-300">Usa jugadores reales del plantel (solo primer nombre).</p>
          </div>
        </div>

        <div class="grid grid-cols-2 md:flex gap-2">
          <button id="btn-clear" class="px-3 py-2 rounded-xl border border-red-400/40 text-red-300 bg-red-500/10 hover:bg-red-500/20 text-sm">Limpiar</button>
          <button id="btn-download" class="px-3 py-2 rounded-xl border border-lime-400/40 text-lime-300 bg-lime-500/10 hover:bg-lime-500/20 text-sm">Descargar PNG</button>
        </div>
      </div>
    </div>

    <div class="lineup-glass rounded-2xl p-3 md:p-4 mb-4">
      <div class="flex justify-between items-center text-xs mb-2">
        <span id="team-a-label" class="text-lime-300 font-semibold">Equipo A</span>
        <span id="team-b-label" class="text-amber-300 font-semibold">Equipo B</span>
      </div>

      <div id="field-wrapper" class="w-full">
        <div id="field" class="field-container w-full aspect-[16/10] rounded-xl border-2 md:border-4 border-white/30 overflow-hidden">
          <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 62.5" preserveAspectRatio="none">
            <rect x="2" y="2" width="96" height="58.5" fill="none" stroke="rgba(255,255,255,.45)" stroke-width=".5"/>
            <line x1="50" y1="2" x2="50" y2="60.5" stroke="rgba(255,255,255,.45)" stroke-width=".5"/>
            <circle cx="50" cy="31.25" r="9" fill="none" stroke="rgba(255,255,255,.45)" stroke-width=".5"/>
            <rect x="2" y="15" width="16" height="32.5" fill="none" stroke="rgba(255,255,255,.45)" stroke-width=".5"/>
            <rect x="82" y="15" width="16" height="32.5" fill="none" stroke="rgba(255,255,255,.45)" stroke-width=".5"/>
          </svg>
          <div id="field-players"></div>
          <div class="absolute inset-y-0 left-1/2 w-px border-l border-dashed border-white/20 pointer-events-none"></div>
        </div>
      </div>
    </div>

    <div class="lineup-glass rounded-2xl p-3 md:p-4">
      <div class="mb-3">
        <input id="search-input" type="text" placeholder="Buscar jugador..." class="w-full rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-400 px-3 py-2 text-sm">
      </div>

      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-semibold text-white">Banca</h3>
        <span id="bench-count" class="text-xs text-slate-400">(0)</span>
      </div>

      <div id="bench" class="overflow-x-auto">
        <div id="bench-grid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2"></div>
      </div>
      <div id="bench-empty" class="hidden text-center text-slate-400 text-sm py-4">No se encontraron jugadores.</div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
  <script>
    const players = @json($players);
    const state = { onField: [], search: '', draggedId: null, draggedFromField: false };

    const getInitials = (name) => String(name || 'J').slice(0, 2).toUpperCase();
    const findPlayer = (id) => players.find(p => String(p.id) === String(id));
    const isOnField = (id) => state.onField.some(p => String(p.playerId) === String(id));

    function benchCard(player) {
      return `
        <div class="bench-player lineup-glass rounded-lg p-2 flex flex-col items-center gap-1" draggable="true" data-player-id="${player.id}">
          <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20 bg-white/10 flex items-center justify-center">
            ${player.photo
              ? `<img src="${player.photo}" alt="${player.name}" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><span class="hidden text-white text-xs font-bold">${getInitials(player.name)}</span>`
              : `<span class="text-white text-xs font-bold">${getInitials(player.name)}</span>`}
          </div>
          <span class="text-[11px] text-center text-white truncate w-full">${player.name}</span>
        </div>`;
    }

    function fieldCard(item) {
      const player = findPlayer(item.playerId);
      if (!player) return '';
      const color = item.team === 'A' ? '#84cc16' : '#fbbf24';

      return `
        <div class="field-player rounded-lg p-1.5 md:p-2 bg-black/60 border-2" style="left:${item.x}%;top:${item.y}%;transform:translate(-50%,-50%);border-color:${color};box-shadow:0 0 10px ${color}80" data-player-id="${item.playerId}" draggable="true">
          <button class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full text-[10px] leading-none no-export" data-remove-player="${item.playerId}">×</button>
          <div class="w-10 h-10 md:w-11 md:h-11 rounded-full overflow-hidden border border-white/30 bg-white/10 flex items-center justify-center">
            ${player.photo
              ? `<img src="${player.photo}" alt="${player.name}" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><span class="hidden text-white text-[10px] md:text-[11px] font-bold">${getInitials(player.name)}</span>`
              : `<span class="text-white text-[10px] md:text-[11px] font-bold">${getInitials(player.name)}</span>`}
          </div>
          <div class="text-[10px] md:text-[11px] text-white truncate max-w-[72px] md:max-w-[84px] text-center font-medium">${player.name}</div>
        </div>`;
    }

    function renderBench() {
      const filtered = players.filter(p => p.name.toLowerCase().includes(state.search.toLowerCase()) && !isOnField(p.id));
      const grid = document.getElementById('bench-grid');
      document.getElementById('bench-count').textContent = `(${filtered.length})`;
      document.getElementById('bench-empty').classList.toggle('hidden', filtered.length !== 0);
      grid.classList.toggle('hidden', filtered.length === 0);
      grid.innerHTML = filtered.map(benchCard).join('');

      grid.querySelectorAll('.bench-player').forEach(el => {
        el.addEventListener('dragstart', (e) => {
          state.draggedId = el.dataset.playerId;
          state.draggedFromField = false;
          el.classList.add('dragging');
          e.dataTransfer.setData('text/plain', state.draggedId);
        });
        el.addEventListener('dragend', () => el.classList.remove('dragging'));
      });
    }

    function renderField() {
      const holder = document.getElementById('field-players');
      holder.innerHTML = state.onField.map(fieldCard).join('');

      holder.querySelectorAll('.field-player').forEach(el => {
        el.addEventListener('dragstart', (e) => {
          state.draggedId = el.dataset.playerId;
          state.draggedFromField = true;
          el.classList.add('dragging');
          e.dataTransfer.setData('text/plain', state.draggedId);
        });
        el.addEventListener('dragend', () => el.classList.remove('dragging'));
      });

      holder.querySelectorAll('[data-remove-player]').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.removePlayer;
          state.onField = state.onField.filter(p => String(p.playerId) !== String(id));
          renderField();
          renderBench();
        });
      });
    }

    function upsertOnField(playerId, x, y, team) {
      const idx = state.onField.findIndex(p => String(p.playerId) === String(playerId));
      const next = { playerId, x: Math.max(5, Math.min(95, x)), y: Math.max(5, Math.min(95, y)), team };
      if (idx >= 0) state.onField[idx] = next;
      else state.onField.push(next);
    }

    function setupDnd() {
      const field = document.getElementById('field');
      field.addEventListener('dragover', (e) => {
        e.preventDefault();
        field.classList.add('drop-zone-active');
      });
      field.addEventListener('dragleave', () => field.classList.remove('drop-zone-active'));
      field.addEventListener('drop', (e) => {
        e.preventDefault();
        field.classList.remove('drop-zone-active');
        if (!state.draggedId) return;
        const r = field.getBoundingClientRect();
        const x = ((e.clientX - r.left) / r.width) * 100;
        const y = ((e.clientY - r.top) / r.height) * 100;
        upsertOnField(state.draggedId, x, y, x < 50 ? 'A' : 'B');
        renderField();
        renderBench();
      });

      // touch support
      field.addEventListener('touchend', (e) => {
        if (!state.draggedId) return;
        const t = e.changedTouches?.[0];
        if (!t) return;
        const r = field.getBoundingClientRect();
        if (t.clientX < r.left || t.clientX > r.right || t.clientY < r.top || t.clientY > r.bottom) return;
        const x = ((t.clientX - r.left) / r.width) * 100;
        const y = ((t.clientY - r.top) / r.height) * 100;
        upsertOnField(state.draggedId, x, y, x < 50 ? 'A' : 'B');
        renderField();
        renderBench();
      });

      document.getElementById('bench-grid').addEventListener('touchstart', (e) => {
        const card = e.target.closest('.bench-player');
        if (!card) return;
        state.draggedId = card.dataset.playerId;
        state.draggedFromField = false;
      }, { passive: true });
    }

    async function downloadPng() {
      const btn = document.getElementById('btn-download');
      const wrapper = document.getElementById('field-wrapper');
      btn.disabled = true;
      btn.textContent = 'Generando...';
      wrapper.classList.add('lineup-exporting');
      try {
        const canvas = await html2canvas(wrapper, { scale: 2, useCORS: true, backgroundColor: null });
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = `plantilla-fccs-${Date.now()}.png`;
        link.click();
      } finally {
        wrapper.classList.remove('lineup-exporting');
        btn.disabled = false;
        btn.textContent = 'Descargar PNG';
      }
    }

    function init() {
      document.getElementById('search-input').addEventListener('input', (e) => { state.search = e.target.value; renderBench(); });
      document.getElementById('btn-clear').addEventListener('click', () => { state.onField = []; renderField(); renderBench(); });
      document.getElementById('btn-download').addEventListener('click', downloadPng);
      setupDnd();
      renderBench();
      renderField();
    }

    init();
  </script>
@endpush

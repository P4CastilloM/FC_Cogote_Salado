<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmar Asistencia - FC Cogote Salado</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-[#241337] text-white">
  <main class="max-w-3xl mx-auto px-4 py-8">
    <div class="rounded-2xl border border-lime-400/20 bg-white/5 p-5 mb-5">
      <h1 class="font-bebas tracking-wider text-3xl text-lime-300">✅ Confirmar asistencia</h1>
      <p class="text-sm text-slate-300 mt-2">Partido: <strong>{{ \Carbon\Carbon::parse($partido->fecha)->translatedFormat('d M Y') }}</strong> · vs {{ $partido->rival ?? 'Rival por definir' }} · {{ $partido->nombre_lugar ?? 'Lugar por definir' }}</p>
      <p class="text-sm text-slate-300">Confirmados actuales: <strong>{{ $confirmedCount }}</strong></p>
      @php($flashAlert = session('attendance_alert') ?: $alert)
      @if($flashAlert)
        <p class="text-amber-300 text-sm mt-2">{{ $flashAlert }}</p>
      @endif
    </div>

    @if(session('status'))
      <div class="rounded-xl border border-lime-400/30 bg-lime-500/10 p-3 text-lime-200 mb-4">{{ session('status') }}</div>
    @endif

    @if($errors->any())
      <div class="rounded-xl border border-red-400/30 bg-red-500/10 p-3 text-red-200 mb-4">
        <ul class="list-disc pl-5">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('fccs.partidos.asistencia.confirm', $partido->attendance_token) }}" id="attendanceForm" class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-5">
      @csrf

      <div class="space-y-3">
        <h2 class="text-lg font-semibold text-white">Buscar jugador</h2>
        <p class="text-sm text-slate-300">Recuerda: buscar por <strong>RUT sin guión ni dígito verificador</strong>.</p>
        <div class="flex flex-col sm:flex-row gap-2">
          <input type="text" id="rutSearch" class="w-full rounded-xl bg-black/20 border border-white/15 px-4 py-3" placeholder="Ej: 12345678">
          <button type="button" id="searchMainBtn" class="px-4 py-3 rounded-xl border border-sky-400/40 bg-sky-500/10 text-sky-200 font-medium">Buscar</button>
        </div>
        <div id="searchResults" class="space-y-2"></div>
      </div>

      <input type="hidden" name="actor_rut" id="actorRut" value="{{ old('actor_rut') }}">

      <div id="attendanceFields" class="hidden space-y-4">
        <div class="rounded-xl border border-lime-400/30 bg-lime-500/10 p-3 text-lime-200 text-sm" id="selectedPlayer">
          Rut | Nombre:
        </div>

        <div class="rounded-xl border border-white/10 bg-black/20 p-4 space-y-3">
          <h3 class="font-semibold text-white">Confirmación Check</h3>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="will_attend" value="1" checked required>
            <span>Confirmo que asistiré al partido</span>
          </label>
        </div>

        <div class="rounded-xl border border-white/10 bg-black/20 p-4 space-y-3">
          <h3 class="font-semibold text-white">¿Desea ingresar a alguien más?</h3>
          <p class="text-sm text-slate-300">Puedes agregar hasta 6 personas. Para cada una, ingresa su RUT y presiona Buscar para mostrar su nombre.</p>
          <div id="guestsList" class="space-y-3"></div>
          <button type="button" id="addGuest" class="px-3 py-2 rounded-lg border border-sky-400/40 bg-sky-500/10 text-sky-200 text-sm">+ Agregar persona</button>
        </div>


        <div class="rounded-xl border border-white/10 bg-black/20 p-4 space-y-3">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h3 class="font-semibold text-white">🧳 Invitar visita</h3>
              <p class="text-sm text-slate-300">Opcional. Puedes ingresar hasta 4 visitas (RUT, Nombre, Apellidos opcional).</p>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-200">
              <input type="checkbox" id="enableVisitors"> Activar
            </label>
          </div>

          <div id="visitorsWrap" class="hidden space-y-3">
            <div id="visitorsList" class="space-y-3"></div>
            <button type="button" id="addVisitor" class="px-3 py-2 rounded-lg border border-lime-400/40 bg-lime-500/10 text-lime-200 text-sm">+ Agregar visita</button>
          </div>
        </div>

        <button type="submit" class="px-4 py-3 rounded-xl bg-lime-500 text-slate-900 font-semibold">Confirmar check</button>
      </div>
    </form>
  </main>

  <script>
    (() => {
      const searchInput = document.getElementById('rutSearch');
      const searchMainBtn = document.getElementById('searchMainBtn');
      const results = document.getElementById('searchResults');
      const actorRut = document.getElementById('actorRut');
      const fields = document.getElementById('attendanceFields');
      const selectedPlayer = document.getElementById('selectedPlayer');
      const guestsList = document.getElementById('guestsList');
      const addGuestBtn = document.getElementById('addGuest');
      const enableVisitors = document.getElementById('enableVisitors');
      const visitorsWrap = document.getElementById('visitorsWrap');
      const visitorsList = document.getElementById('visitorsList');
      const addVisitorBtn = document.getElementById('addVisitor');

      let guestCount = 0;
      const token = @json($partido->attendance_token);
      const searchRouteTemplate = @json(route('fccs.partidos.asistencia.search', ['token' => '__TOKEN__']));
      const searchUrlObj = new URL(searchRouteTemplate.replace('__TOKEN__', token), window.location.origin);
      const searchUrl = `${window.location.origin}${searchUrlObj.pathname}`;

      function sanitizeRut(value) {
        return String(value || '').replace(/\D+/g, '');
      }

      async function fetchPlayersByRut(rut) {
        const cleanRut = sanitizeRut(rut);
        if (cleanRut.length < 5) return { players: [] };

        let response;
        try {
          response = await fetch(`${searchUrl}?rut=${encodeURIComponent(cleanRut)}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
          });
        } catch (error) {
          return { error: 'No se pudo conectar al servidor para buscar jugadores.' };
        }

        if (!response.ok) {
          return { error: 'No se pudo completar la búsqueda. Intenta nuevamente.' };
        }

        const data = await response.json().catch(() => ({ players: [] }));
        return { players: data.players || [] };
      }

      function renderPlayerButtons(container, players, onSelect) {
        if (!players.length) {
          container.innerHTML = '<p class="text-sm text-slate-400">No se encontraron jugadores para ese RUT.</p>';
          return;
        }

        container.innerHTML = players.map((p) => `
          <button type="button" class="w-full text-left px-3 py-2 rounded-lg border border-white/15 bg-black/20 hover:bg-white/10"
            data-rut="${p.rut}"
            data-name="${p.name}"
            data-nombre="${p.nombre || ''}"
            data-apellido="${p.apellido || ''}">
            ${p.rut} | ${p.name}
          </button>
        `).join('');

        container.querySelectorAll('button[data-rut]').forEach((btn) => {
          btn.addEventListener('click', () => onSelect(btn.dataset.rut, btn.dataset.name, {
            nombre: btn.dataset.nombre || '',
            apellido: btn.dataset.apellido || '',
          }));
        });
      }



      function addVisitorInput(seed = {}) {
        if (!visitorsList) return;
        if (visitorsList.children.length >= 4) return;

        const row = document.createElement('div');
        row.className = 'rounded-xl border border-white/10 bg-black/25 p-3';
        row.innerHTML = `
          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-2 items-start">
            <input type="text" class="visitor-rut rounded-lg bg-black/20 border border-white/15 px-3 py-2 sm:col-span-1 xl:col-span-3" placeholder="RUT" value="${sanitizeRut(seed.rut || '')}">
            <input type="text" class="visitor-nombre rounded-lg bg-black/20 border border-white/15 px-3 py-2 sm:col-span-1 xl:col-span-3" placeholder="Nombre" value="${seed.nombre || ''}">
            <input type="text" class="visitor-apellido rounded-lg bg-black/20 border border-white/15 px-3 py-2 sm:col-span-2 xl:col-span-4" placeholder="Apellidos (opcional)" value="${seed.apellido || ''}">
            <div class="sm:col-span-2 xl:col-span-2 flex flex-wrap xl:justify-end gap-2">
              <button type="button" class="visitor-autofill px-3 py-2 rounded-lg border border-sky-400/40 bg-sky-500/10 text-sky-200 text-sm whitespace-nowrap">Autocompletar</button>
              <button type="button" class="visitor-remove px-3 py-2 rounded-lg border border-red-400/40 bg-red-500/10 text-red-200 text-sm whitespace-nowrap">Quitar</button>
            </div>
          </div>
        `;

        const rutInput = row.querySelector('.visitor-rut');
        const nombreInput = row.querySelector('.visitor-nombre');
        const apellidoInput = row.querySelector('.visitor-apellido');
        const autofillBtn = row.querySelector('.visitor-autofill');
        const removeBtn = row.querySelector('.visitor-remove');

        const syncNames = () => {
          row.querySelectorAll('input[data-hidden]').forEach((el) => el.remove());

          const rut = sanitizeRut(rutInput.value);
          if (!rut) return;

          const fields = [
            ['visitantes[][rut]', rut],
            ['visitantes[][nombre]', nombreInput.value.trim()],
            ['visitantes[][apellido]', apellidoInput.value.trim()],
          ];

          for (const [name, value] of fields) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = value;
            hidden.setAttribute('data-hidden', '1');
            row.appendChild(hidden);
          }
        };

        const autofill = async () => {
          const rut = sanitizeRut(rutInput.value);
          if (rut.length < 5) return;
          const result = await fetchPlayersByRut(rut);
          const first = (result.players || [])[0];
          if (!first) return;
          nombreInput.value = first.nombre || first.name || '';
          apellidoInput.value = first.apellido || '';
          syncNames();
        };

        rutInput.addEventListener('input', syncNames);
        nombreInput.addEventListener('input', syncNames);
        apellidoInput.addEventListener('input', syncNames);
        autofillBtn.addEventListener('click', autofill);
        removeBtn.addEventListener('click', () => row.remove());

        visitorsList.appendChild(row);
        syncNames();
      }

      async function searchMainPlayer() {
        const rut = sanitizeRut(searchInput.value);
        if (rut.length < 5) {
          results.innerHTML = '<p class="text-sm text-amber-300">Ingresa al menos 5 dígitos.</p>';
          return;
        }

        results.innerHTML = '<p class="text-sm text-slate-400">Buscando...</p>';
        const result = await fetchPlayersByRut(rut);
        if (result.error) {
          results.innerHTML = `<p class="text-sm text-amber-300">${result.error}</p>`;
          return;
        }

        const players = result.players || [];
        renderPlayerButtons(results, players, (selectedRut, selectedName) => {
          actorRut.value = selectedRut;
          selectedPlayer.textContent = `Rut | Nombre: ${selectedRut} | ${selectedName}`;
          fields.classList.remove('hidden');
          results.innerHTML = '';
        });
      }

      function addGuestInput(value = '') {
        if (guestCount >= 6) return;
        guestCount += 1;

        const row = document.createElement('div');
        row.className = 'rounded-lg border border-white/10 bg-black/20 p-3 space-y-2';

        row.innerHTML = `
          <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" class="guest-rut w-full rounded-lg bg-black/20 border border-white/15 px-3 py-2" placeholder="RUT adicional" value="${value}">
            <button type="button" class="guest-search px-3 py-2 rounded-lg border border-sky-400/40 bg-sky-500/10 text-sky-200 text-sm">Buscar</button>
            <button type="button" class="guest-remove px-3 py-2 rounded-lg bg-red-500/20 text-red-200 text-sm">Quitar</button>
          </div>
          <div class="guest-name text-sm text-slate-300"></div>
          <div class="guest-results space-y-2"></div>
          <input type="hidden" name="guests[]" class="guest-hidden-rut" value="${sanitizeRut(value)}">
        `;

        const rutInput = row.querySelector('.guest-rut');
        const searchBtn = row.querySelector('.guest-search');
        const removeBtn = row.querySelector('.guest-remove');
        const guestName = row.querySelector('.guest-name');
        const guestResults = row.querySelector('.guest-results');
        const hiddenRut = row.querySelector('.guest-hidden-rut');

        async function searchGuest() {
          const rut = sanitizeRut(rutInput.value);
          if (rut.length < 5) {
            guestResults.innerHTML = '<p class="text-sm text-amber-300">Ingresa al menos 5 dígitos.</p>';
            return;
          }

          guestResults.innerHTML = '<p class="text-sm text-slate-400">Buscando...</p>';
          const result = await fetchPlayersByRut(rut);
          if (result.error) {
            guestResults.innerHTML = `<p class="text-sm text-amber-300">${result.error}</p>`;
            return;
          }

          const players = result.players || [];
          renderPlayerButtons(guestResults, players, (selectedRut, selectedName) => {
            rutInput.value = selectedRut;
            hiddenRut.value = selectedRut;
            guestName.textContent = `Rut | Nombre: ${selectedRut} | ${selectedName}`;
            guestResults.innerHTML = '';
          });
        }

        searchBtn.addEventListener('click', searchGuest);
        rutInput.addEventListener('keydown', (event) => {
          if (event.key === 'Enter') {
            event.preventDefault();
            searchGuest();
          }
        });

        removeBtn.addEventListener('click', () => {
          row.remove();
          guestCount -= 1;
        });

        guestsList.appendChild(row);
      }

      searchMainBtn?.addEventListener('click', searchMainPlayer);
      searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          event.preventDefault();
          searchMainPlayer();
        }
      });

      addGuestBtn?.addEventListener('click', () => addGuestInput());

      enableVisitors?.addEventListener('change', () => {
        const active = enableVisitors.checked;
        visitorsWrap?.classList.toggle('hidden', !active);
        if (active && visitorsList && visitorsList.children.length === 0) {
          addVisitorInput();
        }
      });

      addVisitorBtn?.addEventListener('click', () => addVisitorInput());

      @if(old('actor_rut'))
        fields.classList.remove('hidden');
        selectedPlayer.textContent = 'Rut | Nombre: {{ old('actor_rut') }} | (seleccionado anteriormente)';
      @endif

      @foreach(old('guests', []) as $guest)
        addGuestInput(@json($guest));
      @endforeach

      @if(is_array(old('visitantes')) && count(old('visitantes')) > 0)
        enableVisitors.checked = true;
        visitorsWrap.classList.remove('hidden');
      @foreach(old('visitantes', []) as $visitante)
        addVisitorInput(@json($visitante));
      @endforeach
      @endif
    })();
  </script>
</body>
</html>

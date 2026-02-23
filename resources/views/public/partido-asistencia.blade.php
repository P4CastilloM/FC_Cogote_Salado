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

    <form method="POST" action="{{ route('fccs.partidos.asistencia.confirm', $partido->attendance_token) }}" id="attendanceForm" class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-4">
      @csrf

      <div>
        <label class="text-sm text-slate-300">Busca tu RUT (sin guión ni dígito)</label>
        <input type="text" id="rutSearch" class="mt-1 w-full rounded-xl bg-black/20 border border-white/15 px-4 py-3" placeholder="Ej: 12345678">
        <div id="searchResults" class="mt-2 space-y-2"></div>
      </div>

      <input type="hidden" name="actor_rut" id="actorRut" value="{{ old('actor_rut') }}">

      <div id="attendanceFields" class="hidden space-y-4">
        <div class="rounded-xl border border-lime-400/30 bg-lime-500/10 p-3 text-lime-200 text-sm" id="selectedPlayer"></div>

        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="will_attend" value="1" checked required>
          <span>Confirmo que asistiré al partido</span>
        </label>

        <div>
          <label class="text-sm text-slate-300">¿Deseas agregar a alguien más? (máx. 6)</label>
          <div id="guestsList" class="space-y-2 mt-2"></div>
          <button type="button" id="addGuest" class="mt-2 px-3 py-2 rounded-lg border border-sky-400/40 bg-sky-500/10 text-sky-200 text-sm">+ Agregar RUT de familiar/amigo</button>
        </div>

        <button type="submit" class="px-4 py-3 rounded-xl bg-lime-500 text-slate-900 font-semibold">Confirmar asistencia</button>
      </div>
    </form>
  </main>

  <script>
    (() => {
      const searchInput = document.getElementById('rutSearch');
      const results = document.getElementById('searchResults');
      const actorRut = document.getElementById('actorRut');
      const fields = document.getElementById('attendanceFields');
      const selectedPlayer = document.getElementById('selectedPlayer');
      const guestsList = document.getElementById('guestsList');
      const addGuestBtn = document.getElementById('addGuest');
      let guestCount = 0;

      const token = @json($partido->attendance_token);
      const searchUrl = `{{ route('fccs.partidos.asistencia.search', ['token' => '__TOKEN__']) }}`.replace('__TOKEN__', token);

      function addGuestInput(value = '') {
        if (guestCount >= 6) return;
        guestCount += 1;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `<input type="text" name="guests[]" value="${value}" class="w-full rounded-lg bg-black/20 border border-white/15 px-3 py-2" placeholder="RUT adicional"> <button type="button" class="px-2 py-2 rounded bg-red-500/20 text-red-200">X</button>`;
        row.querySelector('button').addEventListener('click', () => {
          row.remove();
          guestCount -= 1;
        });
        guestsList.appendChild(row);
      }

      addGuestBtn?.addEventListener('click', () => addGuestInput());

      let timer = null;
      searchInput?.addEventListener('input', () => {
        clearTimeout(timer);
        const q = (searchInput.value || '').replace(/\D+/g, '');
        if (q.length < 5) {
          results.innerHTML = '';
          return;
        }

        timer = setTimeout(async () => {
          const response = await fetch(`${searchUrl}?rut=${encodeURIComponent(q)}`);
          const data = await response.json().catch(() => ({ players: [] }));
          const players = data.players || [];

          results.innerHTML = players.map((p) => `<button type="button" class="w-full text-left px-3 py-2 rounded-lg border border-white/15 bg-black/20 hover:bg-white/10" data-rut="${p.rut}" data-name="${p.name}">${p.name} · ${p.rut}</button>`).join('');

          results.querySelectorAll('button[data-rut]').forEach((btn) => {
            btn.addEventListener('click', () => {
              actorRut.value = btn.dataset.rut;
              selectedPlayer.textContent = `Jugador seleccionado: ${btn.dataset.name} (${btn.dataset.rut})`;
              fields.classList.remove('hidden');
              results.innerHTML = '';
            });
          });
        }, 250);
      });

      @if(old('actor_rut'))
        fields.classList.remove('hidden');
        selectedPlayer.textContent = `Jugador seleccionado: RUT {{ old('actor_rut') }}`;
      @endif

      @foreach(old('guests', []) as $guest)
        addGuestInput(@json($guest));
      @endforeach
    })();
  </script>
</body>
</html>

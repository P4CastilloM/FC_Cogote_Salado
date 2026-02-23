@extends('layouts.admin')

@section('title', 'Editar '.$config['label'])
@section('subtitle', 'Modifica solo los campos editables y elimina si corresponde')

@section('content')
    <style>
        .module-wrap { background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%); border: 1px solid rgba(212, 175, 55, 0.2); }
        .module-input { background: rgba(26, 10, 46, 0.45); border: 1px solid rgba(255,255,255,0.17); color: white; }
        .module-input:focus { border-color: rgba(16,185,129,.9); box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
        .module-select { background-color: rgba(26, 10, 46, 0.45); color-scheme: dark; }
        .module-select option { background: #1f1238; color: #f3f4f6; }
    </style>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status') === 'item-updated')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Registro actualizado correctamente.</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">
                <ul class="list-disc ms-5 space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.'.$module.'.update', $item->id) }}" enctype="multipart/form-data" class="module-wrap rounded-2xl p-5 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            @if(in_array($module, ['noticias', 'avisos', 'partidos', 'premios'], true))
                <div>
                    <label class="text-sm text-slate-300">Temporada *</label>
                    <select name="temporada_id" required class="module-input module-select w-full rounded-xl px-4 py-3 mt-1">
                        @foreach($temporadas as $temporada)
                            <option value="{{ $temporada->id }}" @selected(old('temporada_id', $item->temporada_id) == $temporada->id)>#{{ $temporada->id }} — {{ $temporada->descripcion ?: 'Sin descripción' }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($module === 'noticias')
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="titulo" maxlength="60" required value="{{ old('titulo', $item->titulo) }}" placeholder="📝 Título">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="subtitulo" maxlength="100" value="{{ old('subtitulo', $item->subtitulo) }}" placeholder="✏️ Subtítulo">
                <textarea class="module-input w-full rounded-xl px-4 py-3" name="cuerpo" rows="6" required placeholder="📚 Cuerpo">{{ old('cuerpo', $item->cuerpo) }}</textarea>
                <input class="module-input w-full rounded-xl px-4 py-3" type="date" name="fecha" required value="{{ old('fecha', $item->fecha) }}">
                <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto2" accept="image/jpeg,image/png,image/webp">
            @endif

            @if($module === 'avisos')
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="titulo" maxlength="50" required value="{{ old('titulo', $item->titulo) }}" placeholder="📣 Título">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="descripcion" maxlength="120" required value="{{ old('descripcion', $item->descripcion) }}" placeholder="🗒️ Descripción">
                <input class="module-input w-full rounded-xl px-4 py-3" type="date" name="fecha" required value="{{ old('fecha', $item->fecha) }}">
                <label class="inline-flex items-center gap-2 text-slate-200"><input type="checkbox" name="fijado" value="1" @checked(old('fijado', (string) ($item->fijado ?? 0)) == '1')> 📌 Fijar aviso</label>
                <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
            @endif

            @if($module === 'partidos')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input class="module-input w-full rounded-xl px-4 py-3" type="date" name="fecha" required value="{{ old('fecha', $item->fecha) }}">
                    <input class="module-input w-full rounded-xl px-4 py-3" type="time" name="hora" value="{{ old('hora', $item->hora) }}" placeholder="🕒 Hora">
                    <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="rival" maxlength="100" required value="{{ old('rival', $item->rival) }}" placeholder="🆚 Rival">
                    <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="nombre_lugar" maxlength="100" required value="{{ old('nombre_lugar', $item->nombre_lugar) }}" placeholder="📍 Lugar">
                </div>
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="direccion" maxlength="180" value="{{ old('direccion', $item->direccion) }}" placeholder="🗺️ Dirección">

                <section id="live-match-stats" class="rounded-xl border border-white/15 bg-slate-950/40 p-4 space-y-3" data-match-id="{{ $item->id }}" data-fetch-url="{{ route('admin.partidos.stats', $item->id) }}" data-sync-url="{{ route('admin.partidos.stats.sync', $item->id) }}" data-finalize-url="{{ route('admin.partidos.finalize', $item->id) }}" data-csrf="{{ csrf_token() }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-white font-semibold">📊 Estadísticas en vivo del partido</h3>
                            <p class="text-xs text-slate-300">Se guarda en tiempo real para todos los usuarios conectados. Solo se aplica al plantel cuando finalizas el partido.</p>
                        </div>
                        <button type="button" id="finalize-match-btn" class="px-3 py-2 rounded-lg bg-amber-500/20 border border-amber-400/40 text-amber-200 text-sm">🏁 Finalizar partido</button>
                    </div>
                    <div id="stats-status" class="text-xs text-slate-300">Cargando confirmados...</div>
                    <div class="overflow-auto rounded-lg border border-white/10">
                        <table class="w-full text-sm">
                            <thead class="bg-black/30 text-slate-300">
                                <tr>
                                    <th class="text-left px-3 py-2">Jugador confirmado</th>
                                    <th class="text-center px-3 py-2">⚽ Goles</th>
                                    <th class="text-center px-3 py-2">🎯 Asistencias</th>
                                </tr>
                            </thead>
                            <tbody id="stats-table-body" class="text-slate-100"></tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if($module === 'premios')
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="nombre" maxlength="20" required value="{{ old('nombre', $item->nombre) }}" placeholder="🥇 Nombre">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="descripcion" maxlength="50" value="{{ old('descripcion', $item->descripcion) }}" placeholder="🧾 Descripción">
            @endif

            @if($module === 'temporadas')
                <input class="module-input w-full rounded-xl px-4 py-3" type="date" name="fecha_inicio" required value="{{ old('fecha_inicio', $item->fecha_inicio) }}">
                <input class="module-input w-full rounded-xl px-4 py-3" type="date" name="fecha_termino" value="{{ old('fecha_termino', $item->fecha_termino) }}">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="descripcion" maxlength="150" value="{{ old('descripcion', $item->descripcion) }}" placeholder="🗂️ Descripción">
            @endif

            @if(in_array($module, ['staff', 'directiva'], true))
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="nombre" maxlength="20" required value="{{ old('nombre', $item->nombre) }}" placeholder="👤 Nombre">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="apellido" maxlength="20" value="{{ old('apellido', $item->apellido) }}" placeholder="👤 Apellido">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="descripcion_rol" maxlength="50" value="{{ old('descripcion_rol', $item->descripcion_rol) }}" placeholder="🎖️ Rol">
                @if($module === 'directiva')
                    <input class="module-input w-full rounded-xl px-4 py-3" type="number" name="prioridad" min="1" max="10" required value="{{ old('prioridad', $item->prioridad ?? 10) }}" placeholder="🔢 Prioridad">
                @endif
                <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                <label class="inline-flex items-center gap-2 text-slate-300"><input type="checkbox" name="activo" value="1" @checked(old('activo', (string) $item->activo) == '1')> ✅ Activo</label>
                <div class="rounded-xl border border-amber-400/25 bg-amber-500/10 px-4 py-3 text-xs text-amber-200">ℹ️ En ayudantes solo se edita información de base de datos. Login/correo/clave no se muestra aquí.</div>
            @endif

            <div class="pt-4 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.'.$module.'.index') }}" class="text-center px-6 py-3 rounded-xl border border-white/20 text-slate-200">← Volver al buscador</a>
                <button class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">💾 Guardar cambios</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.'.$module.'.destroy', $item->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')" class="module-wrap rounded-2xl p-5 border-red-500/40">
            @csrf
            @method('DELETE')
            <button class="w-full px-6 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-lg">🗑️ ELIMINAR REGISTRO</button>
        </form>
    </div>

@if($module === 'partidos')
    <script>
        (() => {
            const root = document.getElementById('live-match-stats');
            if (!root) return;

            const statusEl = document.getElementById('stats-status');
            const bodyEl = document.getElementById('stats-table-body');
            const finalizeBtn = document.getElementById('finalize-match-btn');
            const fetchUrl = root.dataset.fetchUrl;
            const syncUrl = root.dataset.syncUrl;
            const finalizeUrl = root.dataset.finalizeUrl;
            const csrf = root.dataset.csrf;
            const entries = new Map();
            let finalized = false;
            let editingActive = false;
            let syncing = false;

            const setStatus = (message, tone = 'text-slate-300') => {
                statusEl.className = `text-xs ${tone}`;
                statusEl.textContent = message;
            };

            const renderRows = (players) => {
                if (!players.length) {
                    bodyEl.innerHTML = '<tr><td colspan="3" class="px-3 py-3 text-slate-400">Aún no hay confirmados para este partido.</td></tr>';
                    return;
                }

                bodyEl.innerHTML = players.map((player) => `
                    <tr class="border-t border-white/10">
                        <td class="px-3 py-2">${player.nombre}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" data-rut="${player.rut}" data-field="goles" data-step="-1" class="px-2 py-1 rounded bg-white/10" ${(finalized || !editingActive) ? 'disabled' : ''}>−</button>
                                <span id="goles-${player.rut}" class="min-w-8 text-center font-semibold">${player.goles}</span>
                                <button type="button" data-rut="${player.rut}" data-field="goles" data-step="1" class="px-2 py-1 rounded bg-white/10" ${(finalized || !editingActive) ? 'disabled' : ''}>+</button>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" data-rut="${player.rut}" data-field="asistencias" data-step="-1" class="px-2 py-1 rounded bg-white/10" ${(finalized || !editingActive) ? 'disabled' : ''}>−</button>
                                <span id="asistencias-${player.rut}" class="min-w-8 text-center font-semibold">${player.asistencias}</span>
                                <button type="button" data-rut="${player.rut}" data-field="asistencias" data-step="1" class="px-2 py-1 rounded bg-white/10" ${(finalized || !editingActive) ? 'disabled' : ''}>+</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            };

            const load = async () => {
                const response = await fetch(fetchUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('No se pudo cargar el estado del partido.');

                const data = await response.json();
                finalized = Boolean(data?.match?.finalized_at);
                editingActive = Boolean(data?.match?.is_active);
                finalizeBtn.disabled = finalized;

                entries.clear();
                (data.players || []).forEach((player) => {
                    entries.set(player.rut, {
                        jugador_rut: player.rut,
                        goles: Number(player.goles || 0),
                        asistencias: Number(player.asistencias || 0),
                    });
                });

                renderRows(data.players || []);
                if (finalized) {
                    setStatus('✅ Partido finalizado: estadísticas aplicadas y edición bloqueada.', 'text-lime-300');
                } else if (!editingActive) {
                    setStatus('⏳ Fuera de la ventana activa (4 horas desde el inicio del partido).', 'text-amber-300');
                } else if ((data.players || []).length > 0) {
                    setStatus('Edición compartida en vivo activa.', 'text-sky-300');
                }
            };

            const sync = async () => {
                if (syncing || finalized || !editingActive || entries.size === 0) return;
                syncing = true;

                try {
                    const response = await fetch(syncUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ entries: Array.from(entries.values()) }),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || 'No se pudo guardar la actualización.');
                    }
                } catch (error) {
                    setStatus(`⚠️ ${error.message}`, 'text-amber-300');
                } finally {
                    syncing = false;
                }
            };

            bodyEl.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-rut]');
                if (!button || finalized || !editingActive) return;

                const rut = Number(button.dataset.rut);
                const field = button.dataset.field;
                const step = Number(button.dataset.step);
                const entry = entries.get(rut);
                if (!entry) return;

                const next = Math.max(0, Number(entry[field]) + step);
                entry[field] = next;
                document.getElementById(`${field}-${rut}`).textContent = String(next);
                setStatus('Guardando cambios...', 'text-sky-300');
                sync();
            });

            finalizeBtn.addEventListener('click', async () => {
                if (finalized) return;
                if (!confirm('¿Finalizar partido? Esta acción aplica estadísticas a confirmados y bloquea edición.')) return;

                try {
                    await sync();
                    const response = await fetch(finalizeUrl, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || 'No se pudo finalizar el partido.');
                    }

                    finalized = true;
                    finalizeBtn.disabled = true;
                    setStatus('✅ Partido finalizado y estadísticas aplicadas.', 'text-lime-300');
                    await load();
                } catch (error) {
                    setStatus(`⚠️ ${error.message}`, 'text-amber-300');
                }
            });

            load().catch((error) => setStatus(`⚠️ ${error.message}`, 'text-amber-300'));
            setInterval(() => {
                if (!document.hidden) {
                    load().catch(() => {});
                }
            }, 5000);
        })();
    </script>
@endif
@endsection

@extends('layouts.admin')

@section('title', 'Agregar Jugador')
@section('subtitle', 'Registra un nuevo jugador del plantel')

@section('content')
    <style>
        .player-wrap {
            background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        .player-input {
            background: rgba(26, 10, 46, 0.45);
            border: 1px solid rgba(255,255,255,0.17);
            color: white;
        }
        .player-input:focus {
            border-color: rgba(16,185,129,.9);
            box-shadow: 0 0 0 3px rgba(16,185,129,.2);
        }
        .player-select {
            background-color: rgba(26, 10, 46, 0.45);
            color: white;
            color-scheme: dark;
        }
        .player-select option {
            background: #1f1238;
            color: #f3f4f6;
        }
    </style>

    <div class="max-w-6xl mx-auto space-y-6">
        @if(session('status') === 'item-created')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                ✅ ¡Jugador guardado correctamente!
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">
                <p class="font-semibold mb-2">Corrige los siguientes campos:</p>
                <ul class="list-disc ms-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <header class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-2xl">🧑‍💼</div>
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Agregar <span class="text-amber-300">Jugador</span></h1>
                <p class="text-slate-400">Registra un nuevo jugador del plantel</p>
            </div>
        </header>

        <form method="POST" action="{{ route('admin.plantel.store') }}" enctype="multipart/form-data" class="player-wrap rounded-2xl p-5 sm:p-8 space-y-8">
            @csrf

            <section class="space-y-4">
                <h2 class="text-2xl font-semibold text-white">👤 Datos del Jugador</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-slate-300">RUT * (sin dígito verificador)</label>
                        <input class="player-input w-full rounded-xl px-4 py-3 mt-1" type="number" min="1" max="99999999" id="rut" name="rut" required value="{{ old('rut') }}" placeholder="12345678">
                        <p class="text-xs text-slate-500 mt-1">Solo números, máximo 8 dígitos.</p>
                    </div>

                    <div>
                        <label class="text-sm text-slate-300">Nombre completo *</label>
                        <input class="player-input w-full rounded-xl px-4 py-3 mt-1" type="text" id="nombre" name="nombre" maxlength="25" required value="{{ old('nombre') }}" placeholder="Ej: Juan Pérez González">
                    </div>

                    <div>
                        <label class="text-sm text-slate-300">Número de camiseta *</label>
                        <input class="player-input w-full rounded-xl px-4 py-3 mt-1" type="number" id="numero_camiseta" name="numero_camiseta" min="1" max="65535" required value="{{ old('numero_camiseta') }}" placeholder="1 - 99">
                    </div>

                    <div>
                        <label class="text-sm text-slate-300">Posición *</label>
                        <select id="posicion" name="posicion" required class="player-input player-select w-full rounded-xl px-4 py-3 mt-1">
                            <option value="">Selecciona una posición</option>
                            <option value="ARQUERO" @selected(old('posicion') === 'ARQUERO')>🧤 Arquero</option>
                            <option value="DEFENSA" @selected(old('posicion') === 'DEFENSA')>🛡️ Defensa</option>
                            <option value="MEDIOCAMPISTA" @selected(old('posicion') === 'MEDIOCAMPISTA')>🎯 Mediocampista</option>
                            <option value="DELANTERO" @selected(old('posicion') === 'DELANTERO')>⚽ Delantero</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-2xl font-semibold text-white">📊 Rendimiento</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-slate-300">Goles *</label>
                        <input class="player-input w-full rounded-xl px-4 py-3 mt-1" type="number" id="goles" name="goles" min="0" value="{{ old('goles', 0) }}">
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Asistencias *</label>
                        <input class="player-input w-full rounded-xl px-4 py-3 mt-1" type="number" id="asistencia" name="asistencia" min="0" value="{{ old('asistencia', 0) }}">
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-2xl font-semibold text-white">📸 Foto del Jugador <span class="text-xs text-slate-400">(Opcional)</span></h2>
                <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2 hover:file:bg-emerald-500/30" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                <p class="text-xs text-slate-500">Se guarda en <code>storage/app/public/jugadores</code> y en BD como ruta relativa.</p>
            </section>

            <div class="pt-5 border-t border-white/15 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-500">* Campos obligatorios</p>
                <div class="flex w-full sm:w-auto gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="flex-1 sm:flex-none text-center px-6 py-3 rounded-xl border border-amber-400/50 text-amber-300 hover:bg-amber-400/10">✖ Cancelar</a>
                    <button type="submit" class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold shadow-lg shadow-emerald-700/25">✔ Guardar Jugador</button>
                </div>
            </div>
        </form>

        <div class="player-wrap rounded-xl px-4 py-3 text-sm text-slate-300">
            💡 <span class="text-amber-300 font-medium">Tip:</span> Puedes agregar goles y asistencias después. El jugador aparecerá al instante en el plantel.
        </div>
    </div>
@endsection

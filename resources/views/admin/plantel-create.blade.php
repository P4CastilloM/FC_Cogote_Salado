@extends('layouts.admin')

@section('title', 'Agregar Jugador')
@section('subtitle', 'Registro del plantel')

@section('content')
    <div class="max-w-5xl mx-auto">
        @if(session('status') === 'player-created')
            <div class="mb-4 rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                ✅ Jugador guardado correctamente.
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">
                <p class="font-semibold mb-2">Revisa estos campos:</p>
                <ul class="list-disc ms-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.plantel.store') }}" enctype="multipart/form-data" class="glass-card rounded-2xl p-5 sm:p-8 space-y-8">
            @csrf

            <section>
                <h2 class="text-lg font-semibold text-white mb-4">👤 Datos del jugador</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="rut" class="block text-sm text-slate-300 mb-1">RUT (sin dígito verificador) *</label>
                        <input id="rut" name="rut" type="number" min="1" max="99999999" required value="{{ old('rut') }}"
                               class="w-full rounded-xl bg-black/20 border border-white/20 text-white px-4 py-3 focus:border-emerald-400 focus:ring-emerald-400/30">
                        <p class="text-xs text-slate-500 mt-1">Solo número, máximo 8 dígitos.</p>
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm text-slate-300 mb-1">Nombre *</label>
                        <input id="nombre" name="nombre" type="text" maxlength="25" required value="{{ old('nombre') }}"
                               class="w-full rounded-xl bg-black/20 border border-white/20 text-white px-4 py-3 focus:border-emerald-400 focus:ring-emerald-400/30">
                    </div>

                    <div>
                        <label for="numero_camiseta" class="block text-sm text-slate-300 mb-1">Número camiseta *</label>
                        <input id="numero_camiseta" name="numero_camiseta" type="number" min="1" max="65535" required value="{{ old('numero_camiseta') }}"
                               class="w-full rounded-xl bg-black/20 border border-white/20 text-white px-4 py-3 focus:border-emerald-400 focus:ring-emerald-400/30">
                    </div>

                    <div>
                        <label for="posicion" class="block text-sm text-slate-300 mb-1">Posición *</label>
                        <select id="posicion" name="posicion" required
                                class="w-full rounded-xl bg-black/20 border border-white/20 text-white px-4 py-3 focus:border-emerald-400 focus:ring-emerald-400/30">
                            <option value="">Seleccionar</option>
                            @foreach(['ARQUERO','DELANTERO','CENTRAL','DEFENSA'] as $pos)
                                <option value="{{ $pos }}" @selected(old('posicion') === $pos)>{{ $pos }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-white mb-4">📈 Rendimiento</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="goles" class="block text-sm text-slate-300 mb-1">Goles</label>
                        <input id="goles" name="goles" type="number" min="0" value="{{ old('goles', 0) }}"
                               class="w-full rounded-xl bg-black/20 border border-white/20 text-white px-4 py-3 focus:border-emerald-400 focus:ring-emerald-400/30">
                    </div>
                    <div>
                        <label for="asistencia" class="block text-sm text-slate-300 mb-1">Asistencias</label>
                        <input id="asistencia" name="asistencia" type="number" min="0" value="{{ old('asistencia', 0) }}"
                               class="w-full rounded-xl bg-black/20 border border-white/20 text-white px-4 py-3 focus:border-emerald-400 focus:ring-emerald-400/30">
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-white mb-4">📸 Foto (opcional)</h2>
                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp"
                       class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2 hover:file:bg-emerald-500/30">
                <p class="text-xs text-slate-500 mt-2">Se guarda en <code>storage/app/public/jugadores</code> y en BD como ruta relativa.</p>
            </section>

            <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-xl border border-amber-400/40 text-amber-300 text-center">Cancelar</a>
                <button type="submit" class="px-5 py-3 rounded-xl bg-emerald-500/80 hover:bg-emerald-500 text-white font-semibold">Guardar jugador</button>
            </div>
        </form>
    </div>
@endsection

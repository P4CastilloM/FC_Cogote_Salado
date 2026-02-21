@extends('layouts.admin')

@section('title', 'Editar Jugador')
@section('subtitle', 'Actualiza datos del jugador y elimina si es necesario')

@section('content')
    <style>
        .player-wrap { background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%); border: 1px solid rgba(212, 175, 55, 0.2); }
        .player-input { background: rgba(26, 10, 46, 0.45); border: 1px solid rgba(255,255,255,0.17); color: white; }
        .player-input:focus { border-color: rgba(16,185,129,.9); box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
        .player-select { background-color: rgba(26, 10, 46, 0.45); color: white; color-scheme: dark; }
        .player-select option { background: #1f1238; color: #f3f4f6; }
    </style>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status') === 'item-updated')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Jugador actualizado correctamente.</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">
                <ul class="list-disc ms-5 space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.plantel.update', $item->rut) }}" enctype="multipart/form-data" class="player-wrap rounded-2xl p-5 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="text-sm text-slate-300">RUT (no editable)</label>
                <input class="player-input w-full rounded-xl px-4 py-3 mt-1 opacity-60" type="text" value="{{ $item->rut }}" disabled>
            </div>

            <input class="player-input w-full rounded-xl px-4 py-3" type="text" name="nombre" maxlength="25" required value="{{ old('nombre', $item->nombre) }}" placeholder="👤 Nombre completo">
            <input class="player-input w-full rounded-xl px-4 py-3" type="text" name="sobrenombre" maxlength="25" value="{{ old('sobrenombre', $item->sobrenombre ?? '') }}" placeholder="✨ Sobrenombre (opcional)">
            <p class="col-span-full text-xs text-slate-400">Se mostrará este sobrenombre en plantel y plantilla si está cargado.</p>
            <input class="player-input w-full rounded-xl px-4 py-3" type="number" name="numero_camiseta" min="1" max="65535" required value="{{ old('numero_camiseta', $item->numero_camiseta) }}" placeholder="# Número camiseta">

            <select name="posicion" required class="player-input player-select w-full rounded-xl px-4 py-3">
                @foreach(['ARQUERO' => '🧤 Arquero','DEFENSA' => '🛡️ Defensa','MEDIOCAMPISTA' => '🎯 Mediocampista','DELANTERO' => '⚽ Delantero'] as $value => $label)
                    <option value="{{ $value }}" @selected(($value === 'MEDIOCAMPISTA' && in_array(old('posicion', $item->posicion), ['MEDIOCAMPISTA','CENTRAL'], true)) || old('posicion', $item->posicion) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input class="player-input w-full rounded-xl px-4 py-3" type="number" name="goles" min="0" value="{{ old('goles', $item->goles) }}" placeholder="⚽ Goles">
                <input class="player-input w-full rounded-xl px-4 py-3" type="number" name="asistencia" min="0" value="{{ old('asistencia', $item->asistencia) }}" placeholder="🎯 Asistencias">
            </div>

            <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp">

            <div class="pt-4 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.plantel.index') }}" class="text-center px-6 py-3 rounded-xl border border-white/20 text-slate-200">← Volver al buscador</a>
                <button class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">💾 Guardar cambios</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.plantel.destroy', $item->rut) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este jugador?')" class="player-wrap rounded-2xl p-5 border-red-500/40">
            @csrf
            @method('DELETE')
            <button class="w-full px-6 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-lg">🗑️ ELIMINAR JUGADOR</button>
        </form>
    </div>
@endsection

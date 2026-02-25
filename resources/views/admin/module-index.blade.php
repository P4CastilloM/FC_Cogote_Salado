@extends('layouts.admin')

@section('title', 'Editar / Eliminar '.$config['label'])
@section('subtitle', 'Busca y selecciona un registro para actualizar')

@section('content')
    <style>
        .module-wrap { background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%); border: 1px solid rgba(212, 175, 55, 0.2); }
        .module-input { background: rgba(26, 10, 46, 0.45); border: 1px solid rgba(255,255,255,0.17); color: white; }
        .module-input:focus { border-color: rgba(16,185,129,.9); box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    </style>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status') === 'item-deleted')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Registro eliminado correctamente.</div>
        @endif

        <form method="GET" action="{{ route('admin.'.$module.'.index') }}" class="module-wrap rounded-2xl p-5 sm:p-8 space-y-4">
            <h2 class="text-xl font-semibold">🔎 Buscar en {{ $config['label'] }}</h2>
            <input type="text" name="q" value="{{ $query }}" class="module-input w-full rounded-xl px-4 py-3" placeholder="Escribe RUT, nombre o clave...">
            <button class="px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">Buscar</button>
        </form>

        <div class="space-y-3">
            @forelse($items as $item)
                @php
                    $key = in_array($module, ['plantel', 'visitantes'], true) ? $item->rut : $item->id;
                    $main = match($module) {
                        'plantel' => $item->nombre,
                        'visitantes' => trim(($item->nombre ?? '').' '.($item->apellido ?? '')),
                        'noticias' => $item->titulo,
                        'avisos' => $item->titulo,
                        'partidos' => ($item->rival ?? 'Partido').' · '.$item->nombre_lugar,
                        'premios' => $item->nombre,
                        'temporadas' => $item->descripcion ?: 'Temporada #'.$item->id,
                        'staff', 'directiva' => trim($item->nombre.' '.($item->apellido ?? '')),
                        default => 'Registro #'.$key,
                    };
                    $secondary = match($module) {
                        'plantel' => 'RUT '.$item->rut,
                        'visitantes' => 'RUT '.$item->rut.' · '.(($item->sobrenombre ?? '') !== '' ? 'Alias '.$item->sobrenombre : 'Sin alias'),
                        'noticias', 'avisos' => $item->fecha,
                        'partidos' => trim(($item->fecha ?? '').' · '.($item->hora ?? '--:--')),
                        'premios' => $item->descripcion ?? 'Sin descripción',
                        'temporadas' => $item->fecha_inicio,
                        'staff' => $item->descripcion_rol ?? 'Sin rol',
                        'directiva' => ($item->descripcion_rol ?? 'Sin rol').(isset($item->prioridad) ? ' · Prioridad '.($item->prioridad ?? 10) : ''),
                        default => 'ID '.$key,
                    };
                @endphp

                @if($module === 'partidos')
                    <div class="module-wrap rounded-xl px-4 py-3">
                        <p class="text-white font-semibold">{{ $main }}</p>
                        <p class="text-xs text-slate-400">{{ $secondary }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('admin.partidos.edit', $key) }}" class="inline-flex items-center rounded-lg bg-white/10 hover:bg-white/20 px-3 py-2 text-xs text-white">✏️ Editar partido</a>
                            <a href="{{ route('admin.partidos.stats', $key) }}" class="inline-flex items-center rounded-lg bg-lime-500/20 hover:bg-lime-500/30 border border-lime-400/40 px-3 py-2 text-xs text-lime-200">📊 Agregar estadísticas</a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('admin.'.$module.'.edit', $key) }}" class="block module-wrap rounded-xl px-4 py-3 hover:border-emerald-400/50 transition">
                        <p class="text-white font-semibold">{{ $main }}</p>
                        <p class="text-xs text-slate-400">{{ $secondary }}</p>
                    </a>
                @endif
            @empty
                <div class="module-wrap rounded-xl px-4 py-4 text-slate-300">No hay resultados con ese filtro.</div>
            @endforelse
        </div>
    </div>
@endsection

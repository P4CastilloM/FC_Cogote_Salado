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
                <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
            @endif

            @if($module === 'partidos')
                <input class="module-input w-full rounded-xl px-4 py-3" type="date" name="fecha" required value="{{ old('fecha', $item->fecha) }}">
                <input class="module-input w-full rounded-xl px-4 py-3" type="text" name="nombre_lugar" maxlength="100" required value="{{ old('nombre_lugar', $item->nombre_lugar) }}" placeholder="📍 Lugar">
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
@endsection

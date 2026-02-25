@extends('layouts.admin')

@section('title', 'Editar Visitante')
@section('subtitle', 'Actualiza visitante o traspásalo a jugador oficial')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        @if(session('status') === 'item-updated')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Visitante actualizado.</div>
        @endif

        @if(session('status'))
            <div class="rounded-xl border border-lime-400/40 bg-lime-500/10 px-4 py-3 text-lime-200">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">
                <ul class="list-disc ms-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.visitantes.update', $item->rut) }}" class="rounded-2xl border border-white/10 bg-white/5 p-6 space-y-4">
            @csrf
            @method('PUT')

            <h2 class="text-xl font-semibold text-white">🧳 Visitante RUT {{ $item->rut }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-slate-300">Nombre *</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="text" name="nombre" maxlength="25" required value="{{ old('nombre', $item->nombre) }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Apellidos (opcional)</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="text" name="apellido" maxlength="50" value="{{ old('apellido', $item->apellido) }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Sobrenombre (opcional)</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="text" name="sobrenombre" maxlength="25" value="{{ old('sobrenombre', $item->sobrenombre) }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Goles</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="number" name="goles" min="0" value="{{ old('goles', $item->goles ?? 0) }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Asistencias</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="number" name="asistencia" min="0" value="{{ old('asistencia', $item->asistencia ?? 0) }}">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">💾 Guardar cambios</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.visitantes.transfer', $item->rut) }}" onsubmit="return confirm('¿Traspasar este visitante a jugador oficial del plantel?');" class="rounded-2xl border border-lime-400/30 bg-lime-500/10 p-5">
            @csrf
            <button class="w-full px-6 py-3 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-900 font-bold">🔁 Traspasar a jugador oficial</button>
        </form>

        <form method="POST" action="{{ route('admin.visitantes.destroy', $item->rut) }}" onsubmit="return confirm('¿Eliminar visitante?');" class="rounded-2xl border border-red-500/40 bg-red-500/10 p-5">
            @csrf
            @method('DELETE')
            <button class="w-full px-6 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">🗑️ Eliminar visitante</button>
        </form>
    </div>
@endsection

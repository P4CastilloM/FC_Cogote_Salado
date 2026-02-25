@extends('layouts.admin')

@section('title', 'Agregar Jugador Visitante')
@section('subtitle', 'Registra un visitante que puede confirmar asistencia y aparecer en plantilla')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        @if(session('status') === 'item-created')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Visitante guardado correctamente.</div>
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

        <form method="POST" action="{{ route('admin.visitantes.store') }}" class="rounded-2xl border border-white/10 bg-white/5 p-6 space-y-4">
            @csrf

            <h2 class="text-xl font-semibold text-white">🧳 Nuevo visitante</h2>
            <p class="text-sm text-slate-300">No aparecerá en el plantel público, pero podrá confirmar asistencia y sumar estadísticas.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-slate-300">RUT * (sin guión ni dígito)</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="number" name="rut" min="1" max="99999999" required value="{{ old('rut') }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Sobrenombre (opcional)</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="text" name="sobrenombre" maxlength="25" value="{{ old('sobrenombre') }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Nombre *</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="text" name="nombre" maxlength="25" required value="{{ old('nombre') }}">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Apellidos (opcional)</label>
                    <input class="w-full rounded-xl bg-slate-900/70 border border-white/15 text-white px-4 py-3 mt-1" type="text" name="apellido" maxlength="50" value="{{ old('apellido') }}">
                </div>
            </div>

            <button class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">💾 Guardar visitante</button>
        </form>
    </div>
@endsection

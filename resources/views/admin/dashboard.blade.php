@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Resumen general del club')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($stats as $card)
            <div class="glass-card rounded-2xl p-5">
                <p class="text-xs uppercase text-slate-400 tracking-wide">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $card['count'] }}</p>
                <p class="text-xl mt-3">{{ $card['icon'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="glass-card rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-semibold text-white">Acciones rápidas</h2>
        <p class="text-sm text-slate-400 mt-1">Estos botones ya conectan a controladores backend (JSON), listos para conectar con tus vistas finales.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4">
            <a class="quick-link" href="{{ route('admin.noticias.create') }}">Crear noticia</a>
            <a class="quick-link" href="{{ route('admin.avisos.create') }}">Crear aviso</a>
            <a class="quick-link" href="{{ route('admin.album.create') }}">Subir foto</a>
            <a class="quick-link" href="{{ route('admin.plantel.index') }}">Gestionar plantel</a>
            <a class="quick-link" href="{{ route('admin.partidos.index') }}">Gestionar partidos</a>
            <a class="quick-link" href="{{ route('admin.temporadas.index') }}">Gestionar temporadas</a>
        </div>
    </div>
@endsection

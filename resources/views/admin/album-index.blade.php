@extends('layouts.admin')

@section('title', 'Gestionar Álbum / Fotos')
@section('subtitle', 'Filtra por nombre o fecha de creación del álbum')

@section('content')
    <style>
        .module-wrap { background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%); border: 1px solid rgba(212, 175, 55, 0.2); }
        .module-input { background: rgba(26, 10, 46, 0.45); border: 1px solid rgba(255,255,255,0.17); color: white; }
        .module-input:focus { border-color: rgba(16,185,129,.9); box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    </style>

    <div class="max-w-6xl mx-auto space-y-6">
        @if(session('status') === 'item-deleted')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Eliminación realizada correctamente.</div>
        @endif

        @if(session('error') === 'delete-failed')
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">No se pudo eliminar el registro solicitado.</div>
        @endif

        <form method="GET" action="{{ route('admin.album.index') }}" class="module-wrap rounded-2xl p-5 sm:p-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-slate-300">🔎 Nombre álbum</label>
                <input type="text" name="album" value="{{ $query }}" class="module-input w-full rounded-xl px-4 py-3 mt-1" placeholder="Ej: Apertura 2026">
            </div>
            <div>
                <label class="text-sm text-slate-300">📅 Fecha creación álbum</label>
                <input type="date" name="album_date" value="{{ $albumDate }}" class="module-input w-full rounded-xl px-4 py-3 mt-1">
            </div>
            <div class="flex items-end gap-2">
                <button class="px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">Filtrar</button>
                <a href="{{ route('admin.album.index') }}" class="px-5 py-3 rounded-xl border border-amber-400/50 text-amber-300 hover:bg-amber-400/10">Limpiar</a>
            </div>
        </form>

        <div class="module-wrap rounded-2xl p-5 sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">📚 Álbumes</h2>
                <a href="{{ route('admin.album.create') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold">+ Subir fotos</a>
            </div>

            <div class="space-y-3">
                @forelse($albums as $album)
                    <div class="rounded-xl border border-white/15 bg-black/20 p-4 flex flex-wrap gap-3 items-center justify-between">
                        <div>
                            <p class="font-semibold text-white">{{ $album->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($album->created_at)->format('d/m/Y H:i') }} · {{ $album->total_fotos }} foto(s)</p>
                        </div>
                        <form method="POST" action="{{ route('admin.album.destroy', $album->id) }}?scope=album" onsubmit="return confirm('¿Eliminar álbum y todas sus fotos?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-4 py-2 rounded-xl border border-red-400/50 text-red-300 hover:bg-red-500/10 text-sm">Eliminar álbum</button>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-300">No hay álbumes que coincidan con el filtro.</p>
                @endforelse
            </div>
        </div>

        <div class="module-wrap rounded-2xl p-5 sm:p-8">
            <h2 class="text-xl font-semibold text-white mb-4">🖼️ Fotos</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3">
                @forelse($photos as $photo)
                    <div class="rounded-xl overflow-hidden border border-white/10 bg-black/20">
                        <img src="{{ asset('storage/'.$photo->path) }}" alt="Foto" class="w-full aspect-square object-cover">
                        <div class="p-2">
                            <p class="text-[11px] text-slate-300 truncate">{{ $photo->album_nombre ?: 'Sin álbum' }}</p>
                            <p class="text-[10px] text-slate-500 mb-2">{{ \Carbon\Carbon::parse($photo->created_at)->format('d/m/Y') }}</p>
                            <form method="POST" action="{{ route('admin.album.destroy', $photo->id) }}" onsubmit="return confirm('¿Eliminar esta foto?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-full px-2 py-1 rounded-lg border border-red-400/50 text-red-300 hover:bg-red-500/10 text-xs">Eliminar foto</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-slate-300">No hay fotos cargadas con ese filtro.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

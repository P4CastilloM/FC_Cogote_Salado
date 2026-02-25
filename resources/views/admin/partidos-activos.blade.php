@extends('layouts.admin')

@section('title', 'Partidos activos')
@section('subtitle', 'Listado de partidos con links de asistencia y su estado')

@section('content')
  <div class="space-y-6">
    @if(session('status'))
      <div class="rounded-xl border border-lime-400/30 bg-lime-500/10 p-3 text-lime-200 text-sm">{{ session('status') }}</div>
    @endif
    @if(session('error'))
      <div class="rounded-xl border border-red-400/30 bg-red-500/10 p-3 text-red-200 text-sm">{{ session('error') }}</div>
    @endif
    <div class="glass-card rounded-2xl p-6">
      <h2 class="text-lg font-semibold text-white">📅 Partidos activos / con link</h2>
      <p class="text-sm text-slate-400 mt-1">Comparte estos links por privado para confirmar asistencia.</p>

      <div class="space-y-3 mt-4">
        @forelse(($attendanceMatches ?? collect()) as $match)
          <div class="rounded-xl border border-white/10 bg-slate-900/40 p-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
              <div>
                <p class="text-white font-semibold">{{ \Carbon\Carbon::parse($match->fecha)->translatedFormat('d M Y') }} · vs {{ $match->rival ?? 'Rival por definir' }}</p>
                <p class="text-xs text-slate-400">{{ $match->nombre_lugar ?? 'Lugar por definir' }}</p>
                <p class="text-xs mt-1 {{ $match->is_active ? 'text-lime-300' : 'text-amber-300' }}">
                  {{ $match->is_active ? '✅ Link activo' : '⏳ Link fuera de ventana activa' }} · Confirmados: {{ (int) $match->confirmed_count }}
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                @if($match->attendance_url)
                  <a href="{{ $match->attendance_url }}" target="_blank" class="px-3 py-2 rounded-lg border border-lime-400/40 text-lime-300 bg-lime-500/10 hover:bg-lime-500/20 text-sm">Abrir link</a>
                  <button class="px-3 py-2 rounded-lg border border-sky-400/40 text-sky-300 bg-sky-500/10 hover:bg-sky-500/20 text-sm" onclick="navigator.clipboard.writeText('{{ $match->attendance_url }}')">Copiar link</button>
                @endif
              </div>
            </div>

            <div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Confirmados del partido</p>
              @if(collect($match->confirmed_players ?? [])->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                  @foreach($match->confirmed_players as $player)
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-slate-950/40 px-3 py-1 text-xs text-slate-100">
                      <span>{{ $player['name'] }}</span>
                      @if($player['is_visitante'])
                        <span class="rounded-full bg-sky-500/20 px-2 py-0.5 text-[10px] text-sky-200">Visita</span>
                      @endif
                      <form method="POST" action="{{ route('admin.partidos.confirmados.destroy', ['partidoId' => $match->id, 'jugadorRut' => $player['rut']]) }}" onsubmit="return confirm('¿Retirar a este jugador de este partido?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full bg-red-500/20 px-2 py-0.5 text-[10px] text-red-200 hover:bg-red-500/30">Retirar</button>
                      </form>
                    </div>
                  @endforeach
                </div>
              @else
                <p class="text-sm text-slate-400">Aún no hay jugadores confirmados en este partido.</p>
              @endif
            </div>
          </div>
        @empty
          <p class="text-sm text-slate-400">No hay partidos con link de asistencia todavía.</p>
        @endforelse
      </div>
    </div>

    <div class="glass-card rounded-2xl p-6 space-y-4">
      <h3 class="text-sm font-semibold text-white">🧾 Historial de checks</h3>

      <form method="GET" action="{{ route('admin.partidos.activos') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="text-xs text-slate-300">Buscar</label>
          <input type="text" name="checks_q" value="{{ $checksSearch ?? '' }}" placeholder="Jugador o rival" class="mt-1 w-full rounded-xl bg-slate-900/60 border border-white/15 text-slate-100 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-slate-300">Orden fecha</label>
          <select name="checks_order" class="mt-1 w-full rounded-xl bg-slate-900/60 border border-white/15 text-slate-100 px-3 py-2 text-sm">
            <option value="recent" @selected(($checksOrder ?? 'recent') === 'recent')>Más recientes</option>
            <option value="oldest" @selected(($checksOrder ?? 'recent') === 'oldest')>Más antiguas</option>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-300">Ver</label>
          <select name="checks_per_page" class="mt-1 w-full rounded-xl bg-slate-900/60 border border-white/15 text-slate-100 px-3 py-2 text-sm">
            @foreach([10, 25, 50] as $size)
              <option value="{{ $size }}" @selected((int) ($checksPerPage ?? 10) === $size)>Ver {{ $size }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button class="w-full px-3 py-2 rounded-xl border border-emerald-400/40 bg-emerald-500/10 text-emerald-200 text-sm">Aplicar</button>
          <a href="{{ route('admin.partidos.activos') }}" class="px-3 py-2 rounded-xl border border-white/20 text-slate-300 text-sm">Limpiar</a>
        </div>
      </form>

      <div class="overflow-auto rounded-xl border border-white/10">
        <table class="w-full text-sm">
          <thead class="bg-slate-900/50 text-slate-300">
            <tr>
              <th class="text-left px-3 py-2">Fecha</th>
              <th class="text-left px-3 py-2">Acción</th>
              <th class="text-left px-3 py-2">Partido</th>
            </tr>
          </thead>
          <tbody>
            @if(($attendanceLogs ?? collect())->isNotEmpty())
              @foreach(($attendanceLogs ?? collect()) as $log)
                @php
                  $actor = trim((string) ($log->actor_sobrenombre ?? $log->actor_nombre ?? 'Jugador'));
                  $target = trim((string) ($log->target_sobrenombre ?? $log->target_nombre ?? 'Jugador'));
                @endphp
                <tr class="border-t border-white/5 text-slate-200">
                  <td class="px-3 py-2">{{ \Carbon\Carbon::parse($log->checked_at)->translatedFormat('d M Y H:i') }}</td>
                  <td class="px-3 py-2">{{ $actor }} hizo check por {{ $target }}</td>
                  <td class="px-3 py-2">{{ \Carbon\Carbon::parse($log->fecha)->translatedFormat('d M') }} · {{ $log->rival ?? 'Rival' }}</td>
                </tr>
              @endforeach
            @else
              <tr><td class="px-3 py-3 text-slate-400" colspan="3">Sin historial de checks por ahora.</td></tr>
            @endif
          </tbody>
        </table>
      </div>

      @if(method_exists($attendanceLogs, 'links'))
        <div class="pt-1">
          {{ $attendanceLogs->onEachSide(1)->links() }}
        </div>
      @endif

      <p class="text-xs text-slate-400">Se muestran y filtran solo los 500 checks más recientes.</p>
    </div>
  </div>
@endsection

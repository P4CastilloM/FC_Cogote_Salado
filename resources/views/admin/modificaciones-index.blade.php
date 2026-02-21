@extends('layouts.admin')

@section('title', 'Historial de Cambios')
@section('subtitle', 'Registro de cambios por administradores y ayudantes')

@section('content')
    <style>
        .mod-wrap { background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%); border: 1px solid rgba(212, 175, 55, 0.2); }
        .mod-input { background: rgba(26, 10, 46, 0.45); border: 1px solid rgba(255,255,255,0.17); color: white; }
        .mod-input:focus { border-color: rgba(16,185,129,.9); box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    </style>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="mod-wrap rounded-2xl p-5 sm:p-6 space-y-4">
            <form method="GET" action="{{ route('admin.modificaciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input id="log-search" type="text" name="q" value="{{ $search }}" placeholder="Buscar por usuario, módulo o detalle..." class="mod-input rounded-xl px-4 py-3 md:col-span-2">

                <select name="action" class="mod-input rounded-xl px-4 py-3">
                    <option value="">Todas las acciones</option>
                    @foreach(['añadir' => 'Añadir', 'actualizar' => 'Actualizar', 'eliminar' => 'Eliminar'] as $k => $label)
                        <option value="{{ $k }}" @selected($action === $k)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="order" class="mod-input rounded-xl px-4 py-3">
                    <option value="recent" @selected($order === 'desc')>Más recientes</option>
                    <option value="oldest" @selected($order === 'asc')>Más antiguas</option>
                </select>

                <div class="md:col-span-4 flex gap-3">
                    <button class="px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">Aplicar filtros</button>
                    <a href="{{ route('admin.modificaciones.index') }}" class="px-5 py-3 rounded-xl border border-white/20 text-slate-200">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="mod-wrap rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-white/5 text-slate-300">
                        <tr>
                            <th class="text-left px-4 py-3">Fecha</th>
                            <th class="text-left px-4 py-3">Acción</th>
                            <th class="text-left px-4 py-3">Módulo</th>
                            <th class="text-left px-4 py-3">Quién lo hizo</th>
                            <th class="text-left px-4 py-3">Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table">
                        @forelse($logs as $log)
                            <tr class="border-t border-white/10 log-row" data-search="{{ strtolower(($log->actor_name ?? '').' '.($log->module ?? '').' '.($log->summary ?? '').' '.($log->item_key ?? '')) }}">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-200">{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($log->action) {
                                            'añadir' => 'bg-emerald-500/20 text-emerald-300',
                                            'actualizar' => 'bg-amber-500/20 text-amber-300',
                                            'eliminar' => 'bg-red-500/20 text-red-300',
                                            default => 'bg-slate-500/20 text-slate-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs {{ $badge }}">{{ ucfirst($log->action) }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-200">{{ ucfirst($log->module) }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $log->actor_name }} <span class="text-xs text-slate-400">({{ $log->actor_role ?? 'sin rol' }})</span></td>
                                <td class="px-4 py-3 text-slate-300">{{ $log->summary ?: '-' }} @if($log->item_key)<span class="text-xs text-slate-500">#{{ $log->item_key }}</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No hay modificaciones registradas todavía.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const input = document.getElementById('log-search');
        const rows = Array.from(document.querySelectorAll('.log-row'));

        input?.addEventListener('input', (e) => {
            const q = (e.target.value || '').toLowerCase().trim();
            rows.forEach((row) => {
                const hay = row.dataset.search || '';
                row.style.display = hay.includes(q) ? '' : 'none';
            });
        });
    </script>
@endsection

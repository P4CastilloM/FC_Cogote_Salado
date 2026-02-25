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
            <form method="GET" action="{{ route('admin.modificaciones.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs text-slate-300">Buscar</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Usuario, módulo, detalle..." class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                </div>

                <div>
                    <label class="text-xs text-slate-300">Acción (filtro)</label>
                    <select name="action" class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                        <option value="">Todas</option>
                        @foreach(['añadir' => 'Añadir', 'actualizar' => 'Actualizar', 'eliminar' => 'Eliminar', 'traspasar' => 'Traspasar'] as $k => $label)
                            <option value="{{ $k }}" @selected($action === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Módulo (filtro)</label>
                    <select name="module_filter" class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                        <option value="">Todos</option>
                        @foreach(($modulesFilterOptions ?? collect()) as $moduleOption)
                            <option value="{{ $moduleOption }}" @selected($moduleFilter === $moduleOption)>{{ ucfirst($moduleOption) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Quién lo hizo (filtro)</label>
                    <input type="text" name="actor_filter" value="{{ $actorFilter }}" placeholder="Nombre de usuario" class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                </div>

                <div>
                    <label class="text-xs text-slate-300">Ordenar por</label>
                    <select name="sort_by" class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                        <option value="created_at" @selected($sortBy === 'created_at')>Fecha</option>
                        <option value="action" @selected($sortBy === 'action')>Acción</option>
                        <option value="module" @selected($sortBy === 'module')>Módulo</option>
                        <option value="actor_name" @selected($sortBy === 'actor_name')>Quién lo hizo</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Dirección</label>
                    <select name="sort_dir" class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                        <option value="desc" @selected($sortDir === 'desc')>Descendente</option>
                        <option value="asc" @selected($sortDir === 'asc')>Ascendente</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Ver</label>
                    <select name="per_page" class="mod-input rounded-xl px-4 py-3 mt-1 w-full">
                        @foreach([10,25,50] as $size)
                            <option value="{{ $size }}" @selected((int) $perPage === $size)>Ver {{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-3 items-end">
                    <button class="px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold">Aplicar filtros</button>
                    <a href="{{ route('admin.modificaciones.index') }}" class="px-5 py-3 rounded-xl border border-white/20 text-slate-200">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="mod-wrap rounded-2xl p-4 sm:p-5 space-y-4">
            <div class="overflow-x-auto rounded-xl border border-white/10">
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
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="border-t border-white/10">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-200">{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($log->action) {
                                            'añadir' => 'bg-emerald-500/20 text-emerald-300',
                                            'actualizar' => 'bg-amber-500/20 text-amber-300',
                                            'eliminar' => 'bg-red-500/20 text-red-300',
                                            'traspasar' => 'bg-sky-500/20 text-sky-300',
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

            @if(method_exists($logs, 'links'))
                <div>
                    {{ $logs->onEachSide(1)->links() }}
                </div>
            @endif

            <p class="text-xs text-slate-400">Se muestran y filtran solo los 500 cambios más recientes.</p>
        </div>
    </div>
@endsection

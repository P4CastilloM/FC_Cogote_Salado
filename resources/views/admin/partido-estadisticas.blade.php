@extends('layouts.admin')

@section('title', 'Agregar estadísticas del partido')
@section('subtitle', 'Suma o resta goles, asistencias y atajadas solo para confirmados')

@section('content')
    <div class="max-w-5xl mx-auto space-y-4" x-data="statsApp()">
        @if(session('status'))
            <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
        @endif

        <div class="glass-card rounded-2xl p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">📊 {{ $partido->rival ?? 'Partido' }} · {{ $partido->nombre_lugar ?? 'Sin lugar' }}</h2>
                    <p class="text-sm text-slate-300 mt-1">
                        Ventana activa: {{ $windowStart->translatedFormat('d M H:i') }} → {{ $windowEnd->translatedFormat('d M H:i') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-2">Solo aparecen jugadores confirmados. La participación queda marcada automáticamente dentro de esta ventana.</p>
                </div>
                <form method="POST" action="{{ route('admin.partidos.stats.finish', $partido->id) }}" onsubmit="return confirm('¿Cerrar partido y sumar estadísticas al plantel? Esta acción no se puede repetir.');">
                    @csrf
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl border border-amber-400/40 bg-amber-500/10 hover:bg-amber-500/20 text-amber-200 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                        @disabled(!empty($statsClosedAt))
                    >
                        {{ !empty($statsClosedAt) ? '✅ Partido cerrado' : '🏁 Finalizar partido' }}
                    </button>
                </form>
            </div>

            <div class="mt-4">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Buscar jugador por nombre o sobrenombre..."
                    class="w-full rounded-xl bg-slate-900/70 border border-white/10 text-white px-4 py-3 focus:ring-2 focus:ring-lime-400"
                >
            </div>

            <div class="mt-3 text-xs text-slate-400 flex flex-wrap items-center gap-2">
                <span x-show="!online" class="inline-flex items-center rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 px-2 py-1">Sin conexión: guardando cambios localmente</span>
                <span x-show="online" class="inline-flex items-center rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 px-2 py-1">En línea</span>
                <span class="inline-flex items-center rounded-full bg-sky-500/15 border border-sky-400/30 text-sky-300 px-2 py-1" x-text="`Pendientes: ${queue.length}`"></span>
                <span class="inline-flex items-center rounded-full bg-rose-500/15 border border-rose-400/30 text-rose-300 px-2 py-1" x-show="errorMessage" x-text="errorMessage"></span>
            </div>
        </div>

        <template x-if="filteredPlayers().length === 0">
            <div class="glass-card rounded-2xl p-5 text-slate-300 text-sm">No hay jugadores confirmados que coincidan con la búsqueda.</div>
        </template>

        <div class="space-y-3">
            <template x-for="player in filteredPlayers()" :key="player.rut">
                <div class="glass-card rounded-2xl p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-white font-semibold" x-text="displayName(player)"></p>
                            <p class="text-xs text-slate-400" x-text="meta(player)"></p>
                        </div>
                        <span class="text-[11px] px-2 py-1 rounded-full border border-lime-400/30 bg-lime-500/10 text-lime-300">Participando</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                        <template x-for="field in fields" :key="field.key">
                            <div class="rounded-xl border border-white/10 bg-slate-900/50 p-2">
                                <p class="text-xs text-slate-400" x-text="field.label"></p>
                                <p class="text-2xl font-bold text-white my-2" x-text="player[field.key]"></p>
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="apply(player.rut, field.key, -1)" class="w-9 h-9 rounded-lg bg-rose-500/20 border border-rose-400/30 text-rose-300" :disabled="closed">−</button>
                                    <button type="button" @click="apply(player.rut, field.key, 1)" class="w-9 h-9 rounded-lg bg-lime-500/20 border border-lime-400/30 text-lime-300" :disabled="closed">+</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function statsApp() {
    return {
        endpoint: @json(route('admin.partidos.stats.update', $partido->id)),
        dataEndpoint: @json(route('admin.partidos.stats.data', $partido->id)),
        channelName: @json('partido-stats-'.$partido->id),
        storageKey: @json('partido-stats-queue-'.$partido->id),
        online: navigator.onLine,
        search: '',
        closed: @json(!empty($statsClosedAt)),
        syncing: false,
        errorMessage: '',
        fields: [
            { key: 'goles', label: 'Gol' },
            { key: 'asistencias', label: 'Asistencia' },
            { key: 'atajadas', label: 'Atajada' },
        ],
        players: @json($players),
        queue: [],
        pollTimer: null,
        channel: null,

        init() {
            this.queue = this.readQueue();
            this.bindConnectivity();
            this.flushQueue();
            this.refreshFromServer();
            this.startPolling();
            this.bindCrossTabSync();
        },

        bindConnectivity() {
            window.addEventListener('online', () => {
                this.online = true;
                this.flushQueue();
                this.refreshFromServer();
            });
            window.addEventListener('offline', () => {
                this.online = false;
            });

            window.addEventListener('storage', (event) => {
                if (event.key !== this.storageKey) return;
                this.queue = this.readQueue();
                this.flushQueue();
            });
        },

        filteredPlayers() {
            const term = this.search.trim().toLowerCase();
            if (!term) return this.players;

            return this.players.filter((player) => {
                const n = (player.nombre || '').toLowerCase();
                const s = (player.sobrenombre || '').toLowerCase();
                return n.includes(term) || s.includes(term);
            });
        },

        displayName(player) {
            return player.sobrenombre && player.sobrenombre.trim() !== ''
                ? `${player.sobrenombre} (${player.nombre})`
                : player.nombre;
        },

        meta(player) {
            const pos = player.posicion || 'Sin posición';
            const num = player.numero_camiseta ? `#${player.numero_camiseta}` : '#--';
            return `${num} · ${pos}`;
        },

        apply(rut, field, delta) {
            if (this.closed) return;

            const player = this.players.find((item) => Number(item.rut) === Number(rut));
            if (!player) return;

            const current = Number(player[field] || 0);
            const next = Math.max(0, current + delta);
            const effectiveDelta = next - current;
            if (effectiveDelta === 0) return;

            this.errorMessage = '';
            player[field] = next;

            const payload = {
                operation_id: this.newOperationId(),
                jugador_rut: Number(rut),
                field,
                delta: effectiveDelta,
            };
            this.queue.push(payload);
            this.saveQueue();
            this.flushQueue();
            this.publishCrossTab(payload);
        },

        readQueue() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                const parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        },

        saveQueue() {
            localStorage.setItem(this.storageKey, JSON.stringify(this.queue));
        },



        bindCrossTabSync() {
            if (!('BroadcastChannel' in window)) return;

            this.channel = new BroadcastChannel(this.channelName);
            this.channel.onmessage = () => {
                this.queue = this.readQueue();
                this.flushQueue();
                this.refreshFromServer();
            };
        },

        publishCrossTab(payload) {
            if (!this.channel) return;
            this.channel.postMessage({ type: 'queued', payload });
        },

        newOperationId() {
            if (window.crypto && window.crypto.randomUUID) {
                return window.crypto.randomUUID();
            }

            return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
        },

        startPolling() {
            if (this.pollTimer) return;
            this.pollTimer = setInterval(() => {
                this.refreshFromServer();
            }, 2500);
        },

        async refreshFromServer() {
            if (!this.online) return;

            try {
                const response = await fetch(this.dataEndpoint, {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) return;
                const data = await response.json();
                if (!data || !data.ok) return;

                this.closed = Boolean(data.closed);
                if (Array.isArray(data.players)) {
                    this.players = data.players;
                }
            } catch (_) {}
        },

        async flushQueue() {
            if (this.syncing || !this.online || this.queue.length === 0) return;
            this.syncing = true;

            while (this.online && this.queue.length > 0) {
                const payload = this.queue[0];

                try {
                    const response = await fetch(this.endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        let message = 'No se pudo sincronizar el cambio';
                        try {
                            const data = await response.json();
                            if (data && data.message) message = data.message;
                        } catch (_) {}
                        this.errorMessage = message;
                        break;
                    }

                    this.queue.shift();
                    this.errorMessage = '';
                    this.saveQueue();
                    await this.refreshFromServer();
                } catch (error) {
                    this.errorMessage = 'Sin conexión o error de red';
                    this.online = navigator.onLine;
                    break;
                }
            }

            this.syncing = false;
        },
    };
}
</script>
@endpush

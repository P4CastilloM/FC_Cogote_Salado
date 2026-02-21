@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Resumen general del club y visitas de la web')

@section('content')
    @php($isAdmin = Auth::user()?->isAdmin() ?? false)

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($stats as $card)
            <div class="glass-card rounded-2xl p-5">
                <p class="text-xs uppercase text-slate-400 tracking-wide">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $card['count'] }}</p>
                <p class="text-xl mt-3">{{ $card['icon'] }}</p>
            </div>
        @endforeach
    </div>


    @if(session('status'))
        <div class="mt-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mt-4 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">📱 Dispositivos únicos hoy</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $uniqueSummary['today'] }}</p>
            <p class="text-xs text-slate-500 mt-2">Visitas totales hoy: {{ $visitSummary['today'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">📆 Dispositivos únicos del mes</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $uniqueSummary['month'] }}</p>
            <p class="text-xs text-slate-500 mt-2">Visitas totales mes: {{ $visitSummary['month'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">🗓️ Dispositivos únicos del año</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $uniqueSummary['year'] }}</p>
            <p class="text-xs text-slate-500 mt-2">Visitas totales año: {{ $visitSummary['year'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">📊 Dispositivos hoy por categoría</p>
            <p class="text-3xl font-bold text-white mt-2">{{ array_sum(array_column($deviceSeries, 'total')) }}</p>
            <p class="text-xs text-slate-500 mt-2">Agrupado en móvil/tablet/escritorio/otro</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-5 mt-6">
        <h2 class="text-lg font-semibold text-white mb-3">📈 Dispositivos que entran (desde hoy)</h2>
        <p class="text-sm text-slate-400 mb-4">Este gráfico ignora los datos históricos anteriores a hoy para partir desde 0.</p>
        <div class="h-72">
            <canvas id="deviceVisitsChart"></canvas>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-5 mt-6">
        <h2 class="text-lg font-semibold text-white mb-3">📉 Dispositivos únicos por día (últimos 30 días)</h2>
        <p class="text-sm text-slate-400 mb-4">Mismo enfoque del gráfico anterior, pero deduplicando por dispositivo para no contar tus recargas repetidas.</p>
        <div class="h-72">
            <canvas id="dailyUniqueDevicesChart"></canvas>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-semibold text-white">⚡ Acciones rápidas</h2>
        <p class="text-sm text-slate-400 mt-1">Controladores backend ya conectados para que luego agreguemos vistas CRUD una por una.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4">
            <a class="quick-link" href="{{ route('admin.noticias.create') }}">📰 Crear noticia</a>
            <a class="quick-link" href="{{ route('admin.avisos.create') }}">📢 Crear aviso</a>
            <a class="quick-link" href="{{ route('admin.album.create') }}">📸 Subir foto</a>
            <a class="quick-link" href="{{ route('admin.plantel.index') }}">👥 Gestionar plantel</a>
            <a class="quick-link" href="{{ route('admin.partidos.index') }}">📅 Gestionar partidos</a>
            @if($isAdmin)
                <a class="quick-link" href="{{ route('admin.temporadas.index') }}">⏳ Gestionar temporadas</a>
                <a class="quick-link" href="{{ route('admin.staff.index') }}">🤝 Gestionar ayudantes</a>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const deviceSeries = @json($deviceSeries);
        const dailyUniqueSeries = @json($dailyUniqueSeries);
        const chartTheme = {
            borderColor: '#84cc16',
            textColor: '#cbd5e1'
        };

        const pieOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 700,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    labels: {
                        color: chartTheme.textColor,
                    },
                }
            }
        };

        const deviceCtx = document.getElementById('deviceVisitsChart');
        if (deviceCtx) {
            new Chart(deviceCtx, {
                type: 'doughnut',
                data: {
                    labels: deviceSeries.map(item => item.label),
                    datasets: [{
                        label: 'Dispositivos únicos',
                        data: deviceSeries.map(item => item.total),
                        backgroundColor: [
                            'rgba(132, 204, 22, 0.85)',
                            'rgba(56, 189, 248, 0.85)',
                            'rgba(251, 191, 36, 0.85)',
                            'rgba(167, 139, 250, 0.85)',
                        ],
                        borderColor: 'rgba(15, 23, 42, 0.9)',
                        borderWidth: 2,
                    }]
                },
                options: pieOptions
            });
        }

        const dailyUniqueCtx = document.getElementById('dailyUniqueDevicesChart');
        if (dailyUniqueCtx) {
            new Chart(dailyUniqueCtx, {
                type: 'line',
                data: {
                    labels: dailyUniqueSeries.map(item => item.label),
                    datasets: [{
                        label: 'Dispositivos únicos',
                        data: dailyUniqueSeries.map(item => item.total),
                        borderColor: '#84cc16',
                        backgroundColor: 'rgba(132, 204, 22, 0.2)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { labels: { color: chartTheme.textColor } }
                    },
                    scales: {
                        x: {
                            ticks: { color: chartTheme.textColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: chartTheme.textColor }
                        }
                    }
                }
            });
        }
    </script>
@endpush

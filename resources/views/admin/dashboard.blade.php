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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">👀 Visitas hoy</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $visitSummary['today'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">📆 Visitas del mes</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $visitSummary['month'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">🗓️ Visitas del año</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $visitSummary['year'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs uppercase text-slate-400 tracking-wide">📱 Dispositivos únicos (desde hoy)</p>
            <p class="text-3xl font-bold text-white mt-2">{{ $visitSummary['unique_devices_since_today'] }}</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-5 mt-6">
        <h2 class="text-lg font-semibold text-white mb-3">📈 Dispositivos que entran (desde hoy)</h2>
        <p class="text-sm text-slate-400 mb-4">Este gráfico ignora los datos históricos anteriores a hoy para partir desde 0.</p>
        <div class="h-72">
            <canvas id="deviceVisitsChart"></canvas>
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
    </script>
@endpush

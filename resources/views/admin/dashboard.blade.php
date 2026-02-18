@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Resumen general del club y visitas de la web')

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
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <div class="glass-card rounded-2xl p-5">
            <h2 class="text-lg font-semibold text-white mb-3">📈 Visitas por día (últimos 30 días)</h2>
            <canvas id="dailyVisitsChart" height="120"></canvas>
        </div>

        <div class="glass-card rounded-2xl p-5">
            <h2 class="text-lg font-semibold text-white mb-3">📊 Visitas por mes (últimos 12 meses)</h2>
            <canvas id="monthlyVisitsChart" height="120"></canvas>
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
            <a class="quick-link" href="{{ route('admin.temporadas.index') }}">⏳ Gestionar temporadas</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const dailySeries = @json($dailySeries);
        const monthlySeries = @json($monthlySeries);

        const chartTheme = {
            borderColor: '#84cc16',
            backgroundColor: 'rgba(132, 204, 22, 0.25)',
            textColor: '#cbd5e1'
        };

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {display: false}
            },
            scales: {
                x: {
                    ticks: {color: chartTheme.textColor}
                },
                y: {
                    beginAtZero: true,
                    ticks: {color: chartTheme.textColor}
                }
            }
        };

        const dailyCtx = document.getElementById('dailyVisitsChart');
        if (dailyCtx) {
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: dailySeries.map(item => item.label),
                    datasets: [{
                        data: dailySeries.map(item => item.total),
                        borderColor: chartTheme.borderColor,
                        backgroundColor: chartTheme.backgroundColor,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: commonOptions
            });
        }

        const monthlyCtx = document.getElementById('monthlyVisitsChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: monthlySeries.map(item => item.label),
                    datasets: [{
                        data: monthlySeries.map(item => item.total),
                        borderColor: chartTheme.borderColor,
                        backgroundColor: chartTheme.backgroundColor,
                        borderWidth: 2,
                    }]
                },
                options: commonOptions
            });
        }
    </script>
@endpush

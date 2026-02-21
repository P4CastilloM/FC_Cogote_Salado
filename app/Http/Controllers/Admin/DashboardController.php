<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $cards = [
            ['key' => 'jugadores', 'label' => 'Jugadores', 'icon' => '⚽'],
            ['key' => 'noticias', 'label' => 'Noticias', 'icon' => '📰'],
            ['key' => 'avisos', 'label' => 'Avisos', 'icon' => '📢'],
            ['key' => 'partidos', 'label' => 'Partidos', 'icon' => '📅'],
            ['key' => 'premios', 'label' => 'Premios', 'icon' => '🏆'],
            ['key' => 'temporadas', 'label' => 'Temporadas', 'icon' => '⏳'],
            ['key' => 'ayudantes', 'label' => 'Ayudantes', 'icon' => '🤝'],
        ];

        $stats = collect($cards)->map(function (array $card) {
            $count = Schema::hasTable($card['key'])
                ? DB::table($card['key'])->count()
                : 0;

            return [
                ...$card,
                'count' => $count,
            ];
        });

        $visitSummary = [
            'today' => 0,
            'month' => 0,
            'year' => 0,
            'unique_devices_since_today' => 0,
        ];

        $deviceSeries = [];
        if (Schema::hasTable('page_visits')) {
            $today = now()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();
            $yearStart = now()->startOfYear()->toDateString();

            $visitSummary = [
                'today' => DB::table('page_visits')->whereDate('visited_on', $today)->count(),
                'month' => DB::table('page_visits')->whereDate('visited_on', '>=', $monthStart)->count(),
                'year' => DB::table('page_visits')->whereDate('visited_on', '>=', $yearStart)->count(),
                'unique_devices_since_today' => DB::table('page_visits')
                    ->whereDate('visited_on', '>=', $today)
                    ->selectRaw("COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) AS total")
                    ->value('total') ?? 0,
            ];

            $deviceSeries = DB::table('page_visits')
                ->selectRaw("\n                    CASE\n                        WHEN user_agent LIKE '%Mobile%' AND user_agent NOT LIKE '%Tablet%' THEN 'Móvil'\n                        WHEN user_agent LIKE '%Tablet%' OR user_agent LIKE '%iPad%' THEN 'Tablet'\n                        WHEN user_agent LIKE '%Windows%' OR user_agent LIKE '%Macintosh%' OR user_agent LIKE '%Linux%' THEN 'Escritorio'\n                        ELSE 'Otro'\n                    END AS label,\n                    COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) AS total\n                ")
                ->whereDate('visited_on', '>=', $today)
                ->groupBy('label')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'label' => $row->label,
                    'total' => (int) $row->total,
                ])
                ->all();
        }

        return view('admin.dashboard', [
            'stats' => $stats,
            'visitSummary' => $visitSummary,
            'deviceSeries' => $deviceSeries,
        ]);
    }
}

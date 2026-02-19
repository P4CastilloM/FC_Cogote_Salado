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
        ];

        $dailySeries = [];
        if (Schema::hasTable('page_visits')) {
            $today = now()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();
            $yearStart = now()->startOfYear()->toDateString();

            $visitSummary = [
                'today' => DB::table('page_visits')->whereDate('visited_on', $today)->count(),
                'month' => DB::table('page_visits')->whereDate('visited_on', '>=', $monthStart)->count(),
                'year' => DB::table('page_visits')->whereDate('visited_on', '>=', $yearStart)->count(),
            ];

            $dailySeries = DB::table('page_visits')
                ->selectRaw('visited_on as label, COUNT(*) as total')
                ->whereDate('visited_on', '>=', now()->subDays(29)->toDateString())
                ->groupBy('visited_on')
                ->orderBy('visited_on')
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
            'dailySeries' => $dailySeries,
        ]);
    }
}

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

        return view('admin.dashboard', [
            'stats' => $stats,
        ]);
    }
}

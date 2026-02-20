<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirectivaController extends Controller
{
    public function index()
    {
        $hasPriority = Schema::hasColumn('ayudantes', 'prioridad');

        $directiva = DB::table('ayudantes')
            ->where('activo', true)
            ->when($hasPriority, fn ($query) => $query->orderBy('prioridad'))
            ->orderBy('id')
            ->get()
            ->map(function ($item) use ($hasPriority) {
                $item->full_name = trim(($item->nombre ?? '').' '.($item->apellido ?? ''));
                $item->rol = $item->descripcion_rol ?: 'Integrante';
                $item->foto_url = $item->foto ? asset('storage/'.$item->foto) : null;
                $item->prioridad = $hasPriority ? (int) ($item->prioridad ?? 10) : 10;

                if ($item->prioridad < 1 || $item->prioridad > 10) {
                    $item->prioridad = 10;
                }

                return $item;
            });

        $lineups = $this->buildLineups($directiva);

        return view('public.directiva', [
            'lineups' => $lineups,
            'totalMembers' => $directiva->count(),
        ]);
    }

    /**
     * @param Collection<int, object> $directiva
     * @return Collection<int, object>
     */
    private function buildLineups(Collection $directiva): Collection
    {
        return $directiva
            ->groupBy('prioridad')
            ->sortKeys()
            ->values()
            ->map(function (Collection $group, int $index) {
                $pair = $group->take(2)->values();

                return (object) [
                    'index' => $index,
                    'left' => $pair->get(0),
                    'right' => $pair->get(1),
                    'extraCount' => max(0, $group->count() - 2),
                ];
            });
    }
}

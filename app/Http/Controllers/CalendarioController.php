<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CalendarioController extends Controller
{
    public function index()
    {
        $partidos = DB::table('partidos')
            ->leftJoin('temporadas', 'temporadas.id', '=', 'partidos.temporada_id')
            ->select('partidos.*', 'temporadas.descripcion as temporada_descripcion')
            ->orderBy('partidos.fecha')
            ->get();

        $confirmedByMatch = DB::table('partido_asistencias as pa')
            ->join('jugadores as j', 'j.rut', '=', 'pa.jugador_rut')
            ->select('pa.partido_id', 'j.sobrenombre', 'j.nombre')
            ->orderBy('pa.partido_id')
            ->orderByRaw("COALESCE(NULLIF(j.sobrenombre, ''), j.nombre) asc")
            ->get()
            ->groupBy('partido_id')
            ->map(function ($rows) {
                return collect($rows)
                    ->map(fn ($row) => trim((string) ($row->sobrenombre ?: $row->nombre)))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            });

        $partidosData = $partidos->map(function ($partido) use ($confirmedByMatch) {
            return [
                'id' => (int) $partido->id,
                'fecha' => $partido->fecha,
                'rival' => $partido->rival ?? 'Rival por confirmar',
                'hora' => filled($partido->hora) ? Str::of((string) $partido->hora)->substr(0, 5)->toString() : null,
                'ubicacion' => $partido->nombre_lugar,
                'direccion' => $partido->direccion,
                'temporada' => $partido->temporada_descripcion,
                'confirmados' => $confirmedByMatch->get((int) $partido->id, []),
            ];
        })->values()->all();

        return view('public.calendario', [
            'partidos' => $partidos,
            'partidosData' => $partidosData,
        ]);
    }
}

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

        $partidosData = $partidos->map(function ($partido) {
            return [
                'fecha' => $partido->fecha,
                'rival' => $partido->rival ?? 'Rival por confirmar',
                'hora' => filled($partido->hora) ? Str::of((string) $partido->hora)->substr(0, 5)->toString() : null,
                'ubicacion' => $partido->nombre_lugar,
                'direccion' => $partido->direccion,
                'temporada' => $partido->temporada_descripcion,
            ];
        })->values()->all();

        return view('public.calendario', [
            'partidos' => $partidos,
            'partidosData' => $partidosData,
        ]);
    }
}

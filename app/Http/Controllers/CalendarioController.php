<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    public function index()
    {
        $partidos = DB::table('partidos')
            ->leftJoin('temporadas', 'temporadas.id', '=', 'partidos.temporada_id')
            ->select('partidos.*', 'temporadas.descripcion as temporada_descripcion')
            ->orderBy('partidos.fecha')
            ->get();

        return view('public.calendario', [
            'partidos' => $partidos,
        ]);
    }
}

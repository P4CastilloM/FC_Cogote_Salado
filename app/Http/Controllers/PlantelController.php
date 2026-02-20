<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PlantelController extends Controller
{
    public function index()
    {
        $jugadores = DB::table('jugadores')
            ->select('rut', 'nombre', 'foto', 'goles', 'asistencia', 'numero_camiseta', 'posicion')
            ->orderBy('numero_camiseta')
            ->get()
            ->map(function ($jugador) {
                $jugador->display_name = trim((string) ($jugador->nombre ?? 'Jugador sin nombre'));
                $jugador->foto_url = ! empty($jugador->foto) ? asset('storage/'.$jugador->foto) : null;
                $jugador->posicion_label = match ($jugador->posicion) {
                    'ARQUERO' => 'Arquero',
                    'DEFENSA' => 'Defensa',
                    'MEDIOCAMPISTA', 'CENTRAL' => 'Mediocampista',
                    'DELANTERO' => 'Delantero',
                    default => ucfirst(mb_strtolower((string) $jugador->posicion)),
                };

                $jugador->partidos = 0;
                $jugador->rating = 0;
                return $jugador;
            });

        return view('public.plantel', [
            'jugadores' => $jugadores,
        ]);
    }
}

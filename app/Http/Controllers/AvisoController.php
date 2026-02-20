<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aviso;
use Illuminate\Support\Facades\DB;

class AvisoController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        Aviso::create($request->all());
        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($id)
    {
        Aviso::where('id', $id)->delete();
        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $id)
    {
        Aviso::where('id', $id)->update($request->all());
        return response()->json(['ok' => true]);
    }

    public function home()
    {
        $avisos = Aviso::where('temporada_id', 2025)
            ->orderBy('fecha', 'desc')
            ->get();

        $jugadores = DB::table('jugadores')
            ->select('rut', 'nombre', 'foto', 'numero_camiseta', 'posicion')
            ->orderBy('numero_camiseta')
            ->limit(8)
            ->get()
            ->map(function ($jugador) {
                $jugador->primer_nombre = mb_strtoupper(trim(explode(' ', trim((string) $jugador->nombre))[0] ?? 'JUGADOR'));
                $jugador->posicion_label = match ($jugador->posicion) {
                    'ARQUERO' => 'Portero',
                    'DEFENSA' => 'Defensa',
                    'CENTRAL' => 'Central',
                    'DELANTERO' => 'Delantero',
                    default => ucfirst(mb_strtolower((string) $jugador->posicion)),
                };

                return $jugador;
            });

        $noticias = DB::table('noticias')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('public.home', compact('avisos', 'jugadores', 'noticias'));
    }
}

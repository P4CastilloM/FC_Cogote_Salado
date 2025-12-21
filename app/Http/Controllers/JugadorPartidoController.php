<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JugadorPartido;
use App\Models\Jugador;

class JugadorPartidoController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        JugadorPartido::create([
            'jugador_rut' => $request->jugador_rut,
            'partido_id' => $request->partido_id,
            'goles' => $request->goles,
            'asistencias' => $request->asistencias
        ]);

        $this->recalcularTotales($request->jugador_rut);

        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($jugador_rut, $partido_id)
    {
        JugadorPartido::where('jugador_rut', $jugador_rut)
            ->where('partido_id', $partido_id)
            ->delete();

        $this->recalcularTotales($jugador_rut);

        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $jugador_rut, $partido_id)
    {
        JugadorPartido::where('jugador_rut', $jugador_rut)
            ->where('partido_id', $partido_id)
            ->update([
                'goles' => $request->goles,
                'asistencias' => $request->asistencias
            ]);

        $this->recalcularTotales($jugador_rut);

        return response()->json(['ok' => true]);
    }

    // 🔁 SUMATORIA GLOBAL
    private function recalcularTotales($jugador_rut)
    {
        $totales = JugadorPartido::where('jugador_rut', $jugador_rut)
            ->selectRaw('SUM(goles) as goles, SUM(asistencias) as asistencias')
            ->first();

        Jugador::where('rut', $jugador_rut)->update([
            'goles' => $totales->goles ?? 0,
            'asistencia' => $totales->asistencias ?? 0
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jugador;

class JugadorController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        Jugador::create([
            'rut' => $request->rut,
            'nombre' => $request->nombre,
            'foto' => $request->foto,
            'numero_camiseta' => $request->numero_camiseta,
            'posicion' => $request->posicion
        ]);

        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($rut)
    {
        Jugador::where('rut', $rut)->delete();

        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $rut)
    {
        Jugador::where('rut', $rut)->update([
            'nombre' => $request->nombre,
            'foto' => $request->foto,
            'numero_camiseta' => $request->numero_camiseta,
            'posicion' => $request->posicion
        ]);

        return response()->json(['ok' => true]);
    }
}

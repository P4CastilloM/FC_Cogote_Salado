<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aviso;

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

        return view('public.home', compact('avisos'));
    }
}

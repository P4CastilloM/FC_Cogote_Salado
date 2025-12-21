<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Temporada;

class TemporadaController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        Temporada::create($request->all());
        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($id)
    {
        Temporada::where('id', $id)->delete();
        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $id)
    {
        Temporada::where('id', $id)->update($request->all());
        return response()->json(['ok' => true]);
    }
}

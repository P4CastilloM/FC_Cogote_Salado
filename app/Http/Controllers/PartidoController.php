<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;

class PartidoController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        Partido::create($request->all());
        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($id)
    {
        Partido::where('id', $id)->delete();
        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $id)
    {
        Partido::where('id', $id)->update($request->all());
        return response()->json(['ok' => true]);
    }
}

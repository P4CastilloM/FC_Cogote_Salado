<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ayudante;

class AyudanteController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        Ayudante::create($request->all());
        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($id)
    {
        Ayudante::where('id', $id)->delete();
        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $id)
    {
        Ayudante::where('id', $id)->update($request->all());
        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Premio;

class PremioController extends Controller
{
    // 1️⃣ INSERT
    public function store(Request $request)
    {
        Premio::create($request->all());
        return response()->json(['ok' => true]);
    }

    // 2️⃣ DELETE
    public function destroy($id)
    {
        Premio::where('id', $id)->delete();
        return response()->json(['ok' => true]);
    }

    // 3️⃣ UPDATE
    public function update(Request $request, $id)
    {
        Premio::where('id', $id)->update($request->all());
        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DirectivaController extends Controller
{
    public function index()
    {
        $directiva = DB::table('ayudantes')
            ->where('activo', true)
            ->orderBy('id')
            ->get()
            ->map(function ($item, $index) {
                $item->full_name = trim(($item->nombre ?? '').' '.($item->apellido ?? ''));
                $item->rol = $item->descripcion_rol ?: 'Integrante';
                $item->badge = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                $item->foto_url = $item->foto ? asset('storage/'.$item->foto) : null;
                return $item;
            });

        return view('public.directiva', [
            'directiva' => $directiva,
        ]);
    }
}

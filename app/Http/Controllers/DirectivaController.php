<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirectivaController extends Controller
{
    public function index()
    {
        $hasPriority = Schema::hasColumn('ayudantes', 'prioridad');

        $directiva = DB::table('ayudantes')
            ->where('activo', true)
            ->when($hasPriority, fn ($query) => $query->orderBy('prioridad'))
            ->orderBy('id')
            ->get()
            ->map(function ($item) use ($hasPriority) {
                $item->full_name = trim(($item->nombre ?? '').' '.($item->apellido ?? ''));
                $item->rol = $item->descripcion_rol ?: 'Integrante';
                $item->foto_url = $item->foto ? asset('storage/'.$item->foto) : null;
                $item->prioridad = $hasPriority ? (int) ($item->prioridad ?? 10) : 10;

                if ($item->prioridad < 1 || $item->prioridad > 10) {
                    $item->prioridad = 10;
                }

                return $item;
            });

        $prioridades = collect(range(1, 10))->map(function (int $nivel) use ($directiva) {
            $miembros = $directiva->where('prioridad', $nivel)->values();

            return (object) [
                'nivel' => $nivel,
                'miembros' => $miembros,
                'topPair' => $miembros->take(2),
                'extraCount' => max(0, $miembros->count() - 2),
            ];
        });

        return view('public.directiva', [
            'prioridades' => $prioridades,
            'hasPriority' => $hasPriority,
        ]);
    }
}

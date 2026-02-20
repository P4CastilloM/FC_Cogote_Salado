<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aviso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $avisosQuery = DB::table('avisos');

        if (Schema::hasColumn('avisos', 'fijado')) {
            $avisosQuery->orderByDesc('fijado');
        }

        $avisos = $avisosQuery
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(6)
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
                    'MEDIOCAMPISTA', 'CENTRAL' => 'Mediocampista',
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

        $partidos = DB::table('partidos')
            ->leftJoin('temporadas', 'temporadas.id', '=', 'partidos.temporada_id')
            ->select('partidos.*', 'temporadas.descripcion as temporada_descripcion')
            ->orderBy('partidos.fecha')
            ->limit(6)
            ->get();

        $directivaTop = DB::table('ayudantes')
            ->where('activo', true)
            ->when(Schema::hasColumn('ayudantes', 'prioridad'), fn ($q) => $q->orderByDesc('prioridad'))
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'nombre' => trim(($item->nombre ?? '').' '.($item->apellido ?? '')),
                    'rol' => $item->descripcion_rol ?: 'Integrante',
                    'prioridad' => (int) ($item->prioridad ?? 10),
                    'foto_url' => ! empty($item->foto) ? asset('storage/'.$item->foto) : null,
                ];
            });

        $fotos = collect(Storage::disk('public')->files('fotos'))
            ->filter(fn (string $path) => preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $path) === 1)
            ->shuffle()
            ->take(8)
            ->values()
            ->map(fn (string $path) => [
                'src' => asset('storage/'.$path),
                'alt' => (string) Str::of(pathinfo($path, PATHINFO_FILENAME))->replace(['-', '_'], ' ')->title(),
            ]);

        return view('public.home', compact('avisos', 'jugadores', 'noticias', 'partidos', 'directivaTop', 'fotos'));
    }

}
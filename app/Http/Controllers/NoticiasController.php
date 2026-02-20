<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticiasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $order = $request->query('order', 'recent') === 'oldest' ? 'asc' : 'desc';

        $query = DB::table('noticias as n')
            ->leftJoin('temporadas as t', 't.id', '=', 'n.temporada_id')
            ->select('n.*', 't.descripcion as temporada_descripcion');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('n.titulo', 'like', "%{$search}%")
                    ->orWhere('n.subtitulo', 'like', "%{$search}%")
                    ->orWhere('n.cuerpo', 'like', "%{$search}%");
            });
        }

        $noticias = $query
            ->orderBy('n.fecha', $order)
            ->orderBy('n.id', 'desc')
            ->paginate(9)
            ->withQueryString();

        return view('public.noticias.index', [
            'noticias' => $noticias,
            'search' => $search,
            'order' => $order,
        ]);
    }

    public function show(int $id)
    {
        $noticia = DB::table('noticias as n')
            ->leftJoin('temporadas as t', 't.id', '=', 'n.temporada_id')
            ->select('n.*', 't.descripcion as temporada_descripcion')
            ->where('n.id', $id)
            ->first();

        abort_unless($noticia, 404);

        $related = DB::table('noticias')
            ->where('id', '!=', $noticia->id)
            ->orderBy('fecha', 'desc')
            ->limit(4)
            ->get();

        return view('public.noticias.show', [
            'noticia' => $noticia,
            'related' => $related,
        ]);
    }
}

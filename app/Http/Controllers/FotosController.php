<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FotosController extends Controller
{
    public function index(Request $request)
    {
        $album = trim((string) $request->query('album', ''));
        $albumDate = trim((string) $request->query('album_date', ''));

        if (Schema::hasTable('foto_items')) {
            $items = DB::table('foto_items as i')
                ->leftJoin('foto_albums as a', 'a.id', '=', 'i.album_id')
                ->select('i.path', 'i.created_at', 'a.nombre as album_nombre')
                ->when($album !== '', fn ($q) => $q->where('a.nombre', 'like', "%{$album}%"))
                ->when($albumDate !== '', fn ($q) => $q->whereDate('a.created_at', '=', $albumDate))
                ->orderByDesc('i.created_at')
                ->get();

            $photos = $items->map(function ($row) {
                $filename = pathinfo($row->path, PATHINFO_FILENAME);

                return [
                    'src' => asset('storage/'.$row->path),
                    'alt' => Str::of($filename)->replace(['-', '_'], ' ')->title(),
                    'album' => $row->album_nombre,
                    'created_at' => $row->created_at,
                ];
            });

            $albums = Schema::hasTable('foto_albums')
                ? DB::table('foto_albums')->orderBy('nombre')->get(['id', 'nombre'])
                : collect();

            return view('public.fotos', [
                'photos' => $photos,
                'albums' => $albums,
                'albumFilter' => $album,
                'albumDateFilter' => $albumDate,
            ]);
        }

        $files = Storage::disk('public')->allFiles('fotos');
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        $photos = collect($files)
            ->filter(fn ($path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $allowed, true))
            ->values()
            ->map(function ($path) {
                $filename = pathinfo($path, PATHINFO_FILENAME);

                return [
                    'src' => asset('storage/'.$path),
                    'alt' => Str::of($filename)->replace(['-', '_'], ' ')->title(),
                    'album' => null,
                    'created_at' => null,
                ];
            });

        return view('public.fotos', [
            'photos' => $photos,
            'albums' => collect(),
            'albumFilter' => $album,
            'albumDateFilter' => $albumDate,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FotosController extends Controller
{
    public function index()
    {
        $files = Storage::disk('public')->allFiles('fotos');

        $allowed = ['jpg','jpeg','png','webp','gif'];

        $photos = collect($files)
            ->filter(fn($path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $allowed))
            ->values()
            ->map(function ($path) {
                $filename = pathinfo($path, PATHINFO_FILENAME);

                return [
                    // 👇 CLAVE: asset() respeta tu subruta /fccogotesalado
                    'src' => asset('storage/' . $path),   // => /fccogotesalado/storage/fotos/benja.jpeg
                    'alt' => Str::of($filename)->replace(['-','_'], ' ')->title(),
                ];
            });

        return view('public.fotos', ['photos' => $photos]);
    }
    }

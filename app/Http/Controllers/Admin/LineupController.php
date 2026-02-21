<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LineupController extends Controller
{
    public function index(): View
    {
        $players = DB::table('jugadores')
            ->select('rut', 'nombre', 'foto', 'numero_camiseta')
            ->orderBy('numero_camiseta')
            ->orderBy('nombre')
            ->get()
            ->map(function ($player) {
                $fullName = trim((string) ($player->nombre ?? ''));
                $firstName = trim((string) Str::of($fullName)->before(' '));

                return [
                    'id' => (string) $player->rut,
                    'name' => $firstName !== '' ? $firstName : 'Jugador',
                    'photo' => ! empty($player->foto) ? Storage::url($player->foto) : null,
                ];
            })
            ->values();

        return view('admin.lineup-builder', [
            'players' => $players,
        ]);
    }
}

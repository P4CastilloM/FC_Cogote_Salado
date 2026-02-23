<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LineupController extends Controller
{
    public function index(): View
    {
        $activeMatch = DB::table('partidos')
            ->select('id', 'fecha', 'rival', 'nombre_lugar', 'attendance_starts_at', 'attendance_ends_at')
            ->whereNotNull('attendance_starts_at')
            ->whereNotNull('attendance_ends_at')
            ->where('attendance_starts_at', '<=', now())
            ->where('attendance_ends_at', '>=', now())
            ->orderBy('fecha')
            ->first();

        $query = DB::table('jugadores as j')
            ->select('j.rut', 'j.nombre', 'j.sobrenombre', 'j.foto', 'j.numero_camiseta')
            ->orderBy('j.numero_camiseta')
            ->orderBy('j.nombre');

        if ($activeMatch && Schema::hasTable('partido_asistencias')) {
            $query->join('partido_asistencias as pa', function ($join) use ($activeMatch): void {
                $join->on('pa.jugador_rut', '=', 'j.rut')
                    ->where('pa.partido_id', '=', $activeMatch->id);
            });
        } elseif (! $activeMatch) {
            $query->whereRaw('1 = 0');
        }

        $players = $query->get()
            ->map(function ($player) {
                $nickname = trim((string) ($player->sobrenombre ?? ''));
                $fullName = trim((string) ($player->nombre ?? ''));
                $firstName = trim((string) Str::of($fullName)->before(' '));
                $displayName = $nickname !== '' ? $nickname : ($firstName !== '' ? $firstName : 'Jugador');

                return [
                    'id' => (string) $player->rut,
                    'name' => $displayName,
                    'photo' => ! empty($player->foto) ? Storage::url($player->foto) : null,
                ];
            })
            ->values();

        return view('admin.lineup-builder', [
            'players' => $players,
            'activeMatch' => $activeMatch,
        ]);
    }
}

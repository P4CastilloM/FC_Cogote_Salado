<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $teamAssignments = [];
        if ($activeMatch && Schema::hasTable('jugador_partido') && Schema::hasColumn('jugador_partido', 'equipo_ab')) {
            $teamAssignments = DB::table('jugador_partido')
                ->where('partido_id', $activeMatch->id)
                ->whereIn('jugador_rut', $players->pluck('id')->map(fn ($id) => (int) $id)->all())
                ->whereIn('equipo_ab', ['A', 'B'])
                ->pluck('equipo_ab', 'jugador_rut')
                ->map(fn ($team) => strtoupper((string) $team))
                ->all();
        }

        return view('admin.lineup-builder', [
            'players' => $players,
            'activeMatch' => $activeMatch,
            'teamAssignments' => $teamAssignments,
        ]);
    }

    public function saveTeam(Request $request, int $partidoId): JsonResponse
    {
        $data = $request->validate([
            'jugador_rut' => ['required', 'integer', 'exists:jugadores,rut'],
            'equipo_ab' => ['required', 'string', 'in:A,B'],
        ]);

        if (! Schema::hasTable('jugador_partido') || ! Schema::hasColumn('jugador_partido', 'equipo_ab')) {
            return response()->json([
                'ok' => false,
                'message' => 'Falta aplicar migraciones para guardar Equipo A/B.',
            ], 422);
        }

        $partido = DB::table('partidos')->where('id', $partidoId)->first();
        if (! $partido) {
            return response()->json(['ok' => false, 'message' => 'Partido no encontrado.'], 404);
        }

        $isConfirmed = Schema::hasTable('partido_asistencias')
            && DB::table('partido_asistencias')
                ->where('partido_id', $partidoId)
                ->where('jugador_rut', (int) $data['jugador_rut'])
                ->exists();

        if (! $isConfirmed) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo jugadores confirmados del partido pueden asignarse a Equipo A/B.',
            ], 422);
        }

        DB::table('jugador_partido')->insertOrIgnore([
            'partido_id' => $partidoId,
            'jugador_rut' => (int) $data['jugador_rut'],
            'goles' => 0,
            'asistencias' => 0,
            'atajadas' => 0,
            'participo' => true,
            'equipo_ab' => strtoupper((string) $data['equipo_ab']),
        ]);

        DB::table('jugador_partido')
            ->where('partido_id', $partidoId)
            ->where('jugador_rut', (int) $data['jugador_rut'])
            ->update([
                'equipo_ab' => strtoupper((string) $data['equipo_ab']),
                'participo' => true,
            ]);

        return response()->json([
            'ok' => true,
            'jugador_rut' => (int) $data['jugador_rut'],
            'equipo_ab' => strtoupper((string) $data['equipo_ab']),
        ]);
    }
}

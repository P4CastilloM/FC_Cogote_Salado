<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartidoAsistenciaController extends Controller
{
    public function show(string $token): View
    {
        $partido = $this->activePartidoByToken($token);

        abort_if(! $partido, 404);

        $confirmedCount = DB::table('partido_asistencias')->where('partido_id', $partido->id)->count();

        return view('public.partido-asistencia', [
            'partido' => $partido,
            'confirmedCount' => $confirmedCount,
            'alert' => $this->attendanceAlert($confirmedCount),
        ]);
    }

    public function search(string $token, Request $request): JsonResponse
    {
        $partido = $this->activePartidoByToken($token);
        if (! $partido) {
            return response()->json(['ok' => false, 'message' => 'Link no activo'], 404);
        }

        $rut = preg_replace('/\D+/', '', (string) $request->query('rut', ''));
        if (strlen($rut) < 5) {
            return response()->json(['ok' => true, 'players' => []]);
        }

        $players = DB::table('jugadores')
            ->select('rut', 'nombre', 'sobrenombre')
            ->where('rut', 'like', $rut.'%')
            ->orderBy('nombre')
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'rut' => (string) $p->rut,
                'name' => trim((string) ($p->sobrenombre ?: $p->nombre)),
            ])
            ->values();

        return response()->json(['ok' => true, 'players' => $players]);
    }

    public function confirm(string $token, Request $request): RedirectResponse
    {
        $partido = $this->activePartidoByToken($token);
        abort_if(! $partido, 404);

        $data = $request->validate([
            'actor_rut' => ['required', 'digits_between:5,8'],
            'guests' => ['nullable', 'array', 'max:6'],
            'guests.*' => ['nullable', 'digits_between:5,8'],
            'will_attend' => ['required', 'accepted'],
        ]);

        $actorRut = (int) preg_replace('/\D+/', '', (string) $data['actor_rut']);
        $guestRuts = collect($data['guests'] ?? [])
            ->map(fn ($rut) => (int) preg_replace('/\D+/', '', (string) $rut))
            ->filter(fn ($rut) => $rut > 0)
            ->unique()
            ->take(6)
            ->values();

        $allRuts = collect([$actorRut])->merge($guestRuts)->unique()->values();

        $found = DB::table('jugadores')->whereIn('rut', $allRuts)->pluck('rut')->map(fn ($rut) => (int) $rut);
        $missing = $allRuts->diff($found)->values();

        if ($missing->isNotEmpty()) {
            return redirect()->back()->withErrors([
                'actor_rut' => 'Uno o más RUT no existen en el plantel: '.implode(', ', $missing->all()),
            ])->withInput();
        }

        DB::transaction(function () use ($partido, $actorRut, $allRuts): void {
            foreach ($allRuts as $targetRut) {
                DB::table('partido_asistencias')->updateOrInsert(
                    ['partido_id' => $partido->id, 'jugador_rut' => $targetRut],
                    [
                        'checked_by_rut' => $actorRut,
                        'confirmed_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                DB::table('partido_asistencia_logs')->insert([
                    'partido_id' => $partido->id,
                    'actor_rut' => $actorRut,
                    'target_rut' => $targetRut,
                    'checked_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $confirmedCount = DB::table('partido_asistencias')->where('partido_id', $partido->id)->count();

        return redirect()->route('fccs.partidos.asistencia.show', $token)
            ->with('status', '✅ Asistencia confirmada para '.count($allRuts).' persona(s).')
            ->with('attendance_alert', $this->attendanceAlert($confirmedCount));
    }

    private function activePartidoByToken(string $token): ?object
    {
        return DB::table('partidos')
            ->where('attendance_token', $token)
            ->whereNotNull('attendance_starts_at')
            ->whereNotNull('attendance_ends_at')
            ->where('attendance_starts_at', '<=', now())
            ->where('attendance_ends_at', '>=', now())
            ->first();
    }

    private function attendanceAlert(int $confirmedCount): ?string
    {
        if ($confirmedCount === 15) {
            return '⚠️ Hay 15 confirmados. Estamos impares: faltaría 1 más para completar equipos parejos.';
        }

        if ($confirmedCount > 14) {
            $extra = $confirmedCount - 14;
            return "⚠️ Hay {$confirmedCount} confirmados ({$extra} sobre el ideal de 14).";
        }

        return null;
    }
}

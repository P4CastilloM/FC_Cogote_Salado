<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ->select('rut', 'nombre', 'apellido', 'sobrenombre', 'es_visitante')
            ->where('rut', 'like', $rut.'%')
            ->orderBy('nombre')
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'rut' => (string) $p->rut,
                'name' => trim((string) ($p->sobrenombre ?: $p->nombre)),
                'nombre' => (string) ($p->nombre ?? ''),
                'apellido' => (string) ($p->apellido ?? ''),
                'sobrenombre' => (string) ($p->sobrenombre ?? ''),
                'es_visitante' => (bool) ($p->es_visitante ?? false),
            ])
            ->values();

        return response()->json(['ok' => true, 'players' => $players]);
    }

    public function confirm(string $token, Request $request): RedirectResponse
    {
        $partido = $this->activePartidoByToken($token);
        abort_if(! $partido, 404);

        $request->merge([
            'visitantes' => $this->normalizeVisitantesInput($request->input('visitantes', [])),
        ]);

        $data = $request->validate([
            'actor_rut' => ['required', 'digits_between:5,8'],
            'guests' => ['nullable', 'array', 'max:6'],
            'guests.*' => ['nullable', 'digits_between:5,8'],
            'visitantes' => ['nullable', 'array', 'max:4'],
            'visitantes.*.rut' => ['nullable', 'digits_between:5,8'],
            'visitantes.*.nombre' => ['nullable', 'string', 'max:25'],
            'visitantes.*.apellido' => ['nullable', 'string', 'max:50'],
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

        $visitantes = collect($data['visitantes'] ?? [])
            ->map(function ($visitante) {
                $rut = (int) preg_replace('/\D+/', '', (string) ($visitante['rut'] ?? ''));

                return [
                    'rut' => $rut,
                    'nombre' => trim((string) ($visitante['nombre'] ?? '')),
                    'apellido' => trim((string) ($visitante['apellido'] ?? '')) ?: null,
                ];
            })
            ->filter(fn ($visitante) => $visitante['rut'] > 0 && $visitante['nombre'] !== '')
            ->unique('rut')
            ->take(4)
            ->values();

        if ($visitantes->isNotEmpty()) {
            foreach ($visitantes as $visitante) {
                $exists = DB::table('jugadores')->where('rut', $visitante['rut'])->first();

                if (! $exists) {
                    $insert = [
                        'rut' => $visitante['rut'],
                        'nombre' => $visitante['nombre'],
                        'apellido' => $visitante['apellido'],
                        'sobrenombre' => null,
                        'numero_camiseta' => 999,
                        'posicion' => 'DEFENSA',
                        'goles' => 0,
                        'asistencia' => 0,
                        'created_at' => now($this->clubTimezone()),
                        'updated_at' => now($this->clubTimezone()),
                    ];

                    if (Schema::hasColumn('jugadores', 'atajadas')) {
                        $insert['atajadas'] = 0;
                    }

                    if (Schema::hasColumn('jugadores', 'partidos_jugados')) {
                        $insert['partidos_jugados'] = 0;
                    }

                    if (Schema::hasColumn('jugadores', 'es_visitante')) {
                        $insert['es_visitante'] = true;
                    }

                    DB::table('jugadores')->insert($insert);
                } else {
                    $update = [
                        'updated_at' => now($this->clubTimezone()),
                    ];

                    if (Schema::hasColumn('jugadores', 'apellido') && empty($exists->apellido) && $visitante['apellido']) {
                        $update['apellido'] = $visitante['apellido'];
                    }

                    if (Schema::hasColumn('jugadores', 'es_visitante')) {
                        $update['es_visitante'] = (bool) ($exists->es_visitante ?? false);
                    }

                    DB::table('jugadores')->where('rut', $visitante['rut'])->update($update);
                }
            }

            $allRuts = $allRuts->merge($visitantes->pluck('rut'))->unique()->values();
        }

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
                        'confirmed_at' => now($this->clubTimezone()),
                        'updated_at' => now($this->clubTimezone()),
                        'created_at' => now($this->clubTimezone()),
                    ]
                );

                DB::table('partido_asistencia_logs')->insert([
                    'partido_id' => $partido->id,
                    'actor_rut' => $actorRut,
                    'target_rut' => $targetRut,
                    'checked_at' => now($this->clubTimezone()),
                    'created_at' => now($this->clubTimezone()),
                    'updated_at' => now($this->clubTimezone()),
                ]);

                if ($this->isInsideStatsWindow($partido)) {
                    DB::table('jugador_partido')->insertOrIgnore([
                        'partido_id' => $partido->id,
                        'jugador_rut' => (int) $targetRut,
                        'goles' => 0,
                        'asistencias' => 0,
                        'atajadas' => 0,
                        'participo' => true,
                    ]);

                    DB::table('jugador_partido')
                        ->where('partido_id', $partido->id)
                        ->where('jugador_rut', (int) $targetRut)
                        ->update(['participo' => true]);
                }
            }
        });

        $confirmedCount = DB::table('partido_asistencias')->where('partido_id', $partido->id)->count();

        return redirect()->route('fccs.partidos.asistencia.show', $token)
            ->with('status', '✅ Asistencia confirmada para '.count($allRuts).' persona(s).'.($visitantes->isNotEmpty() ? ' Incluye '.count($visitantes).' visita(s).' : ''))
            ->with('attendance_alert', $this->attendanceAlert($confirmedCount));
    }


    private function isInsideStatsWindow(object $partido): bool
    {
        $timezone = $this->clubTimezone();
        $hour = trim((string) ($partido->hora ?? '00:00'));
        $time = preg_match('/^\d{2}:\d{2}/', $hour) ? substr($hour, 0, 5) : '00:00';
        $kickoff = Carbon::parse(((string) $partido->fecha).' '.$time, $timezone);

        $startsAt = $kickoff->copy()->subHour();
        $endsAt = $kickoff->copy()->addHours(4);

        return now($timezone)->betweenIncluded($startsAt, $endsAt);
    }

    private function activePartidoByToken(string $token): ?object
    {
        return DB::table('partidos')
            ->where('attendance_token', $token)
            ->whereNotNull('attendance_starts_at')
            ->whereNotNull('attendance_ends_at')
            ->where('attendance_starts_at', '<=', now($this->clubTimezone()))
            ->where('attendance_ends_at', '>=', now($this->clubTimezone()))
            ->first();
    }

    /**
     * @return array<int, array{rut: string, nombre: string, apellido: string}>
     */
    private function normalizeVisitantesInput(mixed $rawVisitantes): array
    {
        if (! is_array($rawVisitantes)) {
            return [];
        }

        $rutValues = [];
        $nombreValues = [];
        $apellidoValues = [];

        if (array_key_exists('rut', $rawVisitantes) || array_key_exists('nombre', $rawVisitantes) || array_key_exists('apellido', $rawVisitantes)) {
            $rutValues = is_array($rawVisitantes['rut'] ?? null) ? array_values($rawVisitantes['rut']) : [($rawVisitantes['rut'] ?? null)];
            $nombreValues = is_array($rawVisitantes['nombre'] ?? null) ? array_values($rawVisitantes['nombre']) : [($rawVisitantes['nombre'] ?? null)];
            $apellidoValues = is_array($rawVisitantes['apellido'] ?? null) ? array_values($rawVisitantes['apellido']) : [($rawVisitantes['apellido'] ?? null)];
        } else {
            foreach ($rawVisitantes as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $rutValues[] = $item['rut'] ?? null;
                $nombreValues[] = $item['nombre'] ?? null;
                $apellidoValues[] = $item['apellido'] ?? null;
            }
        }

        $rowCount = max(count($rutValues), count($nombreValues), count($apellidoValues));
        $normalized = [];

        for ($i = 0; $i < $rowCount; $i++) {
            $rut = preg_replace('/\D+/', '', (string) ($rutValues[$i] ?? ''));
            $nombre = trim((string) ($nombreValues[$i] ?? ''));
            $apellido = trim((string) ($apellidoValues[$i] ?? ''));

            if ($rut === '' && $nombre === '' && $apellido === '') {
                continue;
            }

            $normalized[] = [
                'rut' => $rut,
                'nombre' => $nombre,
                'apellido' => $apellido,
            ];
        }

        return array_slice($normalized, 0, 4);
    }

    private function clubTimezone(): string
    {
        return 'America/Santiago';
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

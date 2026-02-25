<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PartidoStatsController extends Controller
{
    public function index(int $id): View|RedirectResponse
    {
        $partido = DB::table('partidos')->where('id', $id)->first();
        abort_unless($partido, 404);

        $window = $this->statsWindow($partido);
        $now = now($this->clubTimezone());
        $isActiveWindow = $now->betweenIncluded($window['starts_at'], $window['ends_at']);

        if (! $isActiveWindow) {
            return redirect()
                ->route('admin.partidos.index')
                ->with('error', 'La carga de estadísticas está disponible desde 1 hora antes hasta 4 horas después del inicio del partido.');
        }

        $this->syncConfirmedPlayers($id);

        return view('admin.partido-estadisticas', [
            'partido' => $partido,
            'players' => $this->playersForMatch($id),
            'windowStart' => $window['starts_at'],
            'windowEnd' => $window['ends_at'],
            'statsClosedAt' => $partido->stats_closed_at ?? null,
            'supportsOperationId' => $this->supportsOperationTable(),
        ]);
    }

    public function data(int $id): JsonResponse
    {
        $partido = DB::table('partidos')->where('id', $id)->first();
        if (! $partido) {
            return response()->json(['ok' => false, 'message' => 'Partido no encontrado.'], 404);
        }

        $this->syncConfirmedPlayers($id);

        return response()->json([
            'ok' => true,
            'closed' => ! empty($partido->stats_closed_at),
            'players' => $this->playersForMatch($id),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $partido = DB::table('partidos')->where('id', $id)->first();
        if (! $partido) {
            return response()->json(['ok' => false, 'message' => 'Partido no encontrado.'], 404);
        }

        if (! empty($partido->stats_closed_at)) {
            return response()->json(['ok' => false, 'message' => 'El partido ya fue cerrado.'], 422);
        }

        $window = $this->statsWindow($partido);
        if (! now($this->clubTimezone())->betweenIncluded($window['starts_at'], $window['ends_at'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Fuera de la ventana permitida para cargar estadísticas.',
            ], 422);
        }

        $data = $request->validate([
            'jugador_rut' => ['required', 'integer', 'exists:jugadores,rut'],
            'field' => ['required', 'string', 'in:goles,asistencias,atajadas'],
            'delta' => ['required', 'integer', 'min:-20', 'max:20'],
            'operation_id' => ['nullable', 'string', 'max:80'],
        ]);

        $confirmed = DB::table('partido_asistencias')
            ->where('partido_id', $id)
            ->where('jugador_rut', $data['jugador_rut'])
            ->exists();

        if (! $confirmed) {
            return response()->json(['ok' => false, 'message' => 'Solo jugadores confirmados pueden registrar estadísticas.'], 422);
        }

        $field = $data['field'];
        $jugadorRut = (int) $data['jugador_rut'];
        $delta = (int) $data['delta'];
        $operationId = trim((string) ($data['operation_id'] ?? ''));

        $this->syncConfirmedPlayers($id);

        $result = DB::transaction(function () use ($id, $jugadorRut, $field, $delta, $operationId): array {
            if ($operationId !== '' && $this->supportsOperationTable()) {
                $alreadyApplied = DB::table('partido_stat_operaciones')
                    ->where('partido_id', $id)
                    ->where('operation_id', $operationId)
                    ->exists();

                if ($alreadyApplied) {
                    return [
                        'value' => (int) DB::table('jugador_partido')
                            ->where('partido_id', $id)
                            ->where('jugador_rut', $jugadorRut)
                            ->value($field),
                        'effective_delta' => 0,
                    ];
                }
            }

            DB::table('jugador_partido')->insertOrIgnore([
                'partido_id' => $id,
                'jugador_rut' => $jugadorRut,
                'goles' => 0,
                'asistencias' => 0,
                'atajadas' => 0,
                'participo' => true,
            ]);

            $current = (int) DB::table('jugador_partido')
                ->where('partido_id', $id)
                ->where('jugador_rut', $jugadorRut)
                ->lockForUpdate()
                ->value($field);

            $next = max(0, $current + $delta);
            $effectiveDelta = $next - $current;

            DB::table('jugador_partido')
                ->where('partido_id', $id)
                ->where('jugador_rut', $jugadorRut)
                ->update([
                    $field => $next,
                    'participo' => true,
                ]);

            if ($operationId !== '' && $this->supportsOperationTable()) {
                try {
                    DB::table('partido_stat_operaciones')->insert([
                        'partido_id' => $id,
                        'operation_id' => $operationId,
                        'jugador_rut' => $jugadorRut,
                        'field' => $field,
                        'delta' => $effectiveDelta,
                        'created_at' => now($this->clubTimezone()),
                    ]);
                } catch (QueryException) {
                    // Compatibilidad si la migración no está aplicada aún.
                }
            }

            return [
                'value' => $next,
                'effective_delta' => $effectiveDelta,
            ];
        });

        return response()->json([
            'ok' => true,
            'value' => $result['value'],
            'applied_delta' => $result['effective_delta'],
        ]);
    }

    public function finish(int $id): RedirectResponse
    {
        $partido = DB::table('partidos')->where('id', $id)->first();
        abort_unless($partido, 404);

        if (! empty($partido->stats_closed_at)) {
            return redirect()->route('admin.partidos.stats', $id)->with('status', 'El partido ya estaba cerrado.');
        }

        DB::transaction(function () use ($id): void {
            $confirmedRows = DB::table('partido_asistencias as pa')
                ->leftJoin('jugador_partido as jp', function ($join): void {
                    $join->on('jp.jugador_rut', '=', 'pa.jugador_rut')
                        ->on('jp.partido_id', '=', 'pa.partido_id');
                })
                ->where('pa.partido_id', $id)
                ->select(
                    'pa.jugador_rut',
                    DB::raw('COALESCE(jp.goles, 0) as goles'),
                    DB::raw('COALESCE(jp.asistencias, 0) as asistencias'),
                    DB::raw('COALESCE(jp.atajadas, 0) as atajadas')
                )
                ->get();

            foreach ($confirmedRows as $row) {
                DB::table('jugadores')
                    ->where('rut', $row->jugador_rut)
                    ->incrementEach([
                        'goles' => (int) $row->goles,
                        'asistencia' => (int) $row->asistencias,
                        'atajadas' => (int) $row->atajadas,
                        'partidos_jugados' => 1,
                    ]);
            }

            DB::table('partidos')
                ->where('id', $id)
                ->update(['stats_closed_at' => now($this->clubTimezone())]);
        });

        return redirect()->route('admin.partidos.stats', $id)->with('status', '✅ Partido cerrado y estadísticas acumuladas al plantel.');
    }

    private function playersForMatch(int $matchId)
    {
        return DB::table('jugador_partido as jp')
            ->join('jugadores as j', 'j.rut', '=', 'jp.jugador_rut')
            ->join('partido_asistencias as pa', function ($join) use ($matchId): void {
                $join->on('pa.jugador_rut', '=', 'jp.jugador_rut')
                    ->where('pa.partido_id', '=', $matchId);
            })
            ->where('jp.partido_id', $matchId)
            ->select(
                'j.rut',
                'j.nombre',
                'j.sobrenombre',
                'j.numero_camiseta',
                'j.posicion',
                'jp.goles',
                'jp.asistencias',
                'jp.atajadas',
                'jp.participo'
            )
            ->orderByRaw("COALESCE(NULLIF(j.sobrenombre, ''), j.nombre) asc")
            ->get();
    }

    private function syncConfirmedPlayers(int $partidoId): void
    {
        $confirmedRuts = DB::table('partido_asistencias')
            ->where('partido_id', $partidoId)
            ->pluck('jugador_rut');

        foreach ($confirmedRuts as $rut) {
            $inserted = DB::table('jugador_partido')->insertOrIgnore([
                'partido_id' => $partidoId,
                'jugador_rut' => (int) $rut,
                'goles' => 0,
                'asistencias' => 0,
                'atajadas' => 0,
                'participo' => true,
            ]);

            if ($inserted > 0) {
                continue;
            }

            DB::table('jugador_partido')
                ->where('partido_id', $partidoId)
                ->where('jugador_rut', (int) $rut)
                ->update(['participo' => true]);
        }
    }

    private function supportsOperationTable(): bool
    {
        static $supports = null;

        if ($supports !== null) {
            return $supports;
        }

        $supports = Schema::hasTable('partido_stat_operaciones');

        return $supports;
    }

    private function clubTimezone(): string
    {
        return 'America/Santiago';
    }

    /** @return array{starts_at: Carbon, ends_at: Carbon} */
    private function statsWindow(object $partido): array
    {
        $date = (string) ($partido->fecha ?? '');
        $hour = trim((string) ($partido->hora ?? '00:00'));
        $time = preg_match('/^\d{2}:\d{2}/', $hour) ? substr($hour, 0, 5) : '00:00';

        $kickoff = Carbon::parse($date.' '.$time, $this->clubTimezone());

        return [
            'starts_at' => $kickoff->copy()->subHour(),
            'ends_at' => $kickoff->copy()->addHours(4),
        ];
    }
}

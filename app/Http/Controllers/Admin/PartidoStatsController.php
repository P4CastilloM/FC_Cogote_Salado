<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
                ->with('error', 'La carga de estadísticas está disponible desde 1 hora antes hasta 2 horas después del inicio del partido.');
        }

        $this->syncConfirmedPlayers($id);

        $players = DB::table('jugador_partido as jp')
            ->join('jugadores as j', 'j.rut', '=', 'jp.jugador_rut')
            ->join('partido_asistencias as pa', function ($join) use ($id): void {
                $join->on('pa.jugador_rut', '=', 'jp.jugador_rut')
                    ->where('pa.partido_id', '=', $id);
            })
            ->where('jp.partido_id', $id)
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

        return view('admin.partido-estadisticas', [
            'partido' => $partido,
            'players' => $players,
            'windowStart' => $window['starts_at'],
            'windowEnd' => $window['ends_at'],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $partido = DB::table('partidos')->where('id', $id)->first();
        if (! $partido) {
            return response()->json(['ok' => false, 'message' => 'Partido no encontrado.'], 404);
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

        $result = DB::transaction(function () use ($id, $jugadorRut, $field, $delta): array {
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

            if ($effectiveDelta !== 0) {
                $playerColumn = match ($field) {
                    'goles' => 'goles',
                    'asistencias' => 'asistencia',
                    'atajadas' => 'atajadas',
                };

                $currentPlayerTotal = (int) DB::table('jugadores')
                    ->where('rut', $jugadorRut)
                    ->lockForUpdate()
                    ->value($playerColumn);

                DB::table('jugadores')
                    ->where('rut', $jugadorRut)
                    ->update([$playerColumn => max(0, $currentPlayerTotal + $effectiveDelta)]);
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

    private function syncConfirmedPlayers(int $partidoId): void
    {
        $confirmedRuts = DB::table('partido_asistencias')
            ->where('partido_id', $partidoId)
            ->pluck('jugador_rut');

        foreach ($confirmedRuts as $rut) {
            DB::table('jugador_partido')->insertOrIgnore([
                'partido_id' => $partidoId,
                'jugador_rut' => (int) $rut,
                'goles' => 0,
                'asistencias' => 0,
                'atajadas' => 0,
                'participo' => true,
            ]);

            DB::table('jugador_partido')
                ->where('partido_id', $partidoId)
                ->where('jugador_rut', (int) $rut)
                ->update(['participo' => true]);
        }
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
            'ends_at' => $kickoff->copy()->addHours(2),
        ];
    }
}

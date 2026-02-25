<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $cards = [
            ['key' => 'jugadores', 'label' => 'Jugadores', 'icon' => '⚽'],
            ['key' => 'noticias', 'label' => 'Noticias', 'icon' => '📰'],
            ['key' => 'avisos', 'label' => 'Avisos', 'icon' => '📢'],
            ['key' => 'partidos', 'label' => 'Partidos', 'icon' => '📅'],
            ['key' => 'premios', 'label' => 'Premios', 'icon' => '🏆'],
            ['key' => 'temporadas', 'label' => 'Temporadas', 'icon' => '⏳'],
            ['key' => 'ayudantes', 'label' => 'Ayudantes', 'icon' => '🤝'],
        ];

        $stats = collect($cards)->map(function (array $card) {
            $count = Schema::hasTable($card['key'])
                ? DB::table($card['key'])->count()
                : 0;

            return [
                ...$card,
                'count' => $count,
            ];
        });

        $visitSummary = [
            'today' => 0,
            'month' => 0,
            'year' => 0,
        ];

        $uniqueSummary = [
            'today' => 0,
            'month' => 0,
            'year' => 0,
        ];

        $deviceSeries = [];
        $dailyUniqueSeries = [];
        $attendanceMatches = collect();
        $attendanceLogs = collect();
        if (Schema::hasTable('page_visits')) {
            $today = now()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();
            $yearStart = now()->startOfYear()->toDateString();

            $visitSummary = [
                'today' => DB::table('page_visits')->whereDate('visited_on', $today)->count(),
                'month' => DB::table('page_visits')->whereDate('visited_on', '>=', $monthStart)->count(),
                'year' => DB::table('page_visits')->whereDate('visited_on', '>=', $yearStart)->count(),
            ];

            $uniqueSummary = [
                'today' => (int) (DB::table('page_visits')
                    ->whereDate('visited_on', $today)
                    ->selectRaw("COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) AS total")
                    ->value('total') ?? 0),
                'month' => (int) (DB::table('page_visits')
                    ->whereDate('visited_on', '>=', $monthStart)
                    ->selectRaw("COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) AS total")
                    ->value('total') ?? 0),
                'year' => (int) (DB::table('page_visits')
                    ->whereDate('visited_on', '>=', $yearStart)
                    ->selectRaw("COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) AS total")
                    ->value('total') ?? 0),
            ];

            $deviceSeries = DB::table('page_visits')
                ->selectRaw("\n                    CASE\n                        WHEN user_agent LIKE '%Mobile%' AND user_agent NOT LIKE '%Tablet%' THEN 'Móvil'\n                        WHEN user_agent LIKE '%Tablet%' OR user_agent LIKE '%iPad%' THEN 'Tablet'\n                        WHEN user_agent LIKE '%Windows%' OR user_agent LIKE '%Macintosh%' OR user_agent LIKE '%Linux%' THEN 'Escritorio'\n                        ELSE 'Otro'\n                    END AS label,\n                    COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) AS total\n                ")
                ->whereDate('visited_on', '>=', $today)
                ->groupBy('label')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'label' => $row->label,
                    'total' => (int) $row->total,
                ])
                ->all();

            $rangeStart = now()->subDays(29)->toDateString();
            $dailyUniqueMap = DB::table('page_visits')
                ->selectRaw("visited_on as label, COUNT(DISTINCT CONCAT(COALESCE(ip_address, ''), '|', COALESCE(user_agent, ''))) as total")
                ->whereDate('visited_on', '>=', $rangeStart)
                ->groupBy('visited_on')
                ->orderBy('visited_on')
                ->get()
                ->keyBy('label');

            $dailyUniqueSeries = collect(range(0, 29))
                ->map(function ($offset) use ($dailyUniqueMap) {
                    $date = now()->subDays(29 - $offset)->toDateString();
                    return [
                        'label' => $date,
                        'total' => (int) optional($dailyUniqueMap->get($date))->total,
                    ];
                })
                ->all();
        }

        $attendanceMatches = $this->attendanceMatches(6);
        $attendanceLogs = $this->attendanceLogs(10, '', 'recent');

        return view('admin.dashboard', [
            'stats' => $stats,
            'visitSummary' => $visitSummary,
            'uniqueSummary' => $uniqueSummary,
            'deviceSeries' => $deviceSeries,
            'dailyUniqueSeries' => $dailyUniqueSeries,
            'attendanceMatches' => $attendanceMatches,
            'attendanceLogs' => $attendanceLogs,
        ]);
    }

    public function activeMatches(Request $request): View
    {
        $perPage = (int) $request->query('checks_per_page', 10);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->query('checks_q', ''));
        $order = $request->query('checks_order', 'recent') === 'oldest' ? 'oldest' : 'recent';

        return view('admin.partidos-activos', [
            'attendanceMatches' => $this->attendanceMatches(50),
            'attendanceLogs' => $this->attendanceLogs($perPage, $search, $order),
            'checksPerPage' => $perPage,
            'checksSearch' => $search,
            'checksOrder' => $order,
        ]);
    }

    public function removeConfirmedPlayer(int $partidoId, int $jugadorRut): RedirectResponse
    {
        if (! Schema::hasTable('partido_asistencias')) {
            return back()->with('error', 'La tabla de asistencias no está disponible.');
        }

        DB::transaction(function () use ($partidoId, $jugadorRut): void {
            DB::table('partido_asistencias')
                ->where('partido_id', $partidoId)
                ->where('jugador_rut', $jugadorRut)
                ->delete();

            if (Schema::hasTable('jugador_partido')) {
                DB::table('jugador_partido')
                    ->where('partido_id', $partidoId)
                    ->where('jugador_rut', $jugadorRut)
                    ->update(['participo' => false]);
            }
        });

        return back()->with('status', 'Jugador retirado del partido correctamente.');
    }

    private function attendanceMatches(int $limit)
    {
        if (! Schema::hasTable('partidos') || ! Schema::hasColumn('partidos', 'attendance_token')) {
            return collect();
        }

        $matchesQuery = DB::table('partidos')
            ->select(
                'partidos.id',
                'partidos.fecha',
                'partidos.rival',
                'partidos.nombre_lugar',
                'partidos.attendance_token',
                'partidos.attendance_starts_at',
                'partidos.attendance_ends_at'
            );

        if (Schema::hasTable('partido_asistencias')) {
            $matchesQuery->leftJoin('partido_asistencias as pa', 'pa.partido_id', '=', 'partidos.id')
                ->addSelect(DB::raw('COUNT(pa.id) as confirmed_count'))
                ->groupBy(
                    'partidos.id',
                    'partidos.fecha',
                    'partidos.rival',
                    'partidos.nombre_lugar',
                    'partidos.attendance_token',
                    'partidos.attendance_starts_at',
                    'partidos.attendance_ends_at'
                );
        } else {
            $matchesQuery->addSelect(DB::raw('0 as confirmed_count'));
        }

        $matches = $matchesQuery
            ->orderBy('partidos.fecha')
            ->limit($limit)
            ->get();

        $confirmedByMatch = collect();
        if (Schema::hasTable('partido_asistencias') && $matches->isNotEmpty()) {
            $confirmedPlayersQuery = DB::table('partido_asistencias as pa')
                ->leftJoin('jugadores as j', 'j.rut', '=', 'pa.jugador_rut')
                ->whereIn('pa.partido_id', $matches->pluck('id')->all())
                ->select('pa.partido_id', 'pa.jugador_rut', 'j.nombre', 'j.sobrenombre');

            if (Schema::hasColumn('jugadores', 'es_visitante')) {
                $confirmedPlayersQuery->addSelect('j.es_visitante');
            } else {
                $confirmedPlayersQuery->addSelect(DB::raw('0 as es_visitante'));
            }

            $confirmedByMatch = $confirmedPlayersQuery
                ->orderBy('pa.confirmed_at')
                ->get()
                ->groupBy('partido_id')
                ->map(fn ($group) => $group->map(fn ($player) => [
                    'rut' => (int) $player->jugador_rut,
                    'name' => trim((string) ($player->sobrenombre ?: $player->nombre ?: 'Jugador')),
                    'is_visitante' => (bool) ($player->es_visitante ?? false),
                ])->values());
        }

        return $matches->map(function ($row) use ($confirmedByMatch) {
            $row->attendance_url = $row->attendance_token
                ? route('fccs.partidos.asistencia.show', ['token' => $row->attendance_token])
                : null;
            if ($row->attendance_starts_at && $row->attendance_ends_at) {
                $now = now($this->clubTimezone());
                $start = Carbon::parse((string) $row->attendance_starts_at, $this->clubTimezone());
                $end = Carbon::parse((string) $row->attendance_ends_at, $this->clubTimezone());
                $row->is_active = $now->betweenIncluded($start, $end);
            } else {
                $row->is_active = false;
            }

            $row->confirmed_players = $confirmedByMatch->get($row->id, collect());

            return $row;
        });
    }

    private function attendanceLogs(int $perPage, string $search, string $order)
    {
        if (! Schema::hasTable('partido_asistencia_logs')) {
            return collect();
        }

        $latestIds = DB::table('partido_asistencia_logs')
            ->orderByDesc('checked_at')
            ->limit(500)
            ->pluck('id');

        if ($latestIds->isEmpty()) {
            return collect();
        }

        $query = DB::table('partido_asistencia_logs as l')
            ->leftJoin('jugadores as actor', 'actor.rut', '=', 'l.actor_rut')
            ->leftJoin('jugadores as target', 'target.rut', '=', 'l.target_rut')
            ->leftJoin('partidos as p', 'p.id', '=', 'l.partido_id')
            ->select(
                'l.id',
                'l.checked_at',
                'l.actor_rut',
                'l.target_rut',
                'p.fecha',
                'p.rival',
                'actor.nombre as actor_nombre',
                'actor.sobrenombre as actor_sobrenombre',
                'target.nombre as target_nombre',
                'target.sobrenombre as target_sobrenombre'
            )
            ->orderByDesc('l.checked_at')
            ->limit($limit)
            ->get();
    }

    private function clubTimezone(): string
    {
        return 'America/Santiago';
    }

    private function convertStoragePathToWebp(string $relativePath): array
    {
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if ($ext === 'webp') {
            return ['status' => 'skipped', 'path' => $relativePath];
        }

        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            return ['status' => 'skipped', 'path' => $relativePath];
        }

        if (! Storage::disk('public')->exists($relativePath)) {
            return ['status' => 'error', 'path' => $relativePath];
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $resource = $this->createImageResource($absolutePath, $ext);
        if (! $resource) {
            return ['status' => 'error', 'path' => $relativePath];
        }

        $resource = $this->normalizeImageOrientation($resource, $absolutePath, $ext);
        if (! $resource) {
            return ['status' => 'error', 'path' => $relativePath];
        }

        $target = pathinfo($relativePath, PATHINFO_DIRNAME);
        $baseName = pathinfo($relativePath, PATHINFO_FILENAME);
        $targetPath = ($target && $target !== '.') ? "{$target}/{$baseName}.webp" : "{$baseName}.webp";

        ob_start();
        $saved = imagewebp($resource, null, 82);
        $binary = ob_get_clean();
        imagedestroy($resource);

        if (! $saved || $binary === false) {
            return ['status' => 'error', 'path' => $relativePath];
        }

        Storage::disk('public')->put($targetPath, $binary);
        Storage::disk('public')->delete($relativePath);

        return ['status' => 'converted', 'path' => $targetPath];
    }

    private function createImageResource(string $path, string $extension)
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }


    private function normalizeImageOrientation($image, string $path, string $extension)
    {
        if (! is_resource($image) && ! ($image instanceof \GdImage)) {
            return $image;
        }

        if (! in_array($extension, ['jpg', 'jpeg'], true) || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => null,
        };

        if ($angle === null) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if (! $rotated) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

}

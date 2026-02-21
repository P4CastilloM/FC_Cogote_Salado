<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.dashboard', [
            'stats' => $stats,
            'visitSummary' => $visitSummary,
            'uniqueSummary' => $uniqueSummary,
            'deviceSeries' => $deviceSeries,
            'dailyUniqueSeries' => $dailyUniqueSeries,
        ]);
    }

    public function convertImagesToWebp(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user || ! $user->isAdmin(), 403);

        if (! function_exists('imagewebp')) {
            return redirect()->route('admin.dashboard')->with('error', 'El servidor no tiene soporte GD/WebP (imagewebp).');
        }

        $converted = 0;
        $skipped = 0;
        $errors = 0;

        $mappings = [
            ['table' => 'jugadores', 'key' => 'rut', 'fields' => ['foto']],
            ['table' => 'noticias', 'key' => 'id', 'fields' => ['foto', 'foto2']],
            ['table' => 'avisos', 'key' => 'id', 'fields' => ['foto']],
            ['table' => 'ayudantes', 'key' => 'id', 'fields' => ['foto']],
        ];

        foreach ($mappings as $mapping) {
            if (! Schema::hasTable($mapping['table'])) {
                continue;
            }

            $rows = DB::table($mapping['table'])->select(array_merge([$mapping['key']], $mapping['fields']))->get();
            foreach ($rows as $row) {
                foreach ($mapping['fields'] as $field) {
                    $current = (string) ($row->{$field} ?? '');
                    if ($current === '') {
                        continue;
                    }

                    $result = $this->convertStoragePathToWebp($current);
                    if ($result['status'] === 'converted') {
                        DB::table($mapping['table'])->where($mapping['key'], $row->{$mapping['key']})->update([$field => $result['path'], 'updated_at' => now()]);
                        $converted++;
                    } elseif ($result['status'] === 'skipped') {
                        $skipped++;
                    } else {
                        $errors++;
                    }
                }
            }
        }

        // Fotos del álbum (sin BD, se listan por carpeta)
        $albumFiles = collect(Storage::disk('public')->files('fotos'));
        foreach ($albumFiles as $filePath) {
            $result = $this->convertStoragePathToWebp($filePath);
            if ($result['status'] === 'converted') {
                $converted++;
            } elseif ($result['status'] === 'skipped') {
                $skipped++;
            } else {
                $errors++;
            }
        }

        return redirect()->route('admin.dashboard')->with('status', "Conversión WebP lista. Convertidas: {$converted}, omitidas: {$skipped}, errores: {$errors}.");
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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    /** @var array<int, string> */
    private array $adminOnlyModules = ['temporadas', 'staff', 'directiva', 'modificaciones'];

    /** @var array<string, array{table: string|null, fields: array<int, string>, label: string}> */
    private array $modules = [
        'plantel' => ['table' => 'jugadores', 'fields' => ['rut', 'nombre', 'foto', 'goles', 'asistencia', 'numero_camiseta', 'posicion'], 'label' => 'Plantel'],
        'noticias' => ['table' => 'noticias', 'fields' => ['temporada_id', 'titulo', 'subtitulo', 'cuerpo', 'fecha', 'foto', 'foto2'], 'label' => 'Noticias'],
        'avisos' => ['table' => 'avisos', 'fields' => ['temporada_id', 'titulo', 'descripcion', 'fecha', 'foto'], 'label' => 'Avisos'],
        'album' => ['table' => null, 'fields' => ['foto'], 'label' => 'Álbum / Fotos'],
        'directiva' => ['table' => 'ayudantes', 'fields' => ['nombre', 'apellido', 'descripcion_rol', 'foto', 'activo'], 'label' => 'Directiva'],
        'partidos' => ['table' => 'partidos', 'fields' => ['fecha', 'nombre_lugar', 'temporada_id'], 'label' => 'Partidos'],
        'premios' => ['table' => 'premios', 'fields' => ['temporada_id', 'nombre', 'descripcion'], 'label' => 'Premios'],
        'temporadas' => ['table' => 'temporadas', 'fields' => ['fecha_inicio', 'fecha_termino', 'descripcion'], 'label' => 'Temporadas'],
        'staff' => ['table' => 'ayudantes', 'fields' => ['nombre', 'apellido', 'descripcion_rol', 'foto', 'activo'], 'label' => 'Ayudantes / Staff'],
        'modificaciones' => ['table' => null, 'fields' => [], 'label' => 'Historial de Cambios'],
    ];

    public function index(string $module): JsonResponse
    {
        $this->authorizeModuleAccess($module);

        $config = $this->config($module);

        if ($config['table'] === null) {
            return response()->json([
                'module' => $module,
                'label' => $config['label'],
                'items' => $module === 'album' ? $this->albumFiles() : [],
                'message' => 'Módulo listo para integrar vista.',
            ]);
        }

        $pk = $this->primaryKeyFor($module);

        $items = Schema::hasTable($config['table'])
            ? DB::table($config['table'])->orderByDesc($pk)->limit(50)->get()
            : collect();

        return response()->json([
            'module' => $module,
            'label' => $config['label'],
            'table' => $config['table'],
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    public function create(string $module): JsonResponse
    {
        $this->authorizeModuleAccess($module);

        $config = $this->config($module);

        return response()->json([
            'module' => $module,
            'label' => $config['label'],
            'fillable_fields' => $config['fields'],
            'message' => 'Endpoint de creación listo. Conecta formulario cuando quieras.',
        ]);
    }

    public function store(Request $request, string $module): JsonResponse
    {
        $this->authorizeModuleAccess($module);

        $config = $this->config($module);

        if ($module === 'album') {
            $request->validate([
                'foto' => ['required', 'file', 'image', 'max:5120'],
            ]);

            $path = $request->file('foto')->store('fotos', 'public');

            return response()->json([
                'ok' => true,
                'module' => $module,
                'path' => $path,
            ], 201);
        }

        if (! $config['table'] || ! Schema::hasTable($config['table'])) {
            return response()->json(['ok' => false, 'message' => 'Tabla no disponible.'], 422);
        }

        $data = $request->only($config['fields']);

        if (Schema::hasColumn($config['table'], 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn($config['table'], 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table($config['table'])->insert($data);

        return response()->json([
            'ok' => true,
            'module' => $module,
            'message' => 'Registro creado correctamente.',
        ], 201);
    }

    public function edit(string $module, string $id): JsonResponse
    {
        $this->authorizeModuleAccess($module);

        $config = $this->config($module);

        if ($module === 'album') {
            return response()->json([
                'module' => $module,
                'message' => 'Para fotos usa delete con filename en el endpoint destroy.',
                'filename' => $id,
            ]);
        }

        if (! $config['table'] || ! Schema::hasTable($config['table'])) {
            return response()->json(['ok' => false, 'message' => 'Tabla no disponible.'], 422);
        }

        $pk = $this->primaryKeyFor($module);

        $item = DB::table($config['table'])->where($pk, $id)->first();

        return response()->json([
            'module' => $module,
            'item' => $item,
        ]);
    }

    public function update(Request $request, string $module, string $id): JsonResponse
    {
        $this->authorizeModuleAccess($module);

        $config = $this->config($module);

        if (! $config['table'] || ! Schema::hasTable($config['table'])) {
            return response()->json(['ok' => false, 'message' => 'Tabla no disponible.'], 422);
        }

        $data = $request->only($config['fields']);

        if (Schema::hasColumn($config['table'], 'updated_at')) {
            $data['updated_at'] = now();
        }

        $pk = $this->primaryKeyFor($module);

        DB::table($config['table'])->where($pk, $id)->update($data);

        return response()->json([
            'ok' => true,
            'module' => $module,
            'message' => 'Registro actualizado correctamente.',
        ]);
    }

    public function destroy(string $module, string $id): JsonResponse
    {
        $this->authorizeModuleAccess($module);

        $config = $this->config($module);

        if ($module === 'album') {
            $deleted = Storage::disk('public')->delete('fotos/'.$id);

            return response()->json([
                'ok' => $deleted,
                'module' => $module,
                'filename' => $id,
            ]);
        }

        if (! $config['table'] || ! Schema::hasTable($config['table'])) {
            return response()->json(['ok' => false, 'message' => 'Tabla no disponible.'], 422);
        }

        $pk = $this->primaryKeyFor($module);

        $deleted = DB::table($config['table'])->where($pk, $id)->delete();

        return response()->json([
            'ok' => (bool) $deleted,
            'module' => $module,
        ]);
    }

    /** @return array{table: string|null, fields: array<int, string>, label: string} */
    private function config(string $module): array
    {
        abort_unless(isset($this->modules[$module]), 404);

        return $this->modules[$module];
    }

    private function primaryKeyFor(string $module): string
    {
        return $module === 'plantel' ? 'rut' : 'id';
    }

    private function authorizeModuleAccess(string $module): void
    {
        $user = auth()->user();

        abort_if(! $user, 401);

        if (in_array($module, $this->adminOnlyModules, true) && ! $user->isAdmin()) {
            abort(403, 'No tienes permisos para este módulo.');
        }
    }

    /** @return array<int, array{filename: string, url: string}> */
    private function albumFiles(): array
    {
        return collect(Storage::disk('public')->files('fotos'))
            ->filter(fn (string $path) => preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $path) === 1)
            ->map(fn (string $path) => [
                'filename' => basename($path),
                'url' => asset('storage/'.$path),
            ])
            ->values()
            ->all();
    }
}

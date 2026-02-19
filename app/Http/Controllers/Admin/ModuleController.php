<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /** @var array<int, string> */
    private array $adminOnlyModules = ['temporadas', 'staff', 'directiva', 'modificaciones'];

    /** @var array<string, array{table: string|null, fields: array<int, string>, label: string, icon: string}> */
    private array $modules = [
        'plantel' => ['table' => 'jugadores', 'fields' => ['rut', 'nombre', 'foto', 'goles', 'asistencia', 'numero_camiseta', 'posicion'], 'label' => 'Plantel', 'icon' => '👥'],
        'noticias' => ['table' => 'noticias', 'fields' => ['temporada_id', 'titulo', 'subtitulo', 'cuerpo', 'fecha', 'foto', 'foto2'], 'label' => 'Noticias', 'icon' => '📰'],
        'avisos' => ['table' => 'avisos', 'fields' => ['temporada_id', 'titulo', 'descripcion', 'fecha', 'foto'], 'label' => 'Avisos', 'icon' => '📢'],
        'album' => ['table' => null, 'fields' => ['foto'], 'label' => 'Álbum / Fotos', 'icon' => '📸'],
        'directiva' => ['table' => 'ayudantes', 'fields' => ['nombre', 'apellido', 'descripcion_rol', 'foto', 'activo'], 'label' => 'Directiva', 'icon' => '🏛️'],
        'partidos' => ['table' => 'partidos', 'fields' => ['fecha', 'nombre_lugar', 'temporada_id'], 'label' => 'Partidos', 'icon' => '📅'],
        'premios' => ['table' => 'premios', 'fields' => ['temporada_id', 'nombre', 'descripcion'], 'label' => 'Premios', 'icon' => '🏆'],
        'temporadas' => ['table' => 'temporadas', 'fields' => ['fecha_inicio', 'fecha_termino', 'descripcion'], 'label' => 'Temporadas', 'icon' => '⏳'],
        'staff' => ['table' => 'ayudantes', 'fields' => ['nombre', 'apellido', 'descripcion_rol', 'foto', 'activo'], 'label' => 'Ayudantes / Staff', 'icon' => '🤝'],
        'modificaciones' => ['table' => null, 'fields' => [], 'label' => 'Historial de Cambios', 'icon' => '🧾'],
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

    public function create(string $module): JsonResponse|View
    {
        $this->authorizeModuleAccess($module);

        if ($module === 'plantel') {
            return view('admin.plantel-create');
        }

        if (in_array($module, ['noticias', 'avisos', 'partidos', 'premios', 'temporadas', 'staff', 'directiva', 'album'], true)) {
            return view('admin.module-create', [
                'module' => $module,
                'config' => $this->config($module),
                'temporadas' => Schema::hasTable('temporadas') ? DB::table('temporadas')->orderByDesc('id')->get() : collect(),
            ]);
        }

        $config = $this->config($module);

        return response()->json([
            'module' => $module,
            'label' => $config['label'],
            'fillable_fields' => $config['fields'],
            'message' => 'Endpoint de creación listo. Conecta formulario cuando quieras.',
        ]);
    }

    public function store(Request $request, string $module): JsonResponse|RedirectResponse
    {
        $this->authorizeModuleAccess($module);

        if ($module === 'plantel') {
            $data = $request->validate([
                'rut' => ['required', 'integer', 'min:1', 'max:99999999', 'unique:jugadores,rut'],
                'nombre' => ['required', 'string', 'max:25'],
                'numero_camiseta' => ['required', 'integer', 'min:1', 'max:65535'],
                'posicion' => ['required', 'in:ARQUERO,DELANTERO,CENTRAL,DEFENSA'],
                'goles' => ['nullable', 'integer', 'min:0'],
                'asistencia' => ['nullable', 'integer', 'min:0'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('jugadores', 'public');
            }

            $data['goles'] = $data['goles'] ?? 0;
            $data['asistencia'] = $data['asistencia'] ?? 0;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('jugadores')->insert($data);

            return redirect()->route('admin.plantel.create')->with('status', 'item-created');
        }

        if ($module === 'noticias') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'titulo' => ['required', 'string', 'max:60'],
                'subtitulo' => ['nullable', 'string', 'max:100'],
                'cuerpo' => ['required', 'string'],
                'fecha' => ['required', 'date'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'foto2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('noticias', 'public');
            }
            if ($request->hasFile('foto2')) {
                $data['foto2'] = $request->file('foto2')->store('noticias', 'public');
            }

            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('noticias')->insert($data);

            return redirect()->route('admin.noticias.create')->with('status', 'item-created');
        }

        if ($module === 'avisos') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'titulo' => ['required', 'string', 'max:50'],
                'descripcion' => ['required', 'string', 'max:120'],
                'fecha' => ['required', 'date'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('avisos', 'public');
            }

            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('avisos')->insert($data);

            return redirect()->route('admin.avisos.create')->with('status', 'item-created');
        }

        if ($module === 'partidos') {
            $data = $request->validate([
                'fecha' => ['required', 'date'],
                'nombre_lugar' => ['required', 'string', 'max:100'],
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
            ]);
            DB::table('partidos')->insert($data);

            return redirect()->route('admin.partidos.create')->with('status', 'item-created');
        }

        if ($module === 'premios') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'nombre' => ['required', 'string', 'max:20'],
                'descripcion' => ['nullable', 'string', 'max:50'],
            ]);
            DB::table('premios')->insert($data);

            return redirect()->route('admin.premios.create')->with('status', 'item-created');
        }

        if ($module === 'temporadas') {
            $data = $request->validate([
                'fecha_inicio' => ['required', 'date'],
                'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
                'descripcion' => ['nullable', 'string', 'max:150'],
            ]);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('temporadas')->insert($data);

            return redirect()->route('admin.temporadas.create')->with('status', 'item-created');
        }

        if (in_array($module, ['staff', 'directiva'], true)) {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'max:20'],
                'apellido' => ['nullable', 'string', 'max:20'],
                'descripcion_rol' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable', 'boolean'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('ayudantes', 'public');
            }

            $data['activo'] = $request->boolean('activo', true);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('ayudantes')->insert($data);

            return redirect()->route("admin.{$module}.create")->with('status', 'item-created');
        }

        if ($module === 'album') {
            $request->validate([
                'foto' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            $request->file('foto')->store('fotos', 'public');

            return redirect()->route('admin.album.create')->with('status', 'item-created');
        }

        $config = $this->config($module);

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

        return response()->json(['module' => $module, 'item' => $item]);
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

        return response()->json(['ok' => true, 'module' => $module, 'message' => 'Registro actualizado correctamente.']);
    }

    public function destroy(string $module, string $id): JsonResponse
    {
        $this->authorizeModuleAccess($module);
        $config = $this->config($module);

        if ($module === 'album') {
            $deleted = Storage::disk('public')->delete('fotos/'.$id);
            return response()->json(['ok' => $deleted, 'module' => $module, 'filename' => $id]);
        }

        if (! $config['table'] || ! Schema::hasTable($config['table'])) {
            return response()->json(['ok' => false, 'message' => 'Tabla no disponible.'], 422);
        }

        $pk = $this->primaryKeyFor($module);
        $deleted = DB::table($config['table'])->where($pk, $id)->delete();

        return response()->json(['ok' => (bool) $deleted, 'module' => $module]);
    }

    /** @return array{table: string|null, fields: array<int, string>, label: string, icon: string} */
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

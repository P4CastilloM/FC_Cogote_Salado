<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        'avisos' => ['table' => 'avisos', 'fields' => ['temporada_id', 'titulo', 'descripcion', 'fecha', 'foto', 'fijado'], 'label' => 'Avisos', 'icon' => '📢'],
        'album' => ['table' => null, 'fields' => ['foto'], 'label' => 'Álbum / Fotos', 'icon' => '📸'],
        'directiva' => ['table' => 'ayudantes', 'fields' => ['nombre', 'apellido', 'descripcion_rol', 'prioridad', 'foto', 'activo'], 'label' => 'Directiva', 'icon' => '🏛️'],
        'partidos' => ['table' => 'partidos', 'fields' => ['fecha', 'hora', 'rival', 'nombre_lugar', 'direccion', 'temporada_id'], 'label' => 'Partidos', 'icon' => '📅'],
        'premios' => ['table' => 'premios', 'fields' => ['temporada_id', 'nombre', 'descripcion'], 'label' => 'Premios', 'icon' => '🏆'],
        'temporadas' => ['table' => 'temporadas', 'fields' => ['fecha_inicio', 'fecha_termino', 'descripcion'], 'label' => 'Temporadas', 'icon' => '⏳'],
        'staff' => ['table' => 'ayudantes', 'fields' => ['nombre', 'apellido', 'descripcion_rol', 'foto', 'activo'], 'label' => 'Ayudantes / Staff', 'icon' => '🤝'],
        'modificaciones' => ['table' => null, 'fields' => [], 'label' => 'Historial de Cambios', 'icon' => '🧾'],
    ];

    public function index(Request $request, string $module): JsonResponse|View
    {
        $this->authorizeModuleAccess($module);
        $config = $this->config($module);

        if ($module === 'modificaciones') {
            $search = trim((string) $request->query('q', ''));
            $order = $request->query('order', 'recent') === 'oldest' ? 'asc' : 'desc';
            $action = trim((string) $request->query('action', ''));

            $logs = DB::table('modificaciones')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($nested) use ($search): void {
                        $nested->where('actor_name', 'like', "%{$search}%")
                            ->orWhere('summary', 'like', "%{$search}%")
                            ->orWhere('module', 'like', "%{$search}%")
                            ->orWhere('item_key', 'like', "%{$search}%");
                    });
                })
                ->when($action !== '', fn ($query) => $query->where('action', $action))
                ->orderBy('created_at', $order)
                ->limit(200)
                ->get();

            return view('admin.modificaciones-index', [
                'module' => $module,
                'config' => $config,
                'logs' => $logs,
                'search' => $search,
                'order' => $order,
                'action' => $action,
            ]);
        }

        if ($config['table'] === null) {
            return response()->json([
                'module' => $module,
                'label' => $config['label'],
                'items' => $module === 'album' ? $this->albumFiles() : [],
                'message' => 'Módulo listo para integrar vista.',
            ]);
        }

        $q = trim((string) $request->query('q', ''));
        $pk = $this->primaryKeyFor($module);

        $itemsQuery = DB::table($config['table']);
        if ($q !== '') {
            $columns = $this->searchableColumnsFor($module);
            $itemsQuery->where(function ($query) use ($columns, $q): void {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'like', "%{$q}%");
                }
            });
        }

        $items = $module === 'directiva'
            ? $itemsQuery->orderBy('prioridad')->orderByDesc($pk)->limit(30)->get()
            : $itemsQuery->orderByDesc($pk)->limit(30)->get();

        return view('admin.module-index', [
            'module' => $module,
            'config' => $config,
            'query' => $q,
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
                'temporadas' => $this->temporadas(),
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
                'posicion' => ['required', 'in:ARQUERO,DELANTERO,MEDIOCAMPISTA,CENTRAL,DEFENSA'],
                'goles' => ['nullable', 'integer', 'min:0'],
                'asistencia' => ['nullable', 'integer', 'min:0'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if (($data['posicion'] ?? null) === 'MEDIOCAMPISTA') {
                $data['posicion'] = 'CENTRAL';
            }

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('jugadores', 'public');
            }

            $data['goles'] = $data['goles'] ?? 0;
            $data['asistencia'] = $data['asistencia'] ?? 0;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('jugadores')->insert($data);
            $this->logModification('plantel', 'añadir', (string) $data['rut'], $data['nombre'] ?? null);

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
            $this->logModification('noticias', 'añadir', null, $data['titulo'] ?? null);

            return redirect()->route('admin.noticias.create')->with('status', 'item-created');
        }

        if ($module === 'avisos') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'titulo' => ['required', 'string', 'max:50'],
                'descripcion' => ['required', 'string', 'max:120'],
                'fecha' => ['required', 'date'],
                'fijado' => ['nullable', 'boolean'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if (Schema::hasColumn('avisos', 'fijado')) {
                $data['fijado'] = $request->boolean('fijado');
            } else {
                unset($data['fijado']);
            }

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('avisos', 'public');
            }

            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('avisos')->insert($data);
            $this->logModification('avisos', 'añadir', null, $data['titulo'] ?? null);

            return redirect()->route('admin.avisos.create')->with('status', 'item-created');
        }

        if ($module === 'partidos') {
            $data = $request->validate([
                'fecha' => ['required', 'date'],
                'nombre_lugar' => ['required', 'string', 'max:100'],
                'rival' => ['required', 'string', 'max:100'],
                'hora' => ['nullable', 'date_format:H:i'],
                'direccion' => ['nullable', 'string', 'max:180'],
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
            ]);
            DB::table('partidos')->insert($data);
            $this->logModification('partidos', 'añadir', null, $data['nombre_lugar'] ?? null);

            return redirect()->route('admin.partidos.create')->with('status', 'item-created');
        }

        if ($module === 'premios') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'nombre' => ['required', 'string', 'max:20'],
                'descripcion' => ['nullable', 'string', 'max:50'],
            ]);
            DB::table('premios')->insert($data);
            $this->logModification('premios', 'añadir', null, $data['nombre'] ?? null);

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
            $this->logModification('temporadas', 'añadir', null, $data['descripcion'] ?? 'Temporada');

            return redirect()->route('admin.temporadas.create')->with('status', 'item-created');
        }

        if (in_array($module, ['staff', 'directiva'], true)) {
            $rules = [
                'nombre' => ['required', 'string', 'max:20'],
                'apellido' => ['nullable', 'string', 'max:20'],
                'descripcion_rol' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable', 'boolean'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ];

            if ($module === 'directiva') {
                $rules['prioridad'] = ['required', 'integer', 'min:1', 'max:10'];
            }

            if ($module === 'staff') {
                $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
                $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            }

            $data = $request->validate($rules);

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('ayudantes', 'public');
            }

            $data['activo'] = $request->boolean('activo', true);
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::transaction(function () use ($module, $data): void {
                DB::table('ayudantes')->insert([
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'] ?? null,
                    'descripcion_rol' => $data['descripcion_rol'] ?? null,
                    'foto' => $data['foto'] ?? null,
                    'activo' => $data['activo'],
                    'prioridad' => $module === 'directiva' ? (int) ($data['prioridad'] ?? 10) : 10,
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ]);

                if ($module === 'staff') {
                    DB::table('users')->insert([
                        'name' => trim($data['nombre'].' '.($data['apellido'] ?? '')),
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                        'role' => 'ayudante',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            $this->logModification($module, 'añadir', null, $data['nombre'] ?? null);

            return redirect()->route("admin.{$module}.create")->with('status', 'item-created');
        }

        if ($module === 'album') {
            $request->validate([
                'foto' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            $path = $request->file('foto')->store('fotos', 'public');
            $this->logModification('album', 'añadir', basename($path), basename($path));

            return redirect()->route('admin.album.create')->with('status', 'item-created');
        }

        return response()->json(['ok' => false, 'message' => 'Módulo no soportado.'], 422);
    }

    public function edit(string $module, string $id): JsonResponse|View
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
        abort_unless($item, 404);

        if ($module === 'plantel') {
            return view('admin.plantel-edit', ['item' => $item]);
        }

        return view('admin.module-edit', [
            'module' => $module,
            'config' => $config,
            'item' => $item,
            'temporadas' => $this->temporadas(),
        ]);
    }

    public function update(Request $request, string $module, string $id): JsonResponse|RedirectResponse
    {
        $this->authorizeModuleAccess($module);

        if ($module === 'plantel') {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'max:25'],
                'numero_camiseta' => ['required', 'integer', 'min:1', 'max:65535'],
                'posicion' => ['required', 'in:ARQUERO,DELANTERO,MEDIOCAMPISTA,CENTRAL,DEFENSA'],
                'goles' => ['nullable', 'integer', 'min:0'],
                'asistencia' => ['nullable', 'integer', 'min:0'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
            if (($data['posicion'] ?? null) === 'MEDIOCAMPISTA') {
                $data['posicion'] = 'CENTRAL';
            }

            if ($request->hasFile('foto')) {
                $old = DB::table('jugadores')->where('rut', $id)->value('foto');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data['foto'] = $request->file('foto')->store('jugadores', 'public');
            }
            $data['goles'] = $data['goles'] ?? 0;
            $data['asistencia'] = $data['asistencia'] ?? 0;
            $data['updated_at'] = now();
            DB::table('jugadores')->where('rut', $id)->update($data);
            $this->logModification('plantel', 'actualizar', $id, $data['nombre'] ?? null);
            return redirect()->route('admin.plantel.edit', $id)->with('status', 'item-updated');
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
            foreach (['foto', 'foto2'] as $field) {
                if ($request->hasFile($field)) {
                    $old = DB::table('noticias')->where('id', $id)->value($field);
                    if ($old) {
                        Storage::disk('public')->delete($old);
                    }
                    $data[$field] = $request->file($field)->store('noticias', 'public');
                }
            }
            $data['updated_at'] = now();
            DB::table('noticias')->where('id', $id)->update($data);
            $this->logModification('noticias', 'actualizar', $id, $data['titulo'] ?? null);
            return redirect()->route('admin.noticias.edit', $id)->with('status', 'item-updated');
        }

        if ($module === 'avisos') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'titulo' => ['required', 'string', 'max:50'],
                'descripcion' => ['required', 'string', 'max:120'],
                'fecha' => ['required', 'date'],
                'fijado' => ['nullable', 'boolean'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
            if (Schema::hasColumn('avisos', 'fijado')) {
                $data['fijado'] = $request->boolean('fijado');
            } else {
                unset($data['fijado']);
            }
            if ($request->hasFile('foto')) {
                $old = DB::table('avisos')->where('id', $id)->value('foto');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data['foto'] = $request->file('foto')->store('avisos', 'public');
            }
            $data['updated_at'] = now();
            DB::table('avisos')->where('id', $id)->update($data);
            $this->logModification('avisos', 'actualizar', $id, $data['titulo'] ?? null);
            return redirect()->route('admin.avisos.edit', $id)->with('status', 'item-updated');
        }

        if ($module === 'partidos') {
            $data = $request->validate([
                'fecha' => ['required', 'date'],
                'nombre_lugar' => ['required', 'string', 'max:100'],
                'rival' => ['required', 'string', 'max:100'],
                'hora' => ['nullable', 'date_format:H:i'],
                'direccion' => ['nullable', 'string', 'max:180'],
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
            ]);
            DB::table('partidos')->where('id', $id)->update($data);
            $this->logModification('partidos', 'actualizar', $id, $data['nombre_lugar'] ?? null);
            return redirect()->route('admin.partidos.edit', $id)->with('status', 'item-updated');
        }

        if ($module === 'premios') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'nombre' => ['required', 'string', 'max:20'],
                'descripcion' => ['nullable', 'string', 'max:50'],
            ]);
            DB::table('premios')->where('id', $id)->update($data);
            $this->logModification('premios', 'actualizar', $id, $data['nombre'] ?? null);
            return redirect()->route('admin.premios.edit', $id)->with('status', 'item-updated');
        }

        if ($module === 'temporadas') {
            $data = $request->validate([
                'fecha_inicio' => ['required', 'date'],
                'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
                'descripcion' => ['nullable', 'string', 'max:150'],
            ]);
            $data['updated_at'] = now();
            DB::table('temporadas')->where('id', $id)->update($data);
            $this->logModification('temporadas', 'actualizar', $id, $data['descripcion'] ?? null);
            return redirect()->route('admin.temporadas.edit', $id)->with('status', 'item-updated');
        }

        if (in_array($module, ['staff', 'directiva'], true)) {
            $rules = [
                'nombre' => ['required', 'string', 'max:20'],
                'apellido' => ['nullable', 'string', 'max:20'],
                'descripcion_rol' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable', 'boolean'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ];

            if ($module === 'directiva') {
                $rules['prioridad'] = ['required', 'integer', 'min:1', 'max:10'];
            }

            $data = $request->validate($rules);
            if ($request->hasFile('foto')) {
                $old = DB::table('ayudantes')->where('id', $id)->value('foto');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data['foto'] = $request->file('foto')->store('ayudantes', 'public');
            }
            $data['activo'] = $request->boolean('activo', true);
            if ($module !== 'directiva') {
                unset($data['prioridad']);
            }
            $data['updated_at'] = now();
            DB::table('ayudantes')->where('id', $id)->update($data);
            $this->logModification($module, 'actualizar', $id, $data['nombre'] ?? null);

            if ($module === 'staff') {
                $email = $request->input('email');
                if ($email) {
                    DB::table('users')
                        ->where('email', $email)
                        ->update([
                            'name' => trim($data['nombre'].' '.($data['apellido'] ?? '')),
                            'updated_at' => now(),
                        ]);
                }
            }

            return redirect()->route("admin.{$module}.edit", $id)->with('status', 'item-updated');
        }

        return response()->json(['ok' => false, 'message' => 'Módulo no soportado.'], 422);
    }

    public function destroy(string $module, string $id): JsonResponse|RedirectResponse
    {
        $this->authorizeModuleAccess($module);
        $config = $this->config($module);

        if ($module === 'album') {
            $deleted = Storage::disk('public')->delete('fotos/'.$id);
            if ($deleted) {
                $this->logModification('album', 'eliminar', $id, $id);
            }
            return response()->json(['ok' => $deleted, 'module' => $module, 'filename' => $id]);
        }

        if (! $config['table'] || ! Schema::hasTable($config['table'])) {
            return response()->json(['ok' => false, 'message' => 'Tabla no disponible.'], 422);
        }

        $pk = $this->primaryKeyFor($module);
        $toDelete = DB::table($config['table'])->where($pk, $id)->first();
        $deleted = DB::table($config['table'])->where($pk, $id)->delete();
        if ($deleted) {
            $summary = $this->summaryFromRow($module, $toDelete);
            $this->logModification($module, 'eliminar', $id, $summary);
        }

        return redirect()->route("admin.{$module}.index")
            ->with($deleted ? 'status' : 'error', $deleted ? 'item-deleted' : 'delete-failed');
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


    private function logModification(string $module, string $action, ?string $itemKey = null, ?string $summary = null): void
    {
        if (! Schema::hasTable('modificaciones')) {
            return;
        }

        $user = auth()->user();

        DB::table('modificaciones')->insert([
            'user_id' => $user?->id,
            'actor_name' => $user?->name ?? 'Sistema',
            'actor_role' => $user?->role,
            'module' => $module,
            'action' => $action,
            'item_key' => $itemKey,
            'summary' => $summary,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function summaryFromRow(string $module, object|null $row): ?string
    {
        if (! $row) {
            return null;
        }

        return match ($module) {
            'plantel' => $row->nombre ?? null,
            'noticias' => $row->titulo ?? null,
            'avisos' => $row->titulo ?? null,
            'partidos' => trim((string) (($row->rival ?? 'Partido').' · '.($row->nombre_lugar ?? ''))),
            'premios' => $row->nombre ?? null,
            'temporadas' => $row->descripcion ?? null,
            'staff', 'directiva' => trim(($row->nombre ?? '').' '.($row->apellido ?? '')),
            default => null,
        };
    }

    /** @return array<int, string> */
    private function searchableColumnsFor(string $module): array
    {
        return match ($module) {
            'plantel' => ['rut', 'nombre'],
            'noticias' => ['titulo', 'subtitulo'],
            'avisos' => ['titulo', 'descripcion'],
            'partidos' => ['rival', 'nombre_lugar', 'direccion', 'fecha'],
            'premios' => ['nombre', 'descripcion'],
            'temporadas' => ['descripcion', 'fecha_inicio'],
            'staff', 'directiva' => ['nombre', 'apellido'],
            default => [$this->primaryKeyFor($module)],
        };
    }

    private function temporadas()
    {
        return Schema::hasTable('temporadas') ? DB::table('temporadas')->orderByDesc('id')->get() : collect();
    }
}

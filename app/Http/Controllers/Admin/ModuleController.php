<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /** @var array<int, string> */
    private array $adminOnlyModules = ['temporadas', 'staff', 'directiva', 'modificaciones'];

    /** @var array<string, array{table: string|null, fields: array<int, string>, label: string, icon: string}> */
    private array $modules = [
        'plantel' => ['table' => 'jugadores', 'fields' => ['rut', 'nombre', 'sobrenombre', 'foto', 'goles', 'asistencia', 'numero_camiseta', 'posicion'], 'label' => 'Plantel', 'icon' => '👥'],
        'visitantes' => ['table' => 'jugadores', 'fields' => ['rut', 'nombre', 'apellido', 'sobrenombre', 'goles', 'asistencia'], 'label' => 'Jugadores Visitantes', 'icon' => '🧳'],
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
            $action = trim((string) $request->query('action', ''));
            $moduleFilter = trim((string) $request->query('module_filter', ''));
            $actorFilter = trim((string) $request->query('actor_filter', ''));
            $sortBy = trim((string) $request->query('sort_by', 'created_at'));
            $sortDir = $request->query('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
            $perPage = (int) $request->query('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            $allowedSorts = ['created_at', 'action', 'module', 'actor_name'];
            if (! in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'created_at';
            }

            $latestIds = DB::table('modificaciones')
                ->orderByDesc('created_at')
                ->limit(500)
                ->pluck('id');

            $logs = collect();
            if ($latestIds->isNotEmpty()) {
                $logs = DB::table('modificaciones')
                    ->whereIn('id', $latestIds->all())
                    ->when($search !== '', function ($query) use ($search): void {
                        $query->where(function ($nested) use ($search): void {
                            $nested->where('actor_name', 'like', "%{$search}%")
                                ->orWhere('summary', 'like', "%{$search}%")
                                ->orWhere('module', 'like', "%{$search}%")
                                ->orWhere('item_key', 'like', "%{$search}%");
                        });
                    })
                    ->when($action !== '', fn ($query) => $query->where('action', $action))
                    ->when($moduleFilter !== '', fn ($query) => $query->where('module', $moduleFilter))
                    ->when($actorFilter !== '', fn ($query) => $query->where('actor_name', 'like', "%{$actorFilter}%"))
                    ->orderBy($sortBy, $sortDir)
                    ->paginate($perPage)
                    ->withQueryString();
            }

            $modulesFilterOptions = DB::table('modificaciones')
                ->whereIn('id', $latestIds->all())
                ->select('module')
                ->distinct()
                ->orderBy('module')
                ->pluck('module');

            return view('admin.modificaciones-index', [
                'module' => $module,
                'config' => $config,
                'logs' => $logs,
                'search' => $search,
                'action' => $action,
                'moduleFilter' => $moduleFilter,
                'actorFilter' => $actorFilter,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
                'perPage' => $perPage,
                'modulesFilterOptions' => $modulesFilterOptions,
            ]);
        }

        if ($module === 'album') {
            $albumName = trim((string) $request->query('album', ''));
            $albumDate = trim((string) $request->query('album_date', ''));

            return view('admin.album-index', [
                'module' => $module,
                'config' => $config,
                'query' => $albumName,
                'albumDate' => $albumDate,
                'albums' => $this->albumsForAdmin($albumName, $albumDate),
                'photos' => $this->albumItemsForAdmin($albumName, $albumDate),
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
        if ($module === 'plantel' && Schema::hasColumn('jugadores', 'es_visitante')) {
            $itemsQuery->where('es_visitante', false);
        }
        if ($module === 'visitantes' && Schema::hasColumn('jugadores', 'es_visitante')) {
            $itemsQuery->where('es_visitante', true);
        }
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

        if ($module === 'visitantes') {
            return view('admin.visitantes-create');
        }

        if (in_array($module, ['noticias', 'avisos', 'partidos', 'premios', 'temporadas', 'staff', 'directiva', 'album'], true)) {
            return view('admin.module-create', [
                'module' => $module,
                'config' => $this->config($module),
                'temporadas' => $this->temporadas(),
                'albums' => $module === 'album' ? $this->albumCatalog() : collect(),
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
                'apellido' => ['nullable', 'string', 'max:50'],
                'sobrenombre' => ['nullable', 'string', 'max:25'],
                'numero_camiseta' => ['required', 'integer', 'min:1', 'max:65535'],
                'posicion' => ['required', 'in:ARQUERO,DELANTERO,MEDIOCAMPISTA,CENTRAL,DEFENSA'],
                'goles' => ['nullable', 'integer', 'min:0'],
                'asistencia' => ['nullable', 'integer', 'min:0'],
                'atajadas' => ['nullable', 'integer', 'min:0'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            if (($data['posicion'] ?? null) === 'MEDIOCAMPISTA') {
                $data['posicion'] = 'CENTRAL';
            }

            if ($request->hasFile('foto')) {
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'jugadores');
            }

            $data['goles'] = $data['goles'] ?? 0;
            $data['asistencia'] = $data['asistencia'] ?? 0;
            if (Schema::hasColumn('jugadores', 'atajadas')) {
                $data['atajadas'] = $data['atajadas'] ?? 0;
            } else {
                unset($data['atajadas']);
            }
            $data['es_visitante'] = false;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('jugadores')->insert($data);
            $this->logModification('plantel', 'añadir', (string) $data['rut'], $data['nombre'] ?? null);

            return redirect()->route('admin.plantel.create')->with('status', 'item-created');
        }


        if ($module === 'visitantes') {
            $data = $request->validate([
                'rut' => ['required', 'integer', 'min:1', 'max:99999999', 'unique:jugadores,rut'],
                'nombre' => ['required', 'string', 'max:25'],
                'apellido' => ['nullable', 'string', 'max:50'],
                'sobrenombre' => ['nullable', 'string', 'max:25'],
            ]);

            $data['numero_camiseta'] = 999;
            $data['posicion'] = 'DEFENSA';
            $data['goles'] = 0;
            $data['asistencia'] = 0;
            if (Schema::hasColumn('jugadores', 'atajadas')) {
                $data['atajadas'] = 0;
            }
            if (Schema::hasColumn('jugadores', 'partidos_jugados')) {
                $data['partidos_jugados'] = 0;
            }
            if (Schema::hasColumn('jugadores', 'es_visitante')) {
                $data['es_visitante'] = true;
            }
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('jugadores')->insert($data);
            $this->logModification('visitantes', 'añadir', (string) $data['rut'], $data['nombre'] ?? null);

            return redirect()->route('admin.visitantes.create')->with('status', 'item-created');
        }


        if ($module === 'noticias') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'titulo' => ['required', 'string', 'max:60'],
                'subtitulo' => ['nullable', 'string', 'max:100'],
                'cuerpo' => ['required', 'string'],
                'fecha' => ['required', 'date'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'foto2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            if ($request->hasFile('foto')) {
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'noticias');
            }
            if ($request->hasFile('foto2')) {
                $data['foto2'] = $this->storeUploadedWebp($request->file('foto2'), 'noticias');
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
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            if (Schema::hasColumn('avisos', 'fijado')) {
                $data['fijado'] = $request->boolean('fijado');
            } else {
                unset($data['fijado']);
            }

            if ($request->hasFile('foto')) {
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'avisos');
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

            $attendanceData = $this->buildAttendanceWindow($data['fecha']);
            $data['attendance_token'] = Str::random(48);
            $data['attendance_starts_at'] = $attendanceData['starts_at'];
            $data['attendance_ends_at'] = $attendanceData['ends_at'];

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
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'ayudantes');
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
            @set_time_limit(600);
            $mode = $request->input('upload_mode', 'single');

            if ($mode === 'album') {
                if (empty($request->file('fotos'))) {
                    $message = 'No se recibieron archivos. Revisa límites de servidor (post_max_size / upload_max_filesize / max_file_uploads) o intenta lotes más pequeños.';
                    return $request->expectsJson()
                        ? response()->json(['ok' => false, 'message' => $message], 422)
                        : redirect()->back()->withErrors(['fotos' => $message])->withInput();
                }

                $data = $request->validate([
                    'upload_mode' => ['required', Rule::in(['single', 'album'])],
                    'album_nombre' => ['required', 'string', 'max:90'],
                    'upload_token' => ['nullable', 'string', 'max:120'],
                    'chunk_index' => ['nullable', 'integer', 'min:0'],
                    'fotos' => ['required', 'array', 'min:1', 'max:80'],
                    'fotos.*' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/bmp,image/avif,image/tiff', 'max:20480'],
                ]);

                $uploadToken = trim((string) ($data['upload_token'] ?? ''));
                $chunkIndex = isset($data['chunk_index']) ? (int) $data['chunk_index'] : null;
                if ($request->expectsJson() && $uploadToken !== '' && $chunkIndex !== null) {
                    $chunkKey = "album-upload:{$uploadToken}:{$chunkIndex}";
                    $accepted = Cache::add($chunkKey, true, now()->addMinutes(30));
                    if (! $accepted) {
                        return response()->json(['ok' => true, 'duplicate' => true]);
                    }
                }

                $albumId = $this->resolveAlbumId(null, $data['album_nombre']);
                $storedPaths = [];

                foreach ($request->file('fotos', []) as $file) {
                    $path = $this->storeUploadedWebp($file, 'fotos', true);

                    if ($path === '' || ! Storage::disk('public')->exists($path)) {
                        foreach ($storedPaths as $storedPath) {
                            Storage::disk('public')->delete($storedPath);
                        }

                        $message = 'No se pudo guardar una o más fotos en storage/app/public/fotos. Revisa permisos y enlace storage:link.';
                        logger()->error('Falló guardado de foto en álbum', [
                            'disk' => 'public',
                            'path' => $path,
                            'root' => config('filesystems.disks.public.root'),
                            'file' => $file->getClientOriginalName(),
                        ]);

                        return $request->expectsJson()
                            ? response()->json(['ok' => false, 'message' => $message], 500)
                            : redirect()->back()->withErrors(['fotos' => $message])->withInput();
                    }

                    $storedPaths[] = $path;
                    $this->persistPhotoItem($path, $albumId);
                }

                $this->logModification('album', 'añadir', (string) $albumId, 'Álbum: '.$data['album_nombre']);

                if ($request->expectsJson()) {
                    return response()->json(['ok' => true, 'album_id' => $albumId]);
                }
            } else {
                if (! $request->hasFile('foto')) {
                    $message = 'No se recibió la foto. Revisa límites de servidor (post_max_size / upload_max_filesize) e intenta de nuevo.';
                    return $request->expectsJson()
                        ? response()->json(['ok' => false, 'message' => $message], 422)
                        : redirect()->back()->withErrors(['foto' => $message])->withInput();
                }

                $data = $request->validate([
                    'upload_mode' => ['required', Rule::in(['single', 'album'])],
                    'album_id' => ['nullable', 'integer', 'exists:foto_albums,id'],
                    'single_album_nombre' => ['nullable', 'string', 'max:90'],
                    'foto' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/bmp,image/avif,image/tiff', 'max:20480'],
                ]);

                $albumId = $this->resolveAlbumId($data['album_id'] ?? null, $data['single_album_nombre'] ?? null);
                $path = $this->storeUploadedWebp($request->file('foto'), 'fotos', true);

                if ($path === '' || ! Storage::disk('public')->exists($path)) {
                    $message = 'No se pudo guardar la foto en storage/app/public/fotos. Revisa permisos y enlace storage:link.';
                    logger()->error('Falló guardado de foto individual', [
                        'disk' => 'public',
                        'path' => $path,
                        'root' => config('filesystems.disks.public.root'),
                        'file' => $request->file('foto')?->getClientOriginalName(),
                    ]);

                    return $request->expectsJson()
                        ? response()->json(['ok' => false, 'message' => $message], 500)
                        : redirect()->back()->withErrors(['foto' => $message])->withInput();
                }

                $this->persistPhotoItem($path, $albumId);
                $this->logModification('album', 'añadir', basename($path), basename($path));

                if ($request->expectsJson()) {
                    return response()->json(['ok' => true, 'album_id' => $albumId, 'path' => $path]);
                }
            }

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

        if ($module === 'visitantes') {
            return view('admin.visitantes-edit', ['item' => $item]);
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
                'apellido' => ['nullable', 'string', 'max:50'],
                'sobrenombre' => ['nullable', 'string', 'max:25'],
                'numero_camiseta' => ['required', 'integer', 'min:1', 'max:65535'],
                'posicion' => ['required', 'in:ARQUERO,DELANTERO,MEDIOCAMPISTA,CENTRAL,DEFENSA'],
                'goles' => ['nullable', 'integer', 'min:0'],
                'asistencia' => ['nullable', 'integer', 'min:0'],
                'atajadas' => ['nullable', 'integer', 'min:0'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
            if (($data['posicion'] ?? null) === 'MEDIOCAMPISTA') {
                $data['posicion'] = 'CENTRAL';
            }

            if ($request->hasFile('foto')) {
                $old = DB::table('jugadores')->where('rut', $id)->value('foto');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'jugadores');
            }
            $data['goles'] = $data['goles'] ?? 0;
            $data['asistencia'] = $data['asistencia'] ?? 0;
            if (Schema::hasColumn('jugadores', 'atajadas')) {
                $data['atajadas'] = $data['atajadas'] ?? 0;
            } else {
                unset($data['atajadas']);
            }
            $data['es_visitante'] = false;
            $data['updated_at'] = now();
            DB::table('jugadores')->where('rut', $id)->update($data);
            $this->logModification('plantel', 'actualizar', $id, $data['nombre'] ?? null);
            return redirect()->route('admin.plantel.edit', $id)->with('status', 'item-updated');
        }


        if ($module === 'visitantes') {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'max:25'],
                'apellido' => ['nullable', 'string', 'max:50'],
                'sobrenombre' => ['nullable', 'string', 'max:25'],
                'goles' => ['nullable', 'integer', 'min:0'],
                'asistencia' => ['nullable', 'integer', 'min:0'],
                'atajadas' => ['nullable', 'integer', 'min:0'],
            ]);

            $data['goles'] = $data['goles'] ?? 0;
            $data['asistencia'] = $data['asistencia'] ?? 0;
            if (Schema::hasColumn('jugadores', 'atajadas')) {
                $data['atajadas'] = $data['atajadas'] ?? 0;
            } else {
                unset($data['atajadas']);
            }
            if (Schema::hasColumn('jugadores', 'es_visitante')) {
                $data['es_visitante'] = true;
            }
            $data['updated_at'] = now();

            DB::table('jugadores')->where('rut', $id)->update($data);
            $this->logModification('visitantes', 'actualizar', $id, $data['nombre'] ?? null);

            return redirect()->route('admin.visitantes.edit', $id)->with('status', 'item-updated');
        }

        if ($module === 'noticias') {
            $data = $request->validate([
                'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
                'titulo' => ['required', 'string', 'max:60'],
                'subtitulo' => ['nullable', 'string', 'max:100'],
                'cuerpo' => ['required', 'string'],
                'fecha' => ['required', 'date'],
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'foto2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
            foreach (['foto', 'foto2'] as $field) {
                if ($request->hasFile($field)) {
                    $old = DB::table('noticias')->where('id', $id)->value($field);
                    if ($old) {
                        Storage::disk('public')->delete($old);
                    }
                    $data[$field] = $this->storeUploadedWebp($request->file($field), 'noticias');
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
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'avisos');
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

            $existingToken = DB::table('partidos')->where('id', $id)->value('attendance_token');
            $attendanceData = $this->buildAttendanceWindow($data['fecha']);
            $data['attendance_token'] = is_string($existingToken) && $existingToken !== '' ? $existingToken : Str::random(48);
            $data['attendance_starts_at'] = $attendanceData['starts_at'];
            $data['attendance_ends_at'] = $attendanceData['ends_at'];

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
                'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
                $data['foto'] = $this->storeUploadedWebp($request->file('foto'), 'ayudantes');
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


    public function transferVisitante(string $rut): RedirectResponse
    {
        $this->authorizeModuleAccess('visitantes');

        $exists = DB::table('jugadores')->where('rut', $rut)->exists();
        abort_unless($exists, 404);

        DB::table('jugadores')
            ->where('rut', $rut)
            ->update([
                'es_visitante' => false,
                'updated_at' => now(),
            ]);

        $this->logModification('visitantes', 'traspasar', $rut, 'Visitante traspasado a oficial');

        return redirect()->route('admin.plantel.edit', $rut)->with('status', '✅ Jugador traspasado a plantel oficial.');
    }

    public function destroy(string $module, string $id): JsonResponse|RedirectResponse
    {
        $this->authorizeModuleAccess($module);
        $config = $this->config($module);

        if ($module === 'album') {
            if ($requestScope = request()->query('scope')) {
                if ($requestScope === 'album' && ctype_digit($id) && Schema::hasTable('foto_items')) {
                    $album = DB::table('foto_albums')->where('id', (int) $id)->first();
                    $items = DB::table('foto_items')->where('album_id', (int) $id)->get();
                    foreach ($items as $item) {
                        Storage::disk('public')->delete($item->path);
                    }
                    DB::table('foto_items')->where('album_id', (int) $id)->delete();
                    DB::table('foto_albums')->where('id', (int) $id)->delete();
                    $this->logModification('album', 'eliminar', $id, 'Álbum: '.($album->nombre ?? $id));

                    return redirect()->route('admin.album.index')->with('status', 'item-deleted');
                }
            }

            if (ctype_digit($id) && Schema::hasTable('foto_items')) {
                $item = DB::table('foto_items')->where('id', (int) $id)->first();
                $deleted = false;
                if ($item) {
                    Storage::disk('public')->delete($item->path);
                    $deleted = DB::table('foto_items')->where('id', (int) $id)->delete() > 0;
                }
                if ($deleted) {
                    $this->logModification('album', 'eliminar', (string) $id, basename((string) $item->path));
                }

                return redirect()->route('admin.album.index')
                    ->with($deleted ? 'status' : 'error', $deleted ? 'item-deleted' : 'delete-failed');
            }

            $deleted = Storage::disk('public')->delete('fotos/'.$id);
            if ($deleted) {
                $this->logModification('album', 'eliminar', $id, $id);
            }

            return redirect()->route('admin.album.index')
                ->with($deleted ? 'status' : 'error', $deleted ? 'item-deleted' : 'delete-failed');
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
        return in_array($module, ['plantel', 'visitantes'], true) ? 'rut' : 'id';
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
        if (Schema::hasTable('foto_items')) {
            return DB::table('foto_items')
                ->orderByDesc('created_at')
                ->limit(120)
                ->get(['id', 'path'])
                ->map(fn ($row) => [
                    'filename' => (string) $row->id,
                    'url' => asset('storage/'.$row->path),
                ])
                ->all();
        }

        return collect(Storage::disk('public')->files('fotos'))
            ->filter(fn (string $path) => preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $path) === 1)
            ->map(fn (string $path) => [
                'filename' => basename($path),
                'url' => asset('storage/'.$path),
            ])
            ->values()
            ->all();
    }

    private function resolveAlbumId(?int $albumId, ?string $albumName): ?int
    {
        if ($albumId) {
            return $albumId;
        }

        $name = trim((string) $albumName);
        if ($name === '' || ! Schema::hasTable('foto_albums')) {
            return null;
        }

        $existing = DB::table('foto_albums')->where('nombre', $name)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('foto_albums')->insertGetId([
            'nombre' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function persistPhotoItem(string $path, ?int $albumId): void
    {
        if (! Schema::hasTable('foto_items')) {
            return;
        }

        DB::table('foto_items')->insert([
            'album_id' => $albumId,
            'path' => $path,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function albumCatalog()
    {
        if (! Schema::hasTable('foto_albums')) {
            return collect();
        }

        return DB::table('foto_albums')->orderBy('nombre')->get();
    }

    private function albumsForAdmin(string $albumName = '', string $albumDate = '')
    {
        if (! Schema::hasTable('foto_albums')) {
            return collect();
        }

        return DB::table('foto_albums as a')
            ->leftJoin('foto_items as i', 'i.album_id', '=', 'a.id')
            ->select('a.id', 'a.nombre', 'a.created_at', DB::raw('COUNT(i.id) as total_fotos'))
            ->when($albumName !== '', fn ($q) => $q->where('a.nombre', 'like', "%{$albumName}%"))
            ->when($albumDate !== '', fn ($q) => $q->whereDate('a.created_at', '=', $albumDate))
            ->groupBy('a.id', 'a.nombre', 'a.created_at')
            ->orderByDesc('a.created_at')
            ->get();
    }

    private function albumItemsForAdmin(string $albumName = '', string $albumDate = '')
    {
        if (! Schema::hasTable('foto_items')) {
            return collect();
        }

        return DB::table('foto_items as i')
            ->leftJoin('foto_albums as a', 'a.id', '=', 'i.album_id')
            ->select('i.id', 'i.path', 'i.created_at', 'a.nombre as album_nombre')
            ->when($albumName !== '', fn ($q) => $q->where('a.nombre', 'like', "%{$albumName}%"))
            ->when($albumDate !== '', fn ($q) => $q->whereDate('a.created_at', '=', $albumDate))
            ->orderByDesc('i.created_at')
            ->limit(300)
            ->get();
    }


    private function storeUploadedWebp(UploadedFile $file, string $directory, bool $convertToWebp = true): string
    {
        if (! $convertToWebp || ! function_exists('imagewebp')) {
            return $this->storeOriginalFile($file, $directory);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $image = $this->createImageResource($file->getRealPath(), $extension);

        if ($this->shouldStoreOriginalForExif($file, $extension)) {
            return $this->storeOriginalFile($file, $directory);
        }


        if ($image) {
            $image = $this->normalizeUploadedImageOrientation($image, $file->getRealPath(), $extension);

            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = Str::slug($filename) ?: 'img';
            $filename .= '-'.Str::random(8).'.webp';
            $path = trim($directory, '/').'/'.$filename;

            $tmpFile = tempnam(sys_get_temp_dir(), 'fccs-webp-');
            if ($tmpFile === false) {
                imagedestroy($image);
                return $this->storeOriginalFile($file, $directory);
            }

            $saved = @imagewebp($image, $tmpFile, 80);
            imagedestroy($image);

            if (! $saved || ! is_file($tmpFile)) {
                @unlink($tmpFile);
                return $this->storeOriginalFile($file, $directory);
            }

            $stream = @fopen($tmpFile, 'rb');
            if ($stream === false) {
                @unlink($tmpFile);
                return $this->storeOriginalFile($file, $directory);
            }

            $written = Storage::disk('public')->put($path, $stream);
            fclose($stream);
            @unlink($tmpFile);

            if ($written && Storage::disk('public')->exists($path)) {
                return $path;
            }

            return $this->storeOriginalFile($file, $directory);
        }

        if (class_exists('Imagick')) {
            try {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = Str::slug($filename) ?: 'img';
                $filename .= '-'.Str::random(8).'.webp';
                $path = trim($directory, '/').'/'.$filename;

                $tmpFile = tempnam(sys_get_temp_dir(), 'fccs-webp-im-');
                if ($tmpFile === false) {
                    return $this->storeOriginalFile($file, $directory);
                }

                $imagick = new \Imagick();
                $imagick->readImage($file->getRealPath());
                if (method_exists($imagick, 'autoOrient')) {
                    $imagick->autoOrient();
                } elseif (method_exists($imagick, 'autoOrientImage')) {
                    $imagick->autoOrientImage();
                }

                $imagick->setImageFormat('webp');
                $imagick->setImageCompressionQuality(80);
                $imagick->writeImage($tmpFile);
                $imagick->clear();
                $imagick->destroy();

                $stream = @fopen($tmpFile, 'rb');
                if ($stream === false) {
                    @unlink($tmpFile);
                    return $this->storeOriginalFile($file, $directory);
                }

                $written = Storage::disk('public')->put($path, $stream);
                fclose($stream);
                @unlink($tmpFile);

                if ($written && Storage::disk('public')->exists($path)) {
                    return $path;
                }
            } catch (\Throwable $e) {
                logger()->warning('No se pudo convertir imagen con Imagick a webp', [
                    'filename' => $file->getClientOriginalName(),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $this->storeOriginalFile($file, $directory);
    }

    private function storeOriginalFile(UploadedFile $file, string $directory): string
    {
        $stored = $file->store($directory, 'public');

        if (! is_string($stored) || $stored === '' || ! Storage::disk('public')->exists($stored)) {
            logger()->error('No se pudo guardar archivo original en disco public', [
                'disk' => 'public',
                'root' => config('filesystems.disks.public.root'),
                'directory' => $directory,
                'file' => $file->getClientOriginalName(),
            ]);

            return '';
        }

        return $stored;
    }

    private function createImageResource(string $path, string $extension)
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'gif' => @imagecreatefromgif($path),
            'bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : null,
            'avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : null,
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


    private function shouldStoreOriginalForExif(UploadedFile $file, string $extension): bool
    {
        if (! in_array($extension, ['jpg', 'jpeg'], true) || ! function_exists('exif_read_data')) {
            return false;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? $exif['orientation'] ?? 1);

        return $orientation !== 1;
    }

    private function normalizeUploadedImageOrientation($image, string $path, string $extension)
    {
        if (! is_resource($image) && ! ($image instanceof \GdImage)) {
            return $image;
        }

        if (! in_array($extension, ['jpg', 'jpeg'], true) || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? $exif['orientation'] ?? 1);

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
            'plantel', 'visitantes' => $row->nombre ?? null,
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
            'plantel', 'visitantes' => ['rut', 'nombre', 'apellido', 'sobrenombre'],
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

    private function buildAttendanceWindow(string $fecha): array
    {
        $timezone = $this->clubTimezone();
        $matchDate = Carbon::parse($fecha, $timezone)->endOfDay();
        $twoWeeksBefore = Carbon::parse($fecha, $timezone)->subDays(14)->startOfDay();
        $now = now($timezone);
        $startAt = $now->greaterThan($twoWeeksBefore) ? $now : $twoWeeksBefore;

        return [
            'starts_at' => $startAt,
            'ends_at' => $matchDate,
        ];
    }

    private function clubTimezone(): string
    {
        return 'America/Santiago';
    }
}


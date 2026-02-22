@extends('layouts.admin')

@php
    $titles = [
        'noticias' => ['📰', 'Crear Noticia', 'Publica una noticia del club'],
        'avisos' => ['📢', 'Crear Aviso', 'Publica un aviso breve para el club'],
        'partidos' => ['📅', 'Crear Partido', 'Registra un nuevo partido'],
        'premios' => ['🏆', 'Crear Premio', 'Registra un premio para la temporada'],
        'temporadas' => ['⏳', 'Crear Temporada', 'Crea una nueva temporada'],
        'staff' => ['🤝', 'Añadir Staff', 'Registra un ayudante o miembro de staff'],
        'directiva' => ['🏛️', 'Añadir Directiva', 'Registra un integrante de directiva'],
        'album' => ['📸', 'Subir Fotos / Álbum', 'Sube una foto individual o un álbum completo'],
    ];

    [$emoji, $title, $subtitle] = $titles[$module] ?? ['🧩', 'Crear Registro', 'Formulario de creación'];
@endphp

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <style>
        .module-wrap { background: linear-gradient(135deg, #241337 0%, #2b1b45 50%, #1a0d2e 100%); border: 1px solid rgba(212, 175, 55, 0.2); }
        .module-input { background: rgba(26, 10, 46, 0.45); border: 1px solid rgba(255,255,255,0.17); color: white; }
        .module-input:focus { border-color: rgba(16,185,129,.9); box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
        .module-select { background-color: rgba(26, 10, 46, 0.45); color-scheme: dark; }
        .module-select option { background: #1f1238; color: #f3f4f6; }
    </style>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status') === 'item-created')
            <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">✅ Registro guardado correctamente.</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-red-200">
                <p class="font-semibold mb-2">Corrige los siguientes campos:</p>
                <ul class="list-disc ms-5 space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <header class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-2xl">{{ $emoji }}</div>
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">{{ $title }}</h1>
                <p class="text-slate-400">{{ $subtitle }}</p>
            </div>
        </header>

        <form method="POST" action="{{ route('admin.'.$module.'.store') }}" enctype="multipart/form-data" class="module-wrap rounded-2xl p-5 sm:p-8 space-y-6">
            @csrf

            @if(in_array($module, ['noticias', 'avisos', 'partidos', 'premios'], true))
                <div>
                    <label class="text-sm text-slate-300">Temporada *</label>
                    <select name="temporada_id" required class="module-input module-select w-full rounded-xl px-4 py-3 mt-1">
                        <option value="">Selecciona una temporada</option>
                        @foreach($temporadas as $temporada)
                            <option value="{{ $temporada->id }}" @selected(old('temporada_id') == $temporada->id)>#{{ $temporada->id }} — {{ $temporada->descripcion ?: 'Sin descripción' }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($module === 'noticias')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">📝 Título *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="titulo" maxlength="60" required value="{{ old('titulo') }}"></div>
                    <div><label class="text-sm text-slate-300">✏️ Subtítulo</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="subtitulo" maxlength="100" value="{{ old('subtitulo') }}"></div>
                </div>
                <div><label class="text-sm text-slate-300">📚 Cuerpo *</label><textarea class="module-input w-full rounded-xl px-4 py-3 mt-1" name="cuerpo" rows="6" required>{{ old('cuerpo') }}</textarea></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">📆 Fecha *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="date" name="fecha" required value="{{ old('fecha') }}"></div>
                    <div class="space-y-3"><label class="text-sm text-slate-300">📸 Foto principal / secundaria</label><input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp"><input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto2" accept="image/jpeg,image/png,image/webp"></div>
                </div>
            @endif

            @if($module === 'avisos')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">📣 Título *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="titulo" maxlength="50" required value="{{ old('titulo') }}"></div>
                    <div><label class="text-sm text-slate-300">📆 Fecha *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="date" name="fecha" required value="{{ old('fecha') }}"></div>
                </div>
                <div><label class="text-sm text-slate-300">🗒️ Descripción *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="descripcion" maxlength="120" required value="{{ old('descripcion') }}"></div>
                <label class="inline-flex items-center gap-2 text-slate-200"><input type="checkbox" name="fijado" value="1" @checked(old('fijado'))> 📌 Fijar aviso</label>
                <div><label class="text-sm text-slate-300">🖼️ Imagen (opcional)</label><input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp"></div>
            @endif

            @if($module === 'partidos')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">📆 Fecha *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="date" name="fecha" required value="{{ old('fecha') }}"></div>
                    <div><label class="text-sm text-slate-300">🕒 Hora</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="time" name="hora" value="{{ old('hora') }}"></div>
                    <div><label class="text-sm text-slate-300">🆚 Rival *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="rival" maxlength="100" required value="{{ old('rival') }}"></div>
                    <div><label class="text-sm text-slate-300">📍 Lugar *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="nombre_lugar" maxlength="100" required value="{{ old('nombre_lugar') }}"></div>
                </div>
                <div><label class="text-sm text-slate-300">🗺️ Dirección</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="direccion" maxlength="180" value="{{ old('direccion') }}"></div>
            @endif

            @if($module === 'premios')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">🥇 Nombre *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="nombre" maxlength="20" required value="{{ old('nombre') }}"></div>
                    <div><label class="text-sm text-slate-300">🧾 Descripción</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="descripcion" maxlength="50" value="{{ old('descripcion') }}"></div>
                </div>
            @endif

            @if($module === 'temporadas')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">🚩 Inicio *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="date" name="fecha_inicio" required value="{{ old('fecha_inicio') }}"></div>
                    <div><label class="text-sm text-slate-300">🏁 Término</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="date" name="fecha_termino" value="{{ old('fecha_termino') }}"></div>
                </div>
                <div><label class="text-sm text-slate-300">🗂️ Descripción</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="descripcion" maxlength="150" value="{{ old('descripcion') }}"></div>
            @endif

            @if(in_array($module, ['staff', 'directiva'], true))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm text-slate-300">👤 Nombre *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="nombre" maxlength="20" required value="{{ old('nombre') }}"></div>
                    <div><label class="text-sm text-slate-300">👤 Apellido</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="apellido" maxlength="20" value="{{ old('apellido') }}"></div>
                </div>
                <div><label class="text-sm text-slate-300">🎖️ Rol</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="descripcion_rol" maxlength="50" value="{{ old('descripcion_rol') }}"></div>

                @if($module === 'directiva')
                    <div><label class="text-sm text-slate-300">🔢 Prioridad (1-10) *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="number" name="prioridad" min="1" max="10" required value="{{ old('prioridad', 10) }}"></div>
                    <div class="rounded-xl border border-lime-400/25 bg-lime-500/10 px-4 py-3 text-xs text-lime-200">1 = mayor prioridad (arriba), 10 = menor prioridad (abajo). Se pueden repetir prioridades para tener 2 personas en el mismo nivel.</div>
                @endif

                @if($module === 'staff')
                    <div class="rounded-xl border border-emerald-400/20 bg-emerald-500/5 px-4 py-3 text-xs text-emerald-200">
                        Al crear un ayudante de staff aquí también se crea su usuario de acceso con rol <strong>ayudante</strong>.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="text-sm text-slate-300">📧 Correo de acceso *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="email" name="email" required value="{{ old('email') }}" placeholder="ayudante@correo.cl"></div>
                        <div><label class="text-sm text-slate-300">🔑 Contraseña *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="password" name="password" required minlength="8" placeholder="Mínimo 8 caracteres"></div>
                    </div>
                    <div><label class="text-sm text-slate-300">🔐 Confirmar contraseña *</label><input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="password" name="password_confirmation" required minlength="8" placeholder="Repite la contraseña"></div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div><label class="text-sm text-slate-300">🖼️ Foto</label><input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp"></div>
                    <label class="inline-flex items-center gap-2 text-slate-300"><input type="checkbox" name="activo" value="1" @checked(old('activo', '1') == '1')> ✅ Activo</label>
                </div>
            @endif

            @if($module === 'album')
                <div class="rounded-xl border border-emerald-400/30 bg-emerald-500/5 p-4 space-y-4">
                    <p class="text-emerald-200 text-sm font-semibold">📌 Modo de carga</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="module-input rounded-xl px-4 py-3 flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="upload_mode" value="single" checked>
                            <span>Subir 1 foto</span>
                        </label>
                        <label class="module-input rounded-xl px-4 py-3 flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="upload_mode" value="album">
                            <span>Subir álbum (varias fotos)</span>
                        </label>
                    </div>
                </div>

                <div id="single-upload-fields" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-slate-300">📂 Álbum existente (opcional)</label>
                            <select name="album_id" class="module-input module-select w-full rounded-xl px-4 py-3 mt-1">
                                <option value="">Sin álbum</option>
                                @foreach(($albums ?? collect()) as $album)
                                    <option value="{{ $album->id }}">{{ $album->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-slate-300">🆕 Crear álbum (opcional)</label>
                            <input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="single_album_nombre" maxlength="90" placeholder="Ej: Temporada 2026 Fecha 1">
                        </div>
                    </div>
                    <div><label class="text-sm text-slate-300">📸 Foto *</label><input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="foto" accept="image/jpeg,image/png,image/webp"></div>
                </div>

                <div id="album-upload-fields" class="space-y-4 hidden">
                    <div>
                        <label class="text-sm text-slate-300">📚 Nombre del álbum *</label>
                        <input class="module-input w-full rounded-xl px-4 py-3 mt-1" type="text" name="album_nombre" maxlength="90" placeholder="Ej: Campeonato Apertura 2026">
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">🖼️ Fotos del álbum *</label>
                        <input class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-200 file:px-4 file:py-2" type="file" name="fotos[]" multiple accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>

                <p class="text-xs text-slate-500">Se guarda en <code>storage/app/public/fotos</code> y queda disponible para <a class="text-emerald-300 underline" href="{{ route('admin.album.index') }}">gestionar álbumes/fotos</a>.</p>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const radios = document.querySelectorAll('input[name="upload_mode"]');
                        const single = document.getElementById('single-upload-fields');
                        const multiple = document.getElementById('album-upload-fields');
                        const singleFile = document.querySelector('input[name="foto"]');
                        const albumFiles = document.querySelector('input[name="fotos[]"]');

                        const syncMode = () => {
                            const mode = document.querySelector('input[name="upload_mode"]:checked')?.value || 'single';
                            const isAlbum = mode === 'album';
                            single?.classList.toggle('hidden', isAlbum);
                            multiple?.classList.toggle('hidden', !isAlbum);
                            if (singleFile) singleFile.required = !isAlbum;
                            if (albumFiles) albumFiles.required = isAlbum;
                        };

                        radios.forEach((radio) => radio.addEventListener('change', syncMode));
                        syncMode();
                    });
                </script>
            @endif

            <div class="pt-5 border-t border-white/15 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-500">* Campos obligatorios</p>
                <div class="flex w-full sm:w-auto gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="flex-1 sm:flex-none text-center px-6 py-3 rounded-xl border border-amber-400/50 text-amber-300 hover:bg-amber-400/10">✖ Cancelar</a>
                    <button type="submit" class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold shadow-lg shadow-emerald-700/25">✔ Guardar</button>
                </div>
            </div>
        </form>
    </div>
@endsection

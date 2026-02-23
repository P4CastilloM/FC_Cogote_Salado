<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galería FC Cogote Salado</title>
  @include('public.partials.seo-meta', [
    'seoTitle' => 'Galería - FC Cogote Salado',
    'seoDescription' => 'Fotos y momentos destacados de FC Cogote Salado.',
    'seoUrl' => route('fccs.fotos'),
  ])

  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  @vite([
    'resources/css/app.css',
    'resources/css/public/home.css',
    'resources/css/public/fotos.css',
    'resources/js/public/fotos.js',
  ])
</head>
<body class="h-full w-full bg-club-dark font-inter text-white overflow-auto">
  @include('public.partials.header')

  <main class="pt-24 pb-10 px-4 sm:px-6 max-w-7xl mx-auto">
    <section class="text-center mb-8 sm:mb-10">
      <h1 class="font-bebas text-5xl sm:text-6xl text-[#78c51c] tracking-wider mb-2">📸 Galería de Fotos</h1>
      <p class="text-gray-300 text-base sm:text-lg">Los mejores momentos del FC Cogote Salado</p>
      <div class="w-28 h-1 rounded-full bg-gradient-to-r from-[#78c51c] to-[#2a869c] mx-auto mt-4"></div>
    </section>

    <form method="GET" action="{{ route('fccs.fotos') }}" class="gallery-filters mb-7">
      <div>
        <label class="filter-label">Álbum</label>
        <input type="text" name="album" value="{{ $albumFilter }}" class="filter-input" placeholder="Buscar por nombre de álbum">
      </div>
      <div>
        <label class="filter-label">Fecha de creación del álbum</label>
        <input type="date" name="album_date" value="{{ $albumDateFilter }}" class="filter-input">
      </div>
      <div class="filter-actions">
        <button class="btn-filter" type="submit">Filtrar</button>
        <a href="{{ route('fccs.fotos') }}" class="btn-all">Ver todas</a>
      </div>
    </form>

    <div class="gallery-container" id="galleryContainer"></div>

    <div id="emptyState" class="hidden text-center py-16">
      <h3 class="font-bebas text-2xl text-club-gold mb-2">No hay fotos disponibles</h3>
      <p class="text-gray-400 text-sm">Sube imágenes a <code class="text-white/80">storage/app/public/fotos</code></p>
    </div>
  </main>

  <div class="modal-backdrop" id="photoModal">
    <div class="modal-content">
      <button class="modal-close" id="closeModal" type="button" aria-label="Cerrar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>

      <button class="modal-nav prev" id="prevPhoto" type="button" aria-label="Anterior">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>

      <img src="" alt="Foto ampliada" id="modalImage">

      <button class="modal-nav next" id="nextPhoto" type="button" aria-label="Siguiente">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>

    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-gray-300 text-sm" id="photoCounter">1 / 1</div>
  </div>

  <script>
    window.__PHOTOS__ = @json($photos);
  </script>
</body>
</html>

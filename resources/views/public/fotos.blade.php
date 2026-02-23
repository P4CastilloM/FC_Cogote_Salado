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
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  @vite([
    'resources/css/app.css',
    'resources/css/public/home.css',
    'resources/css/public/fotos.css',
    'resources/js/public/fotos.js',
  ])
</head>
<body class="h-full w-full bg-club-dark font-inter text-white overflow-auto">
  @include('public.partials.header')

  <main class="gallery-page pt-20 min-h-full">
    <section class="gallery-hero">
      <div class="gallery-hero__decoration gallery-hero__decoration--top"></div>
      <div class="gallery-hero__decoration gallery-hero__decoration--bottom"></div>

      <div class="gallery-shell text-center">
        <div class="gallery-badge">
          <span class="gallery-badge__dot"></span>
          <span id="badgeText">Actualizado</span>
        </div>

        <h1 class="gallery-title">
          <span>Galería de Fotos</span>
        </h1>
        <p class="gallery-subtitle">Los mejores momentos del FC Cogote Salado capturados para la eternidad.</p>

        <div class="gallery-stats">
          <div class="gallery-stat">
            <strong id="photoCount">0</strong>
            <span>Fotos</span>
          </div>
          <div class="gallery-stat">
            <strong id="albumCount">0</strong>
            <span>Álbumes</span>
          </div>
        </div>
      </div>
    </section>

    <section class="gallery-toolbar-wrap">
      <div class="gallery-shell">
        <div class="gallery-toolbar">
          <div class="gallery-toolbar__top">
            <label class="gallery-search">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <input id="searchInput" type="text" placeholder="Buscar por álbum...">
            </label>

            <div class="gallery-dates">
              <input id="dateFrom" type="date">
              <input id="dateTo" type="date">
            </div>
          </div>

          <div class="gallery-toolbar__bottom">
            <div class="gallery-chips-row">
              <span class="gallery-chips-label">Álbumes:</span>
              <div id="albumChips" class="gallery-chips"></div>
            </div>

            <button id="clearFiltersBtn" type="button" class="gallery-clear-btn">Limpiar</button>
          </div>
        </div>
      </div>
    </section>

    <section class="gallery-content">
      <div class="gallery-shell">
        <div class="gallery-results-row">
          <p>Mostrando <strong id="visibleCount">0</strong> de <strong id="totalCount">0</strong> fotos</p>

          <label class="gallery-sort">
            <span>Ordenar:</span>
            <select id="sortSelect">
              <option value="recent">Más recientes</option>
              <option value="oldest">Más antiguas</option>
              <option value="album">Por álbum</option>
            </select>
          </label>
        </div>

        <div class="gallery-grid" id="galleryContainer"></div>

        <div id="emptyState" class="gallery-empty hidden">
          <h3>No se encontraron fotos</h3>
          <p>Intenta ajustar los filtros de búsqueda.</p>
        </div>

        <div id="loadMoreWrap" class="gallery-load-more hidden">
          <button id="loadMoreBtn" type="button">Cargar más fotos</button>
          <p><span id="remainingCount">0</span> fotos más disponibles</p>
        </div>
      </div>
    </section>
  </main>

  <div class="gallery-modal" id="photoModal" aria-hidden="true">
    <div class="gallery-modal__backdrop" id="closeModalBackdrop"></div>

    <div class="gallery-modal__body">
      <button class="gallery-modal__close" id="closeModal" type="button" aria-label="Cerrar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>

      <button class="gallery-modal__nav prev" id="prevPhoto" type="button" aria-label="Anterior">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>

      <img src="" alt="Foto ampliada" id="modalImage">

      <button class="gallery-modal__nav next" id="nextPhoto" type="button" aria-label="Siguiente">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </button>

      <div class="gallery-modal__footer">
        <div>
          <h3 id="lightboxTitle">Foto</h3>
          <p><span id="lightboxAlbum">Álbum</span> • <span id="lightboxDate">Fecha</span></p>
        </div>
        <span id="photoCounter">1 / 1</span>
      </div>
    </div>
  </div>

  <script>
    window.__PHOTOS__ = @json($photos);
    window.__ALBUMS__ = @json($albums);
  </script>
</body>
</html>

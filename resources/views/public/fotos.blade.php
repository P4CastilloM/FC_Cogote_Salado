<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galería FC Cogote Salado</title>

  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">

  <!-- ✅ MISMAS FUENTES QUE EN HOME (para que el texto se vea igual) -->
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
  {{-- ✅ Reutiliza la barrita --}}
  @include('public.partials.header')

  <main class="pt-20 pb-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
    <section class="mb-6 sm:mb-8">
      <div class="text-center mb-6 sm:mb-8">
        <h2 class="font-bebas text-3xl sm:text-4xl lg:text-5xl text-club-gold tracking-wider mb-2">📸 GALERÍA DE FOTOS</h2>
        <p class="text-gray-300 text-sm sm:text-base max-w-md mx-auto">Los mejores momentos del FC Cogote Salado</p>
        <div class="w-24 h-1 bg-gradient-to-r from-club-gold to-club-gray mx-auto mt-4 rounded-full"></div>
      </div>

      <div class="gallery-container" id="galleryContainer"></div>

      <div id="emptyState" class="hidden text-center py-16">
        <h3 class="font-bebas text-xl text-club-gold mb-2">No hay fotos disponibles</h3>
        <p class="text-gray-400 text-sm">Sube imágenes a <code class="text-white/80">storage/app/public/fotos</code></p>
      </div>
    </section>
  </main>

  {{-- Modal --}}
  <div class="modal-backdrop" id="photoModal">
    <div class="modal-content">
      <button class="modal-close" id="closeModal">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>

      <button class="modal-nav prev" id="prevPhoto">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>

      <img src="" alt="Foto ampliada" id="modalImage">

      <button class="modal-nav next" id="nextPhoto">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>

    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-gray-300 text-sm" id="photoCounter">
      1 / 1
    </div>
  </div>

  <footer class="bg-club-dark border-t border-club-gold/20 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
      <p class="text-gray-400 text-sm">© {{ date('Y') }} <span class="text-club-gold font-bebas tracking-wider">FC COGOTE SALADO</span></p>
    </div>
  </footer>

  {{-- ✅ Pasamos las fotos desde PHP a JS --}}
  <script>
    window.__PHOTOS__ = @json($photos);
  </script>
</body>
</html>

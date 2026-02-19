<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $noticia->titulo }} - FC Cogote Salado</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css'])
</head>
<body class="h-full bg-club-dark font-inter text-white overflow-auto">
  @include('public.partials.header')

  <main class="pt-24 pb-14 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-[#1a0a2e] via-[#2d1b4e] to-[#1a0a2e] min-h-full">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
      <article class="lg:col-span-2 rounded-2xl border border-lime-400/20 bg-[#1a0a2e]/80 overflow-hidden">
        <div class="aspect-video bg-gradient-to-br from-[#4a2c7a] to-[#2d1b4e]">
          @if($noticia->foto)
            <img src="{{ asset('storage/'.$noticia->foto) }}" alt="{{ $noticia->titulo }}" class="w-full h-full object-cover">
          @else
            <div class="w-full h-full flex items-center justify-center text-7xl">📰</div>
          @endif
        </div>
        <div class="p-6 sm:p-8">
          <a href="{{ route('fccs.noticias.index') }}" class="text-lime-400 text-sm">← Volver a noticias</a>
          <h1 class="font-bebas text-4xl sm:text-5xl mt-3 leading-none">{{ $noticia->titulo }}</h1>
          @if($noticia->subtitulo)
            <p class="text-gray-300 mt-3 text-lg">{{ $noticia->subtitulo }}</p>
          @endif
          <p class="text-gray-400 text-sm mt-3">{{ \Carbon\Carbon::parse($noticia->fecha)->translatedFormat('d \d\e F \d\e Y') }}</p>
          <div class="prose prose-invert max-w-none mt-6 whitespace-pre-line text-gray-200">{{ $noticia->cuerpo }}</div>
        </div>
      </article>

      <aside class="space-y-4">
        <div class="rounded-2xl border border-lime-400/20 bg-[#1a0a2e]/80 p-5">
          <h3 class="font-bebas text-2xl mb-3">Más noticias</h3>
          <div class="space-y-3">
            @foreach($related as $item)
              <a href="{{ route('fccs.noticias.show', $item->id) }}" class="block p-3 rounded-xl border border-white/10 hover:border-lime-400/40 hover:bg-lime-400/5 transition">
                <p class="font-semibold text-sm">{{ \Illuminate\Support\Str::limit($item->titulo, 60) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($item->fecha)->translatedFormat('d M Y') }}</p>
              </a>
            @endforeach
          </div>
        </div>
      </aside>
    </div>
  </main>
</body>
</html>

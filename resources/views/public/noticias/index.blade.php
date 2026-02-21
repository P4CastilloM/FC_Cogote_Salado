<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias - FC Cogote Salado</title>
  @include('public.partials.seo-meta', [
    'seoTitle' => 'Noticias - FC Cogote Salado',
    'seoDescription' => 'Últimas noticias y novedades de FC Cogote Salado.',
    'seoUrl' => route('fccs.noticias.index'),
  ])
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css', 'resources/js/public/home.js'])
</head>
<body class="h-full bg-club-dark font-inter text-white overflow-auto">
  @include('public.partials.header')

  <main class="w-full min-h-full bg-gradient-to-br from-[#1a0a2e] via-[#2d1b4e] to-[#1a0a2e] pt-24 pb-14">
    <section class="px-4 sm:px-6 lg:px-8 pb-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="font-bebas text-5xl sm:text-6xl tracking-wide">NOTICIAS</h1>
        <p class="text-gray-300 text-lg">Lo último del club, dentro y fuera de la cancha</p>
      </div>
    </section>

    <section class="px-4 sm:px-6 lg:px-8 pb-8">
      <div class="max-w-7xl mx-auto rounded-2xl border border-lime-400/20 bg-[#2d1b4e]/60 p-4 sm:p-6">
        <form class="flex flex-col lg:flex-row gap-4" method="GET" action="{{ route('fccs.noticias.index') }}">
          <input type="text" name="q" value="{{ $search }}" placeholder="Buscar noticia..." class="flex-1 px-4 py-3 bg-[#1a0a2e]/70 border border-white/10 rounded-xl">
          <select
            name="order"
            class="px-4 py-3 pr-10 bg-[#1a0a2e]/70 border border-white/10 rounded-xl appearance-none bg-no-repeat bg-[right_0.8rem_center] bg-[length:0.95rem] hover:border-lime-400/40"
            style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23facc15%27 stroke-width=%272.4%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e');"
          >
            <option value="recent" @selected($order === 'desc')>Más recientes</option>
            <option value="oldest" @selected($order === 'asc')>Más antiguas</option>
          </select>
          <button class="px-6 py-3 rounded-xl bg-lime-500 hover:bg-lime-400 text-black font-semibold">Filtrar</button>
        </form>
      </div>
    </section>

    <section class="px-4 sm:px-6 lg:px-8 pb-8">
      <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($noticias as $noticia)
          <article class="rounded-2xl overflow-hidden border border-lime-400/20 bg-[#1a0a2e]/80 hover:border-lime-400/50 transition-all">
            <a href="{{ route('fccs.noticias.show', $noticia->id) }}" class="block">
              <div class="aspect-video bg-gradient-to-br from-[#4a2c7a] to-[#2d1b4e] relative">
                @if($noticia->foto)
                  <img src="{{ asset('storage/'.$noticia->foto) }}" alt="{{ $noticia->titulo }}" class="w-full h-full object-cover" loading="lazy">
                @else
                  <div class="absolute inset-0 flex items-center justify-center text-5xl">📰</div>
                @endif
              </div>
              <div class="p-5">
                <p class="text-xs text-lime-400">{{ \Carbon\Carbon::parse($noticia->fecha)->translatedFormat('d M Y') }}</p>
                <h2 class="font-bebas text-2xl mt-2 leading-none">{{ \Illuminate\Support\Str::limit($noticia->titulo, 65) }}</h2>
                <p class="text-gray-400 text-sm mt-2 line-clamp-2">{{ \Illuminate\Support\Str::limit($noticia->subtitulo ?: strip_tags($noticia->cuerpo), 110) }}</p>
              </div>
            </a>
          </article>
        @empty
          <div class="col-span-full rounded-2xl border border-white/10 bg-[#1a0a2e]/60 p-8 text-center text-gray-300">No hay noticias para mostrar.</div>
        @endforelse
      </div>
    </section>

    <section class="px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">{{ $noticias->links() }}</div>
    </section>
  </main>
</body>
</html>

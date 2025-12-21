<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FC Cogote Salado</title>
  <link rel="icon" href="{{ asset('storage/fotos/logo_fccs_s_f.png') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    button, a { -webkit-tap-highlight-color: transparent; }
  </style>
</head>

<body class="bg-gray-900 text-white">

<!-- =========================
     NAVBAR
========================= -->
<nav class="fixed top-0 w-full z-50 bg-black/70 backdrop-blur border-b border-white/10">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

    <div class="flex items-center gap-3">
      <img src="{{ asset('storage/fotos/logo_fccs.png') }}" class="h-10" alt="">
      <span class="font-bold text-lg">FC Cogote Salado</span>
    </div>

    <ul class="hidden md:flex gap-6 font-semibold">
      <li><a href="#">Inicio</a></li>
      <li><a href="#">Temporadas</a></li>
      <li><a href="#">Plantel</a></li>
      <li><a href="#">Noticias</a></li>
      <li><a href="#">Fotos</a></li>
    </ul>

    <button id="navToggle"
      class="md:hidden w-11 h-11 rounded-xl bg-white/10 border border-white/10">
      ☰
    </button>
  </div>

  <!-- MOBILE MENU -->
  <div id="navMobile" class="fixed inset-0 bg-black/70 hidden md:hidden">
    <aside class="absolute right-0 top-0 h-full w-[85%] max-w-sm bg-gray-950/95 backdrop-blur border-l border-white/10">
      <div class="p-6 flex justify-between items-center border-b border-white/10">
        <span class="font-bold text-lg">Menú</span>
        <button id="navClose">✕</button>
      </div>

      <div class="p-6 space-y-3">
        @foreach (['Inicio','Temporadas','Plantel','Noticias','Fotos'] as $item)
          <a href="#"
             class="block px-4 py-3 rounded-xl bg-white/10 backdrop-blur
                    border border-white/20 font-semibold">
            {{ $item }}
          </a>
        @endforeach
      </div>
    </aside>
  </div>
</nav>

<!-- =========================
     HERO (IMAGEN CENTRADA)
========================= -->
<section class="min-h-screen relative pt-[72px] md:pt-0">


  <div class="absolute inset-0
              bg-center
              bg-no-repeat
              bg-contain
              md:bg-cover"
       style="background-image:url('{{ asset('storage/fotos/equipo.jpeg') }}')"></div>

  <div class="absolute inset-0 bg-black/60"></div>

  <div class="relative z-10 min-h-screen flex items-center">
    <div class="max-w-5xl mx-auto px-6 py-10">

      <h1 class="text-5xl md:text-6xl font-extrabold mb-6">
        SUPERAMOS LAS EXPECTATIVAS
      </h1>

      <p class="text-xl mb-8 max-w-xl">
Pasión, fútbol y familia. FC Cogote Salado sigue creciendo junto a su gente. El 2025 marcó el nacimiento oficial del FC Cogote Salado, un proyecto que pasó de las reuniones familiares directamente a las canchas de fútbol 7. Durante estos primeros meses, el equipo logró consolidar una base sólida de amigos y parientes, logrando un avance notable en el nivel de juego y organización. Lo que comenzó como una idea casual se transformó rápidamente en un grupo competitivo que ya sabe lo que es sudar la camiseta y crecer partido a partido.
      </p>

      <a href="#avisos"
         class="inline-block bg-purple-600 px-8 py-4 rounded-xl font-bold">
        Ver avisos
      </a>
    </div>
  </div>
</section>

<!-- =========================
     AVISOS (IMAGEN + TEXTO ENCIMA)
========================= -->
<section id="avisos" class="bg-gray-800 py-16">
  <div class="max-w-7xl mx-auto px-6">

    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold">Avisos</h2>
      <div class="flex gap-2">
        <button id="prev" class="w-10 h-10 bg-white/10 rounded-full">‹</button>
        <button id="next" class="w-10 h-10 bg-white/10 rounded-full">›</button>
      </div>
    </div>

    <div class="overflow-hidden">
      <div id="track" class="flex transition-transform duration-300">
        @foreach($avisos as $aviso)
            <div class="w-full md:w-1/2 px-2 shrink-0">
                <article class="relative aspect-[4/3] md:aspect-[3/2] lg:aspect-[5/4] w-full rounded-2xl overflow-hidden border border-white/10 shadow-xl shadow-black/30">

                <div class="absolute inset-0 bg-purple-900"></div>

                @if ($aviso->foto)
                    <img
                    src="{{ asset($aviso->foto) }}"
                    alt="{{ $aviso->titulo }}"
                    class="absolute inset-0 w-full h-full object-contain"
                    loading="lazy"
                    >
                @else
                    <div class="absolute inset-0 flex items-center justify-center text-white/70 text-sm">
                    Sin imagen
                    </div>
                @endif

                <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-black/75 via-black/30 to-transparent"></div>

                <div class="absolute bottom-0 p-5 z-10">
                    <h3 class="text-xl font-bold mb-1">{{ $aviso->titulo }}</h3>
                    <p class="text-sm text-gray-200">{{ $aviso->descripcion }}</p>
                    <span class="text-xs text-gray-300 block mt-2">
                    {{ \Carbon\Carbon::parse($aviso->fecha)->format('d/m/Y') }}
                    </span>
                </div>

                </article>
            </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<footer class="bg-black py-8 text-center text-gray-400 text-sm">
  © {{ date('Y') }} FC Cogote Salado
</footer>

<!-- =========================
     JS
========================= -->
<script>
(() => {
  const navToggle = document.getElementById('navToggle');
  const navMobile = document.getElementById('navMobile');
  const navClose  = document.getElementById('navClose');

  navToggle.onclick = () => {
    navMobile.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  };

  navClose.onclick = () => {
    navMobile.classList.add('hidden');
    document.body.style.overflow = '';
  };

  navMobile.onclick = (e) => {
    if (e.target === navMobile) navClose.onclick();
  };
})();

(() => {
  const track = document.getElementById('track');
  const prev  = document.getElementById('prev');
  const next  = document.getElementById('next');
  let index = 0;

  const perView = () => window.innerWidth >= 768 ? 2 : 1;

  const update = () => {
    const w = track.children[0].offsetWidth;
    track.style.transform = `translateX(-${index * w}px)`;
  };

  next.onclick = () => {
    if (index < track.children.length - perView()) index++;
    update();
  };

  prev.onclick = () => {
    if (index > 0) index--;
    update();
  };

  window.addEventListener('resize', update);
  update();
})();
</script>

</body>
</html>

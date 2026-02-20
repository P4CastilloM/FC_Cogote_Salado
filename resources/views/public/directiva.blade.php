<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FC Cogote Salado - Directiva</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css', 'resources/js/public/home.js'])
  <style>
    .field-pattern {background-image:linear-gradient(rgba(132,204,22,.04) 1px, transparent 1px),linear-gradient(90deg, rgba(132,204,22,.04) 1px, transparent 1px);background-size:42px 42px;}
    .timeline-line {background: repeating-linear-gradient(to bottom, rgba(132,204,22,.65) 0 8px, transparent 8px 16px);}    
    .directive-card {opacity:0; transform:translateY(34px); transition:all .6s ease;}
    .directive-card.visible {opacity:1; transform:translateY(0);} 
    .directive-card.left {transform:translate(-24px,34px);} 
    .directive-card.right {transform:translate(24px,34px);} 
    .directive-card.left.visible, .directive-card.right.visible {transform:translate(0,0);} 
    .chalk-arrow {position:absolute; width:90px; height:2px; background:rgba(132,204,22,.6); top:50%;}
    .chalk-arrow::after {content:''; position:absolute; right:-2px; top:-3px; border-left:10px solid rgba(132,204,22,.7); border-top:4px solid transparent; border-bottom:4px solid transparent;}
    .chalk-arrow.left {right:-92px;} .chalk-arrow.right {left:-92px; transform:scaleX(-1);} 
    @media (max-width: 1023px){.chalk-arrow{display:none}}
  </style>
</head>
<body class="h-full bg-[#241337] text-white overflow-auto">
  @include('public.partials.header')

  <main id="app-wrapper" class="pt-24 relative overflow-hidden min-h-full">
    <div id="parallax-bg" class="absolute inset-0 pointer-events-none field-pattern"></div>

    <section class="relative px-4 py-10 md:py-14">
      <div class="max-w-5xl mx-auto text-center">
        <span class="inline-flex px-4 py-2 rounded-full border border-lime-400/30 bg-lime-500/15 text-lime-300 text-xs font-semibold uppercase tracking-wider">Temporada {{ date('Y') }}</span>
        <h1 class="font-bebas text-5xl md:text-7xl tracking-wider mt-4">DIRECTIVA</h1>
        <p class="text-gray-300 max-w-2xl mx-auto">La estrategia detrás del equipo, con un recorrido táctico en línea recta que acompaña el scroll.</p>
      </div>
    </section>

    <section class="relative px-4 pb-20">
      <div class="max-w-6xl mx-auto relative">
        <div class="timeline-line hidden lg:block absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-[3px]"></div>

        <div class="space-y-10 lg:space-y-16">
          @forelse($directiva as $index => $persona)
            @php $left = $index % 2 === 0; @endphp
            <div class="relative grid lg:grid-cols-2 gap-6 items-center">
              <div class="{{ $left ? 'lg:pr-16' : 'lg:pl-16 lg:col-start-2' }}">
                <article class="directive-card {{ $left ? 'left' : 'right' }} rounded-2xl border border-lime-400/20 bg-white/[0.06] backdrop-blur p-6 hover:border-lime-400/45 transition">
                  <div class="flex items-center gap-4 mb-3">
                    <div class="w-16 h-16 rounded-full overflow-hidden border border-lime-400/30 bg-[#2b1b45] flex items-center justify-center">
                      @if($persona->foto_url)
                        <img src="{{ $persona->foto_url }}" alt="{{ $persona->full_name }}" class="w-full h-full object-cover">
                      @else
                        <span class="text-2xl">👤</span>
                      @endif
                    </div>
                    <div>
                      <span class="inline-flex px-3 py-1 rounded-full bg-lime-500/20 text-lime-300 text-xs font-semibold uppercase">{{ $persona->rol }}</span>
                      <h2 class="text-2xl font-bold mt-2 leading-none">{{ $persona->full_name ?: 'Integrante sin nombre' }}</h2>
                    </div>
                  </div>
                  <p class="text-gray-300 mt-2 text-sm">Parte del núcleo estratégico del club en gestión deportiva y coordinación institucional.</p>
                </article>
              </div>

              <div class="hidden lg:flex items-center justify-center absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 z-10">
                <div class="w-12 h-12 rounded-full bg-lime-400 text-[#241337] font-bold flex items-center justify-center shadow-lg shadow-lime-400/30">{{ $persona->badge }}</div>
              </div>

              <div class="{{ $left ? 'lg:col-start-2' : 'lg:col-start-1 lg:row-start-1' }} hidden lg:block relative h-0">
                <div class="chalk-arrow {{ $left ? 'left' : 'right' }}"></div>
              </div>
            </div>
          @empty
            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 text-center text-gray-300">No hay integrantes activos en directiva todavía.</div>
          @endforelse
        </div>
      </div>
    </section>
  </main>

  <script>
    const wrapper = document.getElementById('app-wrapper');
    const parallaxBg = document.getElementById('parallax-bg');
    const cards = document.querySelectorAll('.directive-card');

    const revealObserver = new IntersectionObserver((entries)=>{
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.2 });

    cards.forEach(card => revealObserver.observe(card));

    window.addEventListener('scroll', () => {
      const y = window.scrollY || document.documentElement.scrollTop;
      if (parallaxBg) parallaxBg.style.transform = `translateY(${y * 0.18}px)`;
    }, { passive: true });
  </script>
</body>
</html>

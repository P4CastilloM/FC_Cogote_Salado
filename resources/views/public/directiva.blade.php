<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Directiva - FC Cogote Salado</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/logo/logo_fccs_s_f.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/css/public/home.css', 'resources/js/public/home.js'])
  <style>
    @keyframes draw { from { stroke-dashoffset: 1000; } to { stroke-dashoffset: 0; } }
    .stroke-animate { stroke-dasharray: 1000; stroke-dashoffset: 1000; }
    .stroke-animate.active { animation: draw 1.2s ease-out forwards; }
    .parallax-bg { will-change: transform; }
    .member-card { opacity: 0; transform: translateY(24px); transition: all .55s ease; }
    .member-card.visible { opacity: 1; transform: translateY(0); }
    .glass-card { background: rgba(0, 0, 0, .42); backdrop-filter: blur(11px); border: 1px solid rgba(132,204,22,.25); }
    .priority-block { border: 1px solid rgba(132,204,22,.2); background: rgba(10, 30, 16, .35); }
    .field-line { stroke: rgba(134, 239, 172, 0.25); stroke-width: 2; fill: none; }
  </style>
</head>
<body class="h-full overflow-auto bg-gradient-to-b from-[#0f3d1f] via-[#14532d] to-[#166534] text-white" style="font-family: 'Inter', sans-serif;">
  @include('public.partials.header')

  <main class="pt-24 relative">
    <section class="relative py-14 md:py-20 px-4 overflow-hidden">
      <div class="max-w-4xl mx-auto text-center relative z-10">
        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase bg-lime-500/20 text-lime-400 border border-lime-500/30 mb-6">Temporada {{ date('Y') }}</span>
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold mb-4 tracking-tight" style="font-family: 'Bebas Neue', sans-serif;">Directiva</h1>
        <p class="text-lg md:text-xl text-green-200/80 max-w-2xl mx-auto">La estrategia detrás del club. Ordenada por prioridad, desde el nivel 1 (más alto) al 10.</p>
      </div>
    </section>

    <section class="relative min-h-screen py-8 md:py-16 px-4">
      <div id="field-bg" class="parallax-bg absolute inset-0 overflow-hidden pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 1200 1800" preserveAspectRatio="xMidYMid slice">
          <rect width="100%" height="100%" fill="rgba(15,61,31,0.55)" />
          <rect x="120" y="120" width="960" height="1560" class="field-line" rx="10" />
          <line x1="120" y1="900" x2="1080" y2="900" class="field-line" />
          <circle cx="600" cy="900" r="120" class="field-line" />
          <path id="t1" class="stroke-animate" d="M 600 190 C 740 250, 780 330, 650 430" stroke="white" stroke-width="3" fill="none" stroke-dasharray="10 6" />
          <path id="t2" class="stroke-animate" d="M 650 430 C 420 520, 380 620, 560 740" stroke="white" stroke-width="3" fill="none" />
          <path id="t3" class="stroke-animate" d="M 560 740 C 780 850, 820 960, 620 1080" stroke="#fbbf24" stroke-width="3" fill="none" />
          <path id="t4" class="stroke-animate" d="M 620 1080 C 420 1220, 430 1370, 610 1510" stroke="white" stroke-width="3" fill="none" stroke-dasharray="10 6" />
        </svg>
      </div>

      <div class="max-w-6xl mx-auto relative z-10 space-y-6 md:space-y-8">
        @if(! $hasPriority)
          <div class="rounded-2xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 text-amber-200 text-sm">
            ⚠️ Falta columna <strong>prioridad</strong> en la tabla <strong>ayudantes</strong>. Se está usando prioridad 10 por defecto para todos.
          </div>
        @endif

        @foreach($prioridades as $bloque)
          <article class="priority-block rounded-2xl p-4 md:p-6" data-priority-block>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl md:text-2xl font-bold" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: .05em;">Prioridad {{ $bloque->nivel }}</h2>
              <span class="px-3 py-1 rounded-full text-xs font-semibold border border-lime-400/35 text-lime-300 bg-lime-500/10">{{ $bloque->miembros->count() }} integrante(s)</span>
            </div>

            @if($bloque->topPair->isNotEmpty())
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($bloque->topPair as $persona)
                  <div class="member-card glass-card rounded-2xl p-4 md:p-5">
                    <div class="flex items-center gap-3">
                      <div class="w-16 h-16 rounded-full overflow-hidden border border-lime-400/35 bg-[#0d1f14] flex items-center justify-center shrink-0">
                        @if($persona->foto_url)
                          <img src="{{ $persona->foto_url }}" alt="{{ $persona->full_name }}" class="w-full h-full object-cover">
                        @else
                          <span class="text-xl">👤</span>
                        @endif
                      </div>
                      <div>
                        <p class="text-xs uppercase tracking-wide text-lime-300/90">{{ $persona->rol }}</p>
                        <h3 class="text-lg md:text-xl font-semibold leading-tight">{{ $persona->full_name ?: 'Integrante sin nombre' }}</h3>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              @if($bloque->extraCount > 0)
                <p class="mt-3 text-xs text-amber-300/90">+ {{ $bloque->extraCount }} integrante(s) adicional(es) en esta prioridad (se muestran hasta 2 por bloque).</p>
              @endif
            @else
              <div class="rounded-xl border border-white/10 bg-black/20 px-4 py-4 text-sm text-green-100/70">Sin integrantes asignados a esta prioridad.</div>
            @endif
          </article>
        @endforeach
      </div>
    </section>
  </main>

  <script>
    const fieldBg = document.getElementById('field-bg');
    const cards = document.querySelectorAll('.member-card');
    const tacticalPaths = document.querySelectorAll('.stroke-animate');

    const reveal = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.2 });

    cards.forEach((card) => reveal.observe(card));

    let tick = false;
    window.addEventListener('scroll', () => {
      if (tick) return;
      tick = true;
      requestAnimationFrame(() => {
        const y = window.scrollY || document.documentElement.scrollTop;
        const sway = Math.sin(y / 180) * 22;

        if (fieldBg) {
          fieldBg.style.transform = `translate3d(${sway}px, ${y * 0.18}px, 0)`;
        }

        tacticalPaths.forEach((path, i) => {
          const start = i * 250;
          if (y > start) path.classList.add('active');
        });

        tick = false;
      });
    }, { passive: true });
  </script>
</body>
</html>

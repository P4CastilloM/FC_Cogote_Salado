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
    @keyframes draw { from { stroke-dashoffset: 900; } to { stroke-dashoffset: 0; } }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(26px); } to { opacity: 1; transform: translateY(0); } }
    .field-overlay { background-image: linear-gradient(rgba(132,204,22,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(132,204,22,.05) 1px, transparent 1px); background-size: 46px 46px; }
    .parallax-bg { will-change: transform; }
    .route-path { stroke-dasharray: 900; stroke-dashoffset: 900; }
    .route-path.active { animation: draw 1.25s ease-out forwards; }
    .member-card { opacity: 0; transform: translateY(26px); }
    .member-card.visible { animation: fadeUp .65s ease-out forwards; }
    .glass-card { background: rgba(0, 0, 0, 0.45); backdrop-filter: blur(12px); border: 1px solid rgba(132, 204, 22, .24); }
    .connector { position: absolute; top: 50%; width: clamp(24px, 5vw, 70px); height: 2px; background: rgba(132,204,22,.7); }
    .connector::after { content: ''; position: absolute; right: -1px; top: -4px; border-left: 10px solid rgba(132,204,22,.8); border-top: 5px solid transparent; border-bottom: 5px solid transparent; }
    .connector.left { right: calc(-1 * clamp(24px, 5vw, 70px)); }
    .connector.right { left: calc(-1 * clamp(24px, 5vw, 70px)); transform: scaleX(-1); }
    @media (max-width: 1023px){ .connector, .desktop-timeline { display:none !important; } }
  </style>
</head>
<body class="h-full overflow-auto bg-gradient-to-b from-[#0f3d1f] via-[#14532d] to-[#166534] text-white" style="font-family: 'Inter', sans-serif;">
  @include('public.partials.header')

  <main class="pt-24 relative min-h-screen overflow-hidden">
    <div id="field-bg" class="parallax-bg absolute inset-0 pointer-events-none">
      <div class="absolute inset-0 field-overlay"></div>
      <svg class="absolute inset-0 w-full h-full opacity-75" viewBox="0 0 1200 2200" preserveAspectRatio="xMidYMid slice">
        <rect x="120" y="90" width="960" height="2020" fill="none" stroke="rgba(134,239,172,.17)" stroke-width="2" rx="8" />
        <line x1="120" y1="1100" x2="1080" y2="1100" stroke="rgba(134,239,172,.15)" stroke-width="2" />
        <circle cx="600" cy="1100" r="120" fill="none" stroke="rgba(134,239,172,.15)" stroke-width="2" />
        <path id="route-a" class="route-path" d="M 600 180 C 780 280, 770 420, 590 520" stroke="white" stroke-width="3" fill="none" stroke-dasharray="8 6" />
        <path id="route-b" class="route-path" d="M 590 520 C 360 700, 430 860, 640 980" stroke="#fbbf24" stroke-width="3" fill="none" />
        <path id="route-c" class="route-path" d="M 640 980 C 860 1120, 810 1280, 610 1430" stroke="white" stroke-width="3" fill="none" stroke-dasharray="8 6" />
        <path id="route-d" class="route-path" d="M 610 1430 C 380 1590, 410 1780, 600 1940" stroke="#fbbf24" stroke-width="3" fill="none" />
      </svg>
    </div>

    <section class="relative px-4 py-14 md:py-20 text-center">
      <div class="max-w-4xl mx-auto">
        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase bg-lime-500/20 text-lime-300 border border-lime-500/35 mb-6">Temporada {{ date('Y') }}</span>
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold mb-3" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: .04em;">Directiva</h1>
        <p class="text-lg md:text-xl text-green-100/80 max-w-2xl mx-auto">La estrategia detrás del club, con recorrido táctico vivo al hacer scroll.</p>
      </div>
    </section>

    <section class="relative px-4 pb-20 md:pb-28">
      <div class="max-w-6xl mx-auto relative">
        @if($lineups->isNotEmpty())
          <div class="desktop-timeline absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-[3px] bg-gradient-to-b from-lime-300/70 via-lime-500/40 to-amber-300/70"></div>

          <div class="space-y-8 md:space-y-12">
            @foreach($lineups as $lineup)
              <article class="relative grid lg:grid-cols-[1fr_90px_1fr] gap-4 lg:gap-8 items-center" data-lineup>
                <div class="hidden lg:block relative">
                  @if($lineup->left)
                    <div class="member-card glass-card rounded-2xl p-5 md:p-6 relative">
                      <div class="connector left"></div>
                      <div class="flex items-center gap-3">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border border-lime-400/35 bg-[#0d1f14] flex items-center justify-center shrink-0">
                          @if($lineup->left->foto_url)
                            <img src="{{ $lineup->left->foto_url }}" alt="{{ $lineup->left->full_name }}" class="w-full h-full object-cover">
                          @else
                            <span class="text-xl">👤</span>
                          @endif
                        </div>
                        <div>
                          <p class="text-xs uppercase tracking-wide text-lime-300/85">{{ $lineup->left->rol }}</p>
                          <h2 class="text-xl md:text-2xl font-bold leading-tight" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: .04em;">{{ $lineup->left->full_name ?: 'Integrante' }}</h2>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>

                <div class="hidden lg:flex items-center justify-center relative z-10">
                  <div class="w-12 h-12 rounded-full border-2 border-lime-300/80 bg-[#102b18] shadow-lg shadow-lime-400/20"></div>
                </div>

                <div class="hidden lg:block relative">
                  @if($lineup->right)
                    <div class="member-card glass-card rounded-2xl p-5 md:p-6 relative">
                      <div class="connector right"></div>
                      <div class="flex items-center gap-3">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border border-lime-400/35 bg-[#0d1f14] flex items-center justify-center shrink-0">
                          @if($lineup->right->foto_url)
                            <img src="{{ $lineup->right->foto_url }}" alt="{{ $lineup->right->full_name }}" class="w-full h-full object-cover">
                          @else
                            <span class="text-xl">👤</span>
                          @endif
                        </div>
                        <div>
                          <p class="text-xs uppercase tracking-wide text-lime-300/85">{{ $lineup->right->rol }}</p>
                          <h2 class="text-xl md:text-2xl font-bold leading-tight" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: .04em;">{{ $lineup->right->full_name ?: 'Integrante' }}</h2>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>

                <div class="lg:hidden space-y-3">
                  @if($lineup->left)
                    <div class="member-card glass-card rounded-2xl p-4">
                      <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-full overflow-hidden border border-lime-400/35 bg-[#0d1f14] flex items-center justify-center shrink-0">
                          @if($lineup->left->foto_url)
                            <img src="{{ $lineup->left->foto_url }}" alt="{{ $lineup->left->full_name }}" class="w-full h-full object-cover">
                          @else
                            <span>👤</span>
                          @endif
                        </div>
                        <div>
                          <p class="text-[11px] uppercase tracking-wide text-lime-300/85">{{ $lineup->left->rol }}</p>
                          <h3 class="text-lg font-semibold leading-tight">{{ $lineup->left->full_name ?: 'Integrante' }}</h3>
                        </div>
                      </div>
                    </div>
                  @endif
                  @if($lineup->right)
                    <div class="member-card glass-card rounded-2xl p-4">
                      <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-full overflow-hidden border border-lime-400/35 bg-[#0d1f14] flex items-center justify-center shrink-0">
                          @if($lineup->right->foto_url)
                            <img src="{{ $lineup->right->foto_url }}" alt="{{ $lineup->right->full_name }}" class="w-full h-full object-cover">
                          @else
                            <span>👤</span>
                          @endif
                        </div>
                        <div>
                          <p class="text-[11px] uppercase tracking-wide text-lime-300/85">{{ $lineup->right->rol }}</p>
                          <h3 class="text-lg font-semibold leading-tight">{{ $lineup->right->full_name ?: 'Integrante' }}</h3>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>

                @if($lineup->extraCount > 0)
                  <p class="lg:col-span-3 text-xs text-amber-200/85">+ {{ $lineup->extraCount }} integrante(s) más con el mismo nivel interno.</p>
                @endif
              </article>
            @endforeach
          </div>
        @else
          <div class="rounded-2xl border border-white/10 bg-black/20 p-8 text-center text-gray-200">No hay integrantes activos en directiva todavía.</div>
        @endif
      </div>
    </section>

    <section class="relative px-4 pb-16">
      <div class="max-w-2xl mx-auto glass-card rounded-3xl p-8 md:p-10 text-center">
        <h3 class="text-2xl md:text-3xl font-bold mb-3" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: .04em;">Equipo fuera de la cancha, estrategia dentro del club</h3>
        <p class="text-green-100/75">{{ $totalMembers }} integrante(s) activos sosteniendo el proyecto de FC Cogote Salado.</p>
      </div>
    </section>
  </main>

  <script>
    const fieldBg = document.getElementById('field-bg');
    const cards = document.querySelectorAll('.member-card');
    const tacticalPaths = document.querySelectorAll('.route-path');

    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.18 });

    cards.forEach((card) => revealObserver.observe(card));

    let ticking = false;
    window.addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;

      requestAnimationFrame(() => {
        const y = window.scrollY || document.documentElement.scrollTop;
        const sway = Math.sin(y / 190) * 24;

        if (fieldBg) {
          fieldBg.style.transform = `translate3d(${sway}px, ${y * 0.18}px, 0)`;
        }

        tacticalPaths.forEach((path, index) => {
          if (y > index * 260) path.classList.add('active');
        });

        ticking = false;
      });
    }, { passive: true });
  </script>
</body>
</html>

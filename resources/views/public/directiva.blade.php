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
    @keyframes strokeDraw { from { stroke-dashoffset: 1000; } to { stroke-dashoffset: 0; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseGlow { 0%,100% { filter: drop-shadow(0 0 8px rgba(132,204,22,.4)); } 50% { filter: drop-shadow(0 0 20px rgba(132,204,22,.85)); } }
    @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    .stroke-animate { stroke-dasharray: 1000; stroke-dashoffset: 1000; }
    .stroke-animate.active { animation: strokeDraw 1.8s ease-out forwards; }
    .fade-in-up { opacity: 0; transform: translateY(40px); }
    .fade-in-up.active { animation: fadeInUp .75s ease-out forwards; }
    .parallax-bg { will-change: transform; }
    .glass-card { background: rgba(0,0,0,.45); backdrop-filter: blur(12px); border: 1px solid rgba(132,204,22,.2); }
    .field-line { stroke: rgba(134,239,172,.25); stroke-width: 2; fill: none; }
    .tactical-line { filter: drop-shadow(0 0 4px rgba(255,255,255,.3)); }
    .floating-badge { animation: float 3s ease-in-out infinite; }

    .member-card {
      transition: all .4s cubic-bezier(0.4, 0, 0.2, 1);
      min-height: 18.5rem;
    }
    .member-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 0 30px rgba(132,204,22,.28), 0 0 60px rgba(132,204,22,.1);
    }
    .member-card:hover .member-photo {
      box-shadow: 0 0 20px rgba(251,191,36,.5);
      animation: pulseGlow 1.8s ease-in-out infinite;
    }

    @media (max-width: 767px) {
      .member-card { min-height: auto; }
    }
  </style>
</head>
<body class="h-full overflow-auto bg-gradient-to-b from-[#0f3d1f] via-[#14532d] to-[#166534]" style="font-family:'Inter',sans-serif;">
  @include('public.partials.header')

  <main id="main-content" class="w-full relative pt-24">
    <section class="relative py-16 md:py-24 overflow-hidden">
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-lime-500 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-amber-400 rounded-full blur-[80px]"></div>
      </div>
      <div class="container mx-auto px-4 text-center relative z-10">
        <div class="fade-in-up" style="animation-delay:.1s;">
          <span id="season-badge" class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase bg-lime-500/20 text-lime-400 border border-lime-500/30 mb-6 floating-badge">Temporada {{ date('Y') }}</span>
        </div>
        <h1 id="main-title" class="fade-in-up text-5xl md:text-7xl lg:text-8xl font-bold text-white mb-4 tracking-tight" style="font-family:'Bebas Neue',sans-serif;animation-delay:.2s;">Directiva</h1>
        <p id="subtitle" class="fade-in-up text-lg md:text-xl text-green-200/80 max-w-2xl mx-auto" style="animation-delay:.3s;">La estrategia detrás del club</p>
        <div class="fade-in-up mt-8 flex justify-center items-center gap-4" style="animation-delay:.4s;">
          <div class="w-16 h-0.5 bg-gradient-to-r from-transparent to-lime-500"></div>
          <svg class="w-6 h-6 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3"/></svg>
          <div class="w-16 h-0.5 bg-gradient-to-l from-transparent to-lime-500"></div>
        </div>
      </div>
    </section>

    <section class="relative min-h-screen py-12 md:py-20">
      <div class="parallax-bg absolute inset-0 overflow-hidden" id="field-bg">
        <svg class="w-full h-full" viewBox="0 0 1200 1800" preserveAspectRatio="xMidYMid slice">
          <defs>
            <linearGradient id="fieldGradient" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" style="stop-color:#0f3d1f"/>
              <stop offset="50%" style="stop-color:#14532d"/>
              <stop offset="100%" style="stop-color:#166534"/>
            </linearGradient>
            <pattern id="grassPattern" patternUnits="userSpaceOnUse" width="40" height="40">
              <rect width="40" height="40" fill="transparent"/>
              <line x1="0" y1="20" x2="40" y2="20" stroke="rgba(134,239,172,.05)" stroke-width="1"/>
            </pattern>
            <marker id="arrowHead" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto" markerUnits="strokeWidth"><path d="M0,0 L0,6 L9,3 z" fill="white"/></marker>
            <marker id="arrowHeadGold" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto" markerUnits="strokeWidth"><path d="M0,0 L0,6 L9,3 z" fill="#fbbf24"/></marker>
          </defs>
          <rect width="100%" height="100%" fill="url(#fieldGradient)"/>
          <rect width="100%" height="100%" fill="url(#grassPattern)"/>
          <rect x="100" y="100" width="1000" height="1600" class="field-line" rx="8"/>
          <line x1="100" y1="900" x2="1100" y2="900" class="field-line"/>
          <circle cx="600" cy="900" r="120" class="field-line"/>
          <circle cx="600" cy="900" r="8" fill="rgba(134,239,172,.3)"/>

          <path id="path1" class="stroke-animate tactical-line" d="M 600 180 C 600 280, 550 350, 600 450" stroke="white" stroke-width="3" fill="none" stroke-dasharray="8,4" marker-end="url(#arrowHead)"/>
          <path id="path2" class="stroke-animate tactical-line" d="M 600 480 C 700 520, 850 550, 900 620" stroke="white" stroke-width="3" fill="none" stroke-dasharray="8,4" marker-end="url(#arrowHead)"/>
          <path id="path3" class="stroke-animate tactical-line" d="M 900 650 C 800 700, 650 750, 600 820" stroke="white" stroke-width="3" fill="none" marker-end="url(#arrowHead)"/>
          <path id="path4" class="stroke-animate tactical-line" d="M 600 850 C 550 920, 400 980, 350 1050" stroke="#fbbf24" stroke-width="3" fill="none" stroke-dasharray="12,6" marker-end="url(#arrowHeadGold)"/>
          <path id="path5" class="stroke-animate tactical-line" d="M 350 1080 C 400 1150, 500 1200, 600 1250" stroke="white" stroke-width="3" fill="none" marker-end="url(#arrowHead)"/>
          <path id="path6" class="stroke-animate tactical-line" d="M 600 1280 C 750 1300, 850 1350, 900 1400" stroke="white" stroke-width="3" fill="none" stroke-dasharray="8,4" marker-end="url(#arrowHead)"/>
          <path id="path7" class="stroke-animate tactical-line" d="M 900 1430 C 800 1480, 650 1500, 600 1550" stroke="#fbbf24" stroke-width="4" fill="none" marker-end="url(#arrowHeadGold)"/>
        </svg>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-6xl mx-auto">
          @forelse($lineups as $lineup)
            @php
              $pathId = 'path'.(($lineup->index % 7) + 1);
              $align = match($lineup->index % 4) {
                1 => 'md:justify-end md:pr-12 lg:pr-24',
                3 => 'md:justify-start md:pl-12 lg:pl-24',
                default => 'justify-center',
              };
            @endphp

            @if($lineup->left)
              <div class="member-wrapper fade-in-up mb-10 md:mb-16" data-path="{{ $pathId }}">
                <div class="flex {{ $align }}">
                  <div class="member-card glass-card rounded-2xl p-6 md:p-8 max-w-md w-full">
                    <div class="flex flex-col items-center text-center h-full justify-center">
                      <div class="relative mb-4">
                        <div class="member-photo w-24 h-24 md:w-28 md:h-28 rounded-xl bg-gradient-to-br from-lime-500 to-green-700 p-1 transition-all duration-300">
                          <div class="w-full h-full rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 overflow-hidden flex items-center justify-center">
                            @if($lineup->left->foto_url)
                              <img src="{{ $lineup->left->foto_url }}" alt="{{ $lineup->left->full_name }}" class="w-full h-full object-cover">
                            @else
                              <svg class="w-14 h-14 text-green-200/50" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            @endif
                          </div>
                        </div>
                      </div>
                      <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-lime-500/20 text-lime-400 border border-lime-500/30 mb-2">{{ $lineup->left->rol }}</span>
                      <h3 class="text-2xl font-bold text-white mb-2" style="font-family:'Bebas Neue',sans-serif;letter-spacing:.05em;">{{ $lineup->left->full_name ?: 'Integrante' }}</h3>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            @if($lineup->right)
              <div class="member-wrapper fade-in-up mb-10 md:mb-16" data-path="{{ $pathId }}">
                <div class="flex {{ $align }}">
                  <div class="member-card glass-card rounded-2xl p-6 md:p-8 max-w-md w-full border-amber-400/30">
                    <div class="flex flex-col items-center text-center h-full justify-center">
                      <div class="relative mb-4">
                        <div class="member-photo w-24 h-24 md:w-28 md:h-28 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 p-1 transition-all duration-300">
                          <div class="w-full h-full rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 overflow-hidden flex items-center justify-center">
                            @if($lineup->right->foto_url)
                              <img src="{{ $lineup->right->foto_url }}" alt="{{ $lineup->right->full_name }}" class="w-full h-full object-cover">
                            @else
                              <svg class="w-14 h-14 text-amber-200/50" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            @endif
                          </div>
                        </div>
                      </div>
                      <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/20 text-amber-400 border border-amber-400/30 mb-2">{{ $lineup->right->rol }}</span>
                      <h3 class="text-2xl font-bold text-white mb-2" style="font-family:'Bebas Neue',sans-serif;letter-spacing:.05em;">{{ $lineup->right->full_name ?: 'Integrante' }}</h3>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            @if($lineup->extraCount > 0)
              <p class="text-xs text-amber-300/85 mb-8 text-center">+ {{ $lineup->extraCount }} integrante(s) más en este mismo nivel interno.</p>
            @endif
          @empty
            <div class="rounded-2xl border border-white/10 bg-black/20 p-8 text-center text-gray-200">No hay integrantes activos en directiva todavía.</div>
          @endforelse
        </div>
      </div>
    </section>

    <section class="relative py-16 md:py-24">
      <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
          <div class="fade-in-up glass-card rounded-3xl p-8 md:p-12 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-32 h-32 bg-lime-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-40 h-40 bg-amber-400/10 rounded-full blur-3xl"></div>
            <div class="relative z-10">
              <h2 id="closing-text" class="text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-4" style="font-family:'Bebas Neue',sans-serif;letter-spacing:.05em;">Una estrategia sólida fuera de la cancha</h2>
              <p class="text-green-200/70 mb-2 max-w-xl mx-auto">Cada miembro aporta su visión y compromiso para llevar al club hacia nuevos horizontes de éxito.</p>
              <p class="text-lime-300 text-sm">{{ $totalMembers }} integrante(s) activos en directiva.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    let ticking = false;
    const fieldBg = document.getElementById('field-bg');

    function updateParallax() {
      const scrolled = window.pageYOffset || document.documentElement.scrollTop;
      const sway = Math.sin(scrolled / 210) * 20;
      if (fieldBg) {
        fieldBg.style.transform = `translate3d(${sway}px, ${scrolled * 0.28}px, 0)`;
      }
      ticking = false;
    }

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(updateParallax);
        ticking = true;
      }
    }, { passive: true });

    const fadeObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
          const pathId = entry.target.dataset.path;
          if (pathId) {
            const path = document.getElementById(pathId);
            if (path) {
              setTimeout(() => path.classList.add('active'), 120);
            }
          }
        }
      });
    }, { root: null, rootMargin: '0px 0px -90px 0px', threshold: 0.12 });

    document.querySelectorAll('.fade-in-up, .member-wrapper').forEach(el => fadeObserver.observe(el));

    setTimeout(() => {
      document.querySelectorAll('.fade-in-up').forEach((el, idx) => setTimeout(() => el.classList.add('active'), idx * 120));
    }, 80);
  </script>
</body>
</html>

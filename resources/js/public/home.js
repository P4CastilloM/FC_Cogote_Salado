/* =========================================================
   ✅ CONFIG POR DEFECTO (DÓNDE CAMBIAR TEXTOS/COLORES)
========================================================== */
const defaultConfig = {
  club_name: 'FC COGOTE SALADO',
  hero_title: 'MÁS QUE AMIGOS, FAMILIA',
  hero_subtitle: 'Unidos dentro y fuera de la cancha. Donde la pasión por el fútbol nos une.',
  avisos_title: 'AVISOS IMPORTANTES',
  noticias_title: 'ÚLTIMAS NOTICIAS',
  destacados_title: 'JUGADORES DESTACADOS',
  primary_color: '#77BB14',
  secondary_color: '#CC9799',
  background_color: '#34205C',
  text_color: '#ffffff',
  accent_color: '#2C3784'
};

let config = { ...defaultConfig };

/* =========================================================
   ✅ HERO SLIDER
========================================================== */
let currentHeroSlide = 0;
const heroSlides = document.querySelectorAll('.hero-slide');
const heroDots = document.querySelectorAll('[data-slide]');

function showHeroSlide(index) {
  heroSlides.forEach((slide, i) => slide.classList.toggle('active', i === index));
  heroDots.forEach((dot, i) => {
    dot.classList.toggle('active', i === index);
    dot.classList.toggle('bg-club-gold', i === index);
    dot.classList.toggle('bg-white/40', i !== index);
  });
}

function nextHeroSlide() {
  currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
  showHeroSlide(currentHeroSlide);
}

heroDots.forEach(dot => {
  dot.addEventListener('click', () => {
    currentHeroSlide = parseInt(dot.dataset.slide, 10);
    showHeroSlide(currentHeroSlide);
  });
});

setInterval(nextHeroSlide, 5000);

/* =========================================================
   ✅ AVISOS CAROUSEL
========================================================== */
let currentAviso = 0;
const avisosCarousel = document.getElementById('avisos-carousel');
const avisosContainer = document.getElementById('avisos-container');
const avisoCards = document.querySelectorAll('.aviso-card');
const avisoDots = document.querySelectorAll('[data-aviso]');
let avisosPerView = 1;

function updateAvisosPerView() {
  if (window.innerWidth >= 1024) avisosPerView = 3;
  else if (window.innerWidth >= 768) avisosPerView = 2;
  else avisosPerView = 1;
}

function showAviso(index) {
  updateAvisosPerView();
  const maxIndex = Math.max(0, avisoCards.length - avisosPerView);
  currentAviso = Math.min(Math.max(0, index), maxIndex);

  const translateX = -(currentAviso * (100 / avisosPerView));
  if (avisosCarousel) avisosCarousel.style.transform = `translateX(${translateX}%)`;

  avisoDots.forEach((dot, i) => {
    dot.classList.toggle('active', i === currentAviso);
    dot.classList.toggle('bg-club-gold', i === currentAviso);
    dot.classList.toggle('bg-white/40', i !== currentAviso);
  });
}

document.getElementById('aviso-prev')?.addEventListener('click', () => showAviso(currentAviso - 1));
document.getElementById('aviso-next')?.addEventListener('click', () => showAviso(currentAviso + 1));

avisoDots.forEach(dot => {
  dot.addEventListener('click', () => showAviso(parseInt(dot.dataset.aviso, 10)));
});

/* =========================================================
   ✅ SWIPE EN MÓVIL (AVISOS)
========================================================== */
let touchStartX = 0;
let touchEndX = 0;

avisosContainer?.addEventListener('touchstart', (e) => {
  touchStartX = e.changedTouches[0].screenX;
}, { passive: true });

avisosContainer?.addEventListener('touchend', (e) => {
  touchEndX = e.changedTouches[0].screenX;
  const swipeThreshold = 50;
  const diff = touchStartX - touchEndX;

  if (Math.abs(diff) > swipeThreshold) {
    if (diff > 0) showAviso(currentAviso + 1);
    else showAviso(currentAviso - 1);
  }
}, { passive: true });

/* =========================================================
   ✅ AUTO-AVANCE AVISOS
========================================================== */
setInterval(() => {
  updateAvisosPerView();
  const maxIndex = Math.max(0, avisoCards.length - avisosPerView);
  if (currentAviso >= maxIndex) showAviso(0);
  else showAviso(currentAviso + 1);
}, 6000);

window.addEventListener('resize', () => showAviso(currentAviso));

/* =========================================================
   ✅ ELEMENT SDK INTEGRATION (EDIT PANEL)
========================================================== */
async function onConfigChange(newConfig) {
  config = { ...defaultConfig, ...newConfig };

  // Textos
  document.getElementById('header-club-name').textContent = config.club_name;

  const heroTitle = document.getElementById('hero-title');
  const titleParts = config.hero_title.split(',');
  if (titleParts.length > 1) {
    heroTitle.innerHTML = `${titleParts[0]}, <span class="text-club-gold">${titleParts[1].trim()}</span>`;
  } else {
    heroTitle.innerHTML = `${config.hero_title}`;
  }

  document.getElementById('hero-subtitle').textContent = config.hero_subtitle;
  document.getElementById('avisos-title').innerHTML = `<span class="text-club-gold">📢</span> ${config.avisos_title}`;
  document.getElementById('noticias-title').innerHTML = `<span class="text-club-gold">📰</span> ${config.noticias_title}`;
  document.getElementById('destacados-title').innerHTML = `<span class="text-club-gold">⭐</span> ${config.destacados_title}`;
}

function mapToCapabilities(cfg) {
  return {
    recolorables: [
      {
        get: () => cfg.primary_color || defaultConfig.primary_color,
        set: (value) => {
          cfg.primary_color = value;
          window.elementSdk?.setConfig({ primary_color: value });
        }
      },
      {
        get: () => cfg.secondary_color || defaultConfig.secondary_color,
        set: (value) => {
          cfg.secondary_color = value;
          window.elementSdk?.setConfig({ secondary_color: value });
        }
      },
      {
        get: () => cfg.background_color || defaultConfig.background_color,
        set: (value) => {
          cfg.background_color = value;
          window.elementSdk?.setConfig({ background_color: value });
        }
      }
    ],
    borderables: [],
    fontEditable: undefined,
    fontSizeable: undefined
  };
}

function mapToEditPanelValues(cfg) {
  return new Map([
    ['club_name', cfg.club_name || defaultConfig.club_name],
    ['hero_title', cfg.hero_title || defaultConfig.hero_title],
    ['hero_subtitle', cfg.hero_subtitle || defaultConfig.hero_subtitle],
    ['avisos_title', cfg.avisos_title || defaultConfig.avisos_title],
    ['noticias_title', cfg.noticias_title || defaultConfig.noticias_title],
    ['destacados_title', cfg.destacados_title || defaultConfig.destacados_title]
  ]);
}

window.elementSdk?.init({
  defaultConfig,
  onConfigChange,
  mapToCapabilities,
  mapToEditPanelValues
});

/* =========================================================
   ✅ INIT
========================================================== */
showAviso(0);

/* =========================================================
   ✅ MOBILE MENU
========================================================== */
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
const menuIcon = document.getElementById('menu-icon');
const closeIcon = document.getElementById('close-icon');

mobileMenuBtn?.addEventListener('click', () => {
  mobileMenu?.classList.toggle('hidden');
  menuIcon?.classList.toggle('hidden');
  closeIcon?.classList.toggle('hidden');
});

document.querySelectorAll('#mobile-menu a').forEach(link => {
  link.addEventListener('click', () => {
    mobileMenu?.classList.add('hidden');
    menuIcon?.classList.remove('hidden');
    closeIcon?.classList.add('hidden');
  });
});

/* =========================================================
   ✅ NAV ACTIVE STATE (SCROLL)
========================================================== */
const sections = document.querySelectorAll('section[id]');
const navLinksDesktop = document.querySelectorAll('.nav-link[data-nav]');
const navLinksMobile = document.querySelectorAll('.nav-link-mobile[data-nav]');

function updateActiveNav() {
  const scrollPos = window.scrollY + 150;

  sections.forEach(section => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.offsetHeight;
    const sectionId = section.getAttribute('id');

    if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
      navLinksDesktop.forEach(link => {
        link.classList.toggle('active', link.dataset.nav === sectionId);
      });
      navLinksMobile.forEach(link => {
        link.classList.toggle('active', link.dataset.nav === sectionId);
      });
    }
  });
}

window.addEventListener('scroll', updateActiveNav);
updateActiveNav();

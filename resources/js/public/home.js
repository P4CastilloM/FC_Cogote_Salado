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
   ✅ CAROUSELS (AVISOS, NOTICIAS, DESTACADOS)
========================================================== */
function createCarousel(config) {
  const state = {
    current: 0,
    perView: 1,
    touchStartX: 0,
    touchEndX: 0,
  };

  const carousel = document.getElementById(config.carouselId);
  const container = document.getElementById(config.containerId);
  const cards = Array.from(document.querySelectorAll(config.cardSelector));
  const dots = Array.from(document.querySelectorAll(config.dotSelector));

  if (!carousel || !container || cards.length === 0) {
    return {
      show: () => {},
      next: () => {},
      prev: () => {},
      refresh: () => {},
    };
  }

  function updatePerView() {
    if (window.innerWidth >= 1024) state.perView = 3;
    else if (window.innerWidth >= 768) state.perView = 2;
    else state.perView = 1;
  }

  function renderDots(maxIndex) {
    dots.forEach((dot, i) => {
      dot.classList.toggle('hidden', i > maxIndex);
      dot.classList.toggle('active', i === state.current);
      dot.classList.toggle('bg-club-gold', i === state.current);
      dot.classList.toggle('bg-white/40', i !== state.current);
    });
  }

  function show(index) {
    updatePerView();
    const maxIndex = Math.max(0, cards.length - state.perView);
    state.current = Math.min(Math.max(0, index), maxIndex);

    const translateX = -(state.current * (100 / state.perView));
    carousel.style.transform = `translateX(${translateX}%)`;
    renderDots(maxIndex);
  }

  function prev() { show(state.current - 1); }
  function next() { show(state.current + 1); }

  document.getElementById(config.prevId)?.addEventListener('click', prev);
  document.getElementById(config.nextId)?.addEventListener('click', next);

  dots.forEach(dot => {
    dot.addEventListener('click', () => show(parseInt(dot.dataset[config.dotDataKey], 10) || 0));
  });

  container.addEventListener('touchstart', (e) => {
    state.touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  container.addEventListener('touchend', (e) => {
    state.touchEndX = e.changedTouches[0].screenX;
    const diff = state.touchStartX - state.touchEndX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) next();
      else prev();
    }
  }, { passive: true });

  show(0);

  return {
    show,
    next,
    prev,
    refresh: () => show(state.current),
    getCurrent: () => state.current,
    getPerView: () => state.perView,
    cardsCount: cards.length,
  };
}

const avisosCarouselCtl = createCarousel({
  carouselId: 'avisos-carousel',
  containerId: 'avisos-container',
  cardSelector: '.aviso-card',
  dotSelector: '[data-aviso]',
  dotDataKey: 'aviso',
  prevId: 'aviso-prev',
  nextId: 'aviso-next',
});

const noticiasCarouselCtl = createCarousel({
  carouselId: 'noticias-carousel',
  containerId: 'noticias-container',
  cardSelector: '.noticia-card',
  dotSelector: '[data-noticia]',
  dotDataKey: 'noticia',
  prevId: 'noticia-prev',
  nextId: 'noticia-next',
});

const destacadosCarouselCtl = createCarousel({
  carouselId: 'destacados-carousel',
  containerId: 'destacados-container',
  cardSelector: '.destacado-card',
  dotSelector: '[data-destacado]',
  dotDataKey: 'destacado',
  prevId: 'destacado-prev',
  nextId: 'destacado-next',
});


function initAvisosTextOverflow() {
  const elements = Array.from(document.querySelectorAll('.js-aviso-desc'));
  elements.forEach((el) => {
    const fullText = (el.dataset.fullText || el.textContent || '').trim();
    el.dataset.fullText = fullText;
    el.classList.remove('aviso-scroll');
    el.innerHTML = fullText;

    const lineHeight = parseFloat(window.getComputedStyle(el).lineHeight || '20');
    const maxHeight = lineHeight * 3;
    el.style.maxHeight = `${maxHeight}px`;

    if (el.scrollHeight > maxHeight + 2) {
      el.classList.add('aviso-scroll');
      const inner = document.createElement('span');
      inner.className = 'aviso-desc-inner';
      inner.textContent = fullText;
      el.innerHTML = '';
      el.appendChild(inner);

      const distance = Math.max(0, inner.scrollHeight - maxHeight);
      const duration = Math.max(7, Math.round(distance / 14) + 6);
      el.style.setProperty('--aviso-scroll-distance', `${distance}px`);
      el.style.setProperty('--aviso-duration', `${duration}s`);
    }
  });
}

setInterval(() => {
  const maxIndex = Math.max(0, avisosCarouselCtl.cardsCount - avisosCarouselCtl.getPerView());
  if (avisosCarouselCtl.getCurrent() >= maxIndex) avisosCarouselCtl.show(0);
  else avisosCarouselCtl.next();
}, 6000);

window.addEventListener('resize', () => {
  avisosCarouselCtl.refresh();
  noticiasCarouselCtl.refresh();
  destacadosCarouselCtl.refresh();
  initAvisosTextOverflow();
});

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
initAvisosTextOverflow();

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

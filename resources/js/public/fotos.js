let photos = [];
let currentPhotoIndex = 0;
let renderedCount = 0;
const BATCH_SIZE = 20;
let observer = null;

function qs(id) { return document.getElementById(id); }

const resolveSrc = (s) => {
  if (!s) return '';
  if (s.startsWith('http') || s.startsWith('/')) return s;
  return `/fccogotesalado/storage/fotos/${s}`;
};

function photoCard(photo, index) {
  return `
    <div class="gallery-item loading" data-index="${index}">
      <img
        src="${resolveSrc(photo.src)}"
        alt="${photo.alt ?? 'Foto'}"
        loading="lazy"
        onload="this.parentElement.classList.remove('loading')"
        onerror="this.parentElement.style.display='none'"
      >
      <div class="gallery-overlay">
        <span class="text-club-gold text-xs sm:text-sm font-medium truncate">${photo.alt ?? ''}</span>
      </div>
      <div class="zoom-icon">
        <svg class="w-6 h-6 text-club-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
        </svg>
      </div>
    </div>
  `;
}

function bindCardClicks(scope = document) {
  scope.querySelectorAll('.gallery-item').forEach((item) => {
    if (item.dataset.bound === '1') return;
    item.dataset.bound = '1';
    item.addEventListener('click', () => openModal(parseInt(item.dataset.index, 10)));
  });
}

function appendBatch() {
  const galleryContainer = qs('galleryContainer');
  if (!galleryContainer || renderedCount >= photos.length) return;

  const next = photos.slice(renderedCount, renderedCount + BATCH_SIZE);
  const html = next.map((photo, idx) => photoCard(photo, renderedCount + idx)).join('');
  galleryContainer.insertAdjacentHTML('beforeend', html);
  bindCardClicks(galleryContainer);

  renderedCount += next.length;

  if (renderedCount >= photos.length) {
    const sentinel = qs('gallerySentinel');
    if (sentinel) sentinel.remove();
    observer?.disconnect();
  }
}

function setupInfiniteLoading() {
  const galleryContainer = qs('galleryContainer');
  if (!galleryContainer) return;

  let sentinel = qs('gallerySentinel');
  if (!sentinel) {
    sentinel = document.createElement('div');
    sentinel.id = 'gallerySentinel';
    sentinel.className = 'h-8 w-full';
    galleryContainer.parentElement?.appendChild(sentinel);
  }

  observer?.disconnect();
  observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) appendBatch();
    });
  }, { rootMargin: '300px 0px' });

  observer.observe(sentinel);
}

function renderGallery(photosData) {
  photos = photosData || [];
  renderedCount = 0;

  const galleryContainer = qs('galleryContainer');
  const emptyState = qs('emptyState');
  if (!galleryContainer) return;

  galleryContainer.innerHTML = '';

  if (photos.length === 0) {
    galleryContainer.classList.add('hidden');
    emptyState?.classList.remove('hidden');
    qs('gallerySentinel')?.remove();
    observer?.disconnect();
    return;
  }

  galleryContainer.classList.remove('hidden');
  emptyState?.classList.add('hidden');

  appendBatch();
  setupInfiniteLoading();
}

function openModal(index) {
  currentPhotoIndex = index;
  updateModalImage();
  qs('photoModal')?.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  qs('photoModal')?.classList.remove('active');
  document.body.style.overflow = '';
}

function updateModalImage() {
  const modalImage = qs('modalImage');
  const photoCounter = qs('photoCounter');
  const photo = photos[currentPhotoIndex];

  if (!photo || !modalImage) return;

  modalImage.src = resolveSrc(photo.src);
  modalImage.alt = photo.alt ?? 'Foto';

  if (photoCounter) {
    photoCounter.textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
  }
}

function nextPhoto() {
  if (!photos.length) return;
  currentPhotoIndex = (currentPhotoIndex + 1) % photos.length;
  updateModalImage();
}

function prevPhoto() {
  if (!photos.length) return;
  currentPhotoIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
  updateModalImage();
}

function initMobileMenu() {
  const mobileMenuBtn = qs('mobile-menu-btn');
  const mobileMenu = qs('mobile-menu');
  const menuIcon = qs('menu-icon');
  const closeIcon = qs('close-icon');

  mobileMenuBtn?.addEventListener('click', () => {
    mobileMenu?.classList.toggle('hidden');
    menuIcon?.classList.toggle('hidden');
    closeIcon?.classList.toggle('hidden');
  });

  document.querySelectorAll('#mobile-menu a').forEach((link) => {
    link.addEventListener('click', () => {
      mobileMenu?.classList.add('hidden');
      menuIcon?.classList.remove('hidden');
      closeIcon?.classList.add('hidden');
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  renderGallery(window.__PHOTOS__ || []);

  qs('closeModal')?.addEventListener('click', closeModal);
  qs('nextPhoto')?.addEventListener('click', nextPhoto);
  qs('prevPhoto')?.addEventListener('click', prevPhoto);

  const modal = qs('photoModal');
  modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

  document.addEventListener('keydown', (e) => {
    const modalEl = qs('photoModal');
    if (!modalEl?.classList.contains('active')) return;

    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft') prevPhoto();
  });

  let touchStartX = 0;
  let touchEndX = 0;

  modal?.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  modal?.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      diff > 0 ? nextPhoto() : prevPhoto();
    }
  }, { passive: true });
});

let photos = [];
let currentPhotoIndex = 0;

function qs(id) { return document.getElementById(id); }

/**
 * Asegura que el src quede bien aunque venga como:
 * - "/storage/fotos/benja.jpeg"
 * - "benja.jpeg"
 * - "https://..."
*/

const resolveSrc = (s) => {
  if (!s) return '';
  if (s.startsWith('http') || s.startsWith('/')) return s;
  return `/fccogotesalado/storage/fotos/${s}`;
};

function renderGallery(photosData) {
  photos = photosData || [];
  const galleryContainer = qs('galleryContainer');
  const emptyState = qs('emptyState');

  if (!galleryContainer) return;

  if (photos.length === 0) {
    galleryContainer.classList.add('hidden');
    emptyState?.classList.remove('hidden');
    return;
  }

  galleryContainer.classList.remove('hidden');
  emptyState?.classList.add('hidden');

  galleryContainer.innerHTML = photos.map((photo, index) => `
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
        </svg>
      </div>
    </div>
  `).join('');

  document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('click', () => openModal(parseInt(item.dataset.index, 10)));
  });
}

function openModal(index) {
  currentPhotoIndex = index;
  updateModalImage();
  const modal = qs('photoModal');
  modal?.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  const modal = qs('photoModal');
  modal?.classList.remove('active');
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

document.addEventListener('DOMContentLoaded', () => {
  // photosData viene desde Blade (window.__PHOTOS__)
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

  // Swipe en modal
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

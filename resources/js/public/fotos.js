const PAGE_SIZE = 12;

let allPhotos = [];
let filteredPhotos = [];
let visiblePhotos = PAGE_SIZE;
let currentPhotoIndex = 0;
let currentAlbum = 'all';

function qs(id) {
  return document.getElementById(id);
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function resolveSrc(src) {
  if (!src) return '';
  if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('/')) return src;
  return `/fccogotesalado/storage/${src}`;
}

function formatDate(value) {
  if (!value) return 'Sin fecha';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Sin fecha';
  return date.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
}

function uniqueAlbums(photos) {
  return [...new Set(photos.map((photo) => (photo.album || '').trim()).filter(Boolean))].sort((a, b) => a.localeCompare(b));
}

function makeHeightClass(index) {
  const patterns = ['h-tall', 'h-normal', 'h-short', 'h-normal'];
  return patterns[index % patterns.length];
}

function renderAlbumChips() {
  const chipsRoot = qs('albumChips');
  if (!chipsRoot) return;

  const albums = uniqueAlbums(allPhotos);
  const chips = ['all', ...albums];

  chipsRoot.innerHTML = chips.map((album) => {
    const isAll = album === 'all';
    const label = isAll ? 'Todos' : album;
    const activeClass = currentAlbum === album ? 'active' : '';

    return `<button type="button" class="gallery-chip ${activeClass}" data-album="${escapeHtml(album)}">${escapeHtml(label)}</button>`;
  }).join('');

  chipsRoot.querySelectorAll('.gallery-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      currentAlbum = chip.dataset.album;
      applyFilters();
      renderAlbumChips();
    });
  });
}

function photoCard(photo, index) {
  const album = photo.album ? `<span>${escapeHtml(photo.album)}</span>` : '<span>FC Cogote Salado</span>';

  return `
    <article class="gallery-item ${makeHeightClass(index)}" data-index="${index}">
      <img
        src="${resolveSrc(photo.src)}"
        alt="Foto FC Cogote Salado"
        loading="lazy"
        onerror="this.closest('.gallery-item')?.remove()"
      >
      <div class="gallery-overlay">
        ${album}
      </div>
      <div class="gallery-zoom-icon" aria-hidden="true">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
    </article>
  `;
}

function bindCardClicks(scope = document) {
  scope.querySelectorAll('.gallery-item').forEach((item) => {
    if (item.dataset.bound === '1') return;
    item.dataset.bound = '1';
    item.addEventListener('click', () => openModal(parseInt(item.dataset.index, 10)));
  });
}

function updateCounters(visibleCount, totalCount) {
  const remaining = Math.max(0, totalCount - visibleCount);
  qs('visibleCount').textContent = String(visibleCount);
  qs('totalCount').textContent = String(totalCount);
  qs('remainingCount').textContent = String(remaining);
  qs('photoCount').textContent = String(allPhotos.length);
  qs('albumCount').textContent = String(uniqueAlbums(allPhotos).length);

  const loadWrap = qs('loadMoreWrap');
  loadWrap?.classList.toggle('hidden', remaining === 0 || totalCount === 0);
}

function renderGallery() {
  const gallery = qs('galleryContainer');
  const empty = qs('emptyState');
  if (!gallery) return;

  const photosToRender = filteredPhotos.slice(0, visiblePhotos);
  gallery.innerHTML = photosToRender.map((photo, index) => photoCard(photo, index)).join('');
  bindCardClicks(gallery);

  const isEmpty = filteredPhotos.length === 0;
  gallery.classList.toggle('hidden', isEmpty);
  empty?.classList.toggle('hidden', !isEmpty);

  updateCounters(photosToRender.length, filteredPhotos.length);
}

function applyFilters() {
  const term = (qs('searchInput')?.value || '').trim().toLowerCase();
  const dateFrom = qs('dateFrom')?.value || '';
  const dateTo = qs('dateTo')?.value || '';
  const sortValue = qs('sortSelect')?.value || 'recent';

  filteredPhotos = allPhotos.filter((photo) => {
    const album = (photo.album || '').toLowerCase();
    const photoDate = photo.created_at ? String(photo.created_at).slice(0, 10) : '';

    const matchesSearch = !term || album.includes(term);
    const matchesAlbum = currentAlbum === 'all' || (photo.album || '') === currentAlbum;

    let matchesDate = true;
    if (dateFrom) matchesDate = matchesDate && photoDate >= dateFrom;
    if (dateTo) matchesDate = matchesDate && photoDate <= dateTo;

    return matchesSearch && matchesAlbum && matchesDate;
  });

  if (sortValue === 'oldest') {
    filteredPhotos.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
  } else if (sortValue === 'album') {
    filteredPhotos.sort((a, b) => (a.album || '').localeCompare(b.album || ''));
  } else {
    filteredPhotos.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
  }

  visiblePhotos = PAGE_SIZE;
  renderGallery();
}

function openModal(index) {
  if (!filteredPhotos.length) return;
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
  const counter = qs('photoCounter');
  const title = qs('lightboxTitle');
  const album = qs('lightboxAlbum');
  const date = qs('lightboxDate');
  const photo = filteredPhotos[currentPhotoIndex];

  if (!photo || !modalImage) return;

  modalImage.src = resolveSrc(photo.src);
  modalImage.alt = 'Foto ampliada FC Cogote Salado';

  if (counter) counter.textContent = `${currentPhotoIndex + 1} / ${filteredPhotos.length}`;
  if (title) title.textContent = 'FC Cogote Salado';
  if (album) album.textContent = photo.album || 'Galería';
  if (date) date.textContent = formatDate(photo.created_at);
}

function nextPhoto() {
  if (!filteredPhotos.length) return;
  currentPhotoIndex = (currentPhotoIndex + 1) % filteredPhotos.length;
  updateModalImage();
}

function prevPhoto() {
  if (!filteredPhotos.length) return;
  currentPhotoIndex = (currentPhotoIndex - 1 + filteredPhotos.length) % filteredPhotos.length;
  updateModalImage();
}

function bindEvents() {
  qs('searchInput')?.addEventListener('input', applyFilters);
  qs('dateFrom')?.addEventListener('change', applyFilters);
  qs('dateTo')?.addEventListener('change', applyFilters);
  qs('sortSelect')?.addEventListener('change', applyFilters);

  qs('clearFiltersBtn')?.addEventListener('click', () => {
    qs('searchInput').value = '';
    qs('dateFrom').value = '';
    qs('dateTo').value = '';
    qs('sortSelect').value = 'recent';
    currentAlbum = 'all';
    renderAlbumChips();
    applyFilters();
  });

  qs('loadMoreBtn')?.addEventListener('click', () => {
    visiblePhotos += PAGE_SIZE;
    renderGallery();
  });

  qs('closeModal')?.addEventListener('click', closeModal);
  qs('closeModalBackdrop')?.addEventListener('click', closeModal);
  qs('nextPhoto')?.addEventListener('click', nextPhoto);
  qs('prevPhoto')?.addEventListener('click', prevPhoto);

  document.addEventListener('keydown', (e) => {
    const modal = qs('photoModal');
    if (!modal?.classList.contains('active')) return;

    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft') prevPhoto();
  });

  let touchStartX = 0;
  let touchEndX = 0;

  qs('photoModal')?.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  qs('photoModal')?.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) nextPhoto(); else prevPhoto();
    }
  }, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
  allPhotos = Array.isArray(window.__PHOTOS__) ? [...window.__PHOTOS__] : [];
  renderAlbumChips();
  bindEvents();
  applyFilters();
});

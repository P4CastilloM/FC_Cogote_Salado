const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');

document.querySelectorAll('[data-action="toggle-sidebar"]').forEach((btn) => {
  btn.addEventListener('click', () => {
    sidebar?.classList.toggle('-translate-x-full');
    sidebar?.classList.toggle('translate-x-0');
    overlay?.classList.toggle('hidden');
  });
});

document.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
  trigger.addEventListener('click', () => {
    const key = trigger.getAttribute('data-accordion-trigger');
    const content = document.querySelector(`[data-accordion-content="${key}"]`);
    const arrow = document.querySelector(`[data-accordion-arrow="${key}"]`);

    document.querySelectorAll('[data-accordion-content]').forEach((item) => {
      if (item !== content) item.classList.remove('open');
    });

    document.querySelectorAll('[data-accordion-arrow]').forEach((item) => {
      if (item !== arrow) item.classList.remove('rotated');
    });

    content?.classList.toggle('open');
    arrow?.classList.toggle('rotated');
  });
});

document.querySelectorAll('form[data-confirm]').forEach((form) =>
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    }),
);
const toggle = document.querySelector('.menu-toggle');
const overlay = document.querySelector('.menu-overlay');
function menu(open) {
    document.body.classList.toggle('menu-open', open);
    if (toggle) toggle.setAttribute('aria-expanded', String(open));
    if (overlay) overlay.hidden = !open;
}
toggle?.addEventListener('click', () => menu(!document.body.classList.contains('menu-open')));
overlay?.addEventListener('click', () => menu(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') menu(false);
});
document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

/**
 * Login / Register flip kart animasyonu
 */
export function initAuthFlip(containerSelector = '[data-auth-flip]') {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const inner = container.querySelector('[data-flip-inner]');
    const triggers = container.querySelectorAll('[data-flip-trigger]');

    triggers.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            inner?.classList.toggle('flipped');
        });
    });
}

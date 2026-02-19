import './bootstrap';
import { initLoginForm } from './login-form.js';
import { initRegisterForm } from './register-form.js';
import { initAuthFlip } from './auth-flip.js';
import { initAdminCategorySort } from './admin-category-sort.js';
import { initDealerEmailVerify } from './dealer-email-verify.js';

function initConfirmDeleteModal() {
    const modal = document.getElementById('confirm_delete_modal');
    const titleEl = document.getElementById('confirm_delete_title');
    const msgEl = document.getElementById('confirm_delete_message');
    const yesBtn = document.getElementById('confirm_delete_yes');
    if (!modal || !titleEl || !msgEl || !yesBtn) return;

    let pendingForm = null;

    const escapeHtml = (str) => String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.confirm !== 'delete') return;

        e.preventDefault();
        pendingForm = form;

        titleEl.textContent = form.dataset.confirmTitle || 'Silme Onayı';
        const item = form.dataset.confirmItem || '';
        const suffix = form.dataset.confirmMessage || 'silmek istediğinize emin misiniz?';
        if (item) {
            msgEl.innerHTML = `<span class="font-semibold">${escapeHtml(item)}</span> ${escapeHtml(suffix)}`;
        } else {
            msgEl.textContent = suffix;
        }

        modal.showModal();
    }, true);

    yesBtn.addEventListener('click', () => {
        const form = pendingForm;
        pendingForm = null;
        modal.close();
        // form.submit() submit event’ini tetiklemez → tekrar modal açılmaz
        if (form) form.submit();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLoginForm();
    initRegisterForm();
    initAuthFlip();
    initDealerEmailVerify();
    initAdminCategorySort();
    initConfirmDeleteModal();
});

// Livewire re-render sonrası (filtre/pagination) tekrar init et
document.addEventListener('livewire:init', () => {
    const run = () => {
        initRegisterForm();
        initDealerEmailVerify();
        initAdminCategorySort();
    };
    try {
        // Livewire v3+
        window.Livewire?.hook?.('commit', ({ succeed }) => succeed(run));
    } catch (_) {}
    try {
        // Eski hook ismi (bazı versiyonlarda)
        window.Livewire?.hook?.('message.processed', run);
    } catch (_) {}
});

document.addEventListener('livewire:navigated', () => {
    initAdminCategorySort();
});

// Şifremi unuttum - tasarım amaçlı, e-posta gönderilmiş gibi kapatır
window.handleForgotPassword = function () {
    const modal = document.getElementById('forgot_password_modal');
    const input = document.getElementById('forgot_email');
    if (input) input.value = '';
    modal?.close();
};

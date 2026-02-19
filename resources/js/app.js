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

function initConfirmDealerStatusModal() {
    const modal = document.getElementById('confirm_dealer_status_modal');
    const titleEl = document.getElementById('confirm_dealer_status_title');
    const msgEl = document.getElementById('confirm_dealer_status_message');
    const yesBtn = document.getElementById('confirm_dealer_status_yes');
    if (!modal || !titleEl || !msgEl || !yesBtn) return;

    let pending = null; // { dealerId, action }

    const escapeHtml = (str) => String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const setYesStyle = (action) => {
        yesBtn.classList.remove('btn-success', 'btn-warning', 'btn-error');
        if (action === 'approve') {
            yesBtn.classList.add('btn-success');
            yesBtn.textContent = 'Evet, onayla';
        } else {
            yesBtn.classList.add('btn-warning');
            yesBtn.textContent = 'Evet, pasife al';
        }
    };

    document.addEventListener('click', (e) => {
        const target = e.target instanceof Element ? e.target.closest('[data-confirm="dealer-status"]') : null;
        if (!target) return;
        if (target.hasAttribute('disabled') || target.getAttribute('aria-disabled') === 'true') return;

        e.preventDefault();

        const dealerId = Number(target.dataset.confirmId || 0);
        const action = String(target.dataset.confirmAction || '');
        if (!dealerId || (action !== 'approve' && action !== 'reject')) return;

        pending = { dealerId, action };

        titleEl.textContent = target.dataset.confirmTitle || 'Onay';
        const item = target.dataset.confirmItem || '';
        const suffix = target.dataset.confirmMessage || 'Bu işlemi yapmak istediğinize emin misiniz?';
        if (item) {
            msgEl.innerHTML = `<span class="font-semibold">${escapeHtml(item)}</span> ${escapeHtml(suffix)}`;
        } else {
            msgEl.textContent = suffix;
        }

        setYesStyle(action);
        modal.showModal();
    }, true);

    document.addEventListener('click', (e) => {
        const target = e.target instanceof Element ? e.target.closest('[data-confirm="dealer-status"]') : null;
        if (!target) return;
        if (target.hasAttribute('disabled') || target.getAttribute('aria-disabled') === 'true') return;

        e.preventDefault();

        const dealerId = Number(target.dataset.confirmId || 0);
        const action = String(target.dataset.confirmAction || '');
        const method = String(target.dataset.confirmMethod || action);
        
        if (!dealerId || (action !== 'approve' && action !== 'reject')) return;

        pending = { dealerId, action, method };

        titleEl.textContent = target.dataset.confirmTitle || 'Onay';
        const item = target.dataset.confirmItem || '';
        const suffix = target.dataset.confirmMessage || 'Bu işlemi yapmak istediğinize emin misiniz?';
        if (item) {
            msgEl.innerHTML = `<span class="font-semibold">${escapeHtml(item)}</span> ${escapeHtml(suffix)}`;
        } else {
            msgEl.textContent = suffix;
        }

        setYesStyle(action);
        modal.showModal();
    }, true);

    yesBtn.addEventListener('click', () => {
        const payload = pending;
        pending = null;
        modal.close();

        if (!payload) return;
        
        // Livewire component'ini bul - dealer-index component'ini ara
        let $wire = null;
        
        // Önce DOM'dan wire:id ile ara
        const wireElement = document.querySelector('[wire\\:id]');
        if (wireElement && window.Livewire) {
            const wireId = wireElement.getAttribute('wire:id');
            $wire = window.Livewire.find(wireId);
        }
        
        // Bulunamazsa, tüm component'lerden ara
        if (!$wire && window.Livewire?.all) {
            try {
                const allComponents = window.Livewire.all();
                for (const comp of allComponents) {
                    const name = comp?.__instance?.getName?.();
                    if (name === 'admin.dealer-index' || name?.includes('DealerIndex')) {
                        $wire = comp;
                        break;
                    }
                }
            } catch (e) {
                console.warn('Livewire component bulunamadı:', e);
            }
        }
        
        // Method'u çağır
        if ($wire && payload.method) {
            try {
                if (payload.method === 'approve') {
                    $wire.approve(payload.dealerId);
                } else if (payload.method === 'reject') {
                    $wire.reject(payload.dealerId);
                } else {
                    $wire.call(payload.method, payload.dealerId);
                }
            } catch (e) {
                console.error('Livewire method çağrılırken hata:', e);
            }
        }
    });

    modal.addEventListener('close', () => {
        pending = null;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLoginForm();
    initRegisterForm();
    initAuthFlip();
    initDealerEmailVerify();
    initAdminCategorySort();
    initConfirmDeleteModal();
    initConfirmDealerStatusModal();
    initCopyToClipboard();
});

// Livewire re-render sonrası (filtre/pagination) tekrar init et
document.addEventListener('livewire:init', () => {
    const run = () => {
        initRegisterForm();
        initDealerEmailVerify();
        initAdminCategorySort();
        initConfirmDealerStatusModal();
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

// Copy to clipboard with toast
function initCopyToClipboard() {
    let toastContainer = null;
    
    // CSS animasyonlarını ekle
    if (!document.getElementById('toast-animations')) {
        const style = document.createElement('style');
        style.id = 'toast-animations';
        style.textContent = `
            @keyframes toastSlideDown {
                from {
                    opacity: 0;
                    transform: translateY(-100%);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @keyframes toastSlideUp {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(-100%);
                }
            }
            .toast-enter {
                animation: toastSlideDown 0.3s ease-out forwards;
            }
            .toast-exit {
                animation: toastSlideUp 0.2s ease-in forwards;
            }
        `;
        document.head.appendChild(style);
    }
    
    function showToast(message) {
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-50 flex flex-col gap-2';
            toastContainer.id = 'copy-toast-container';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = 'alert alert-success shadow-lg toast-enter';
        toast.innerHTML = `<span class="text-sm">${message}</span>`;
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            setTimeout(() => {
                toast.remove();
                if (toastContainer && toastContainer.children.length === 0) {
                    toastContainer.remove();
                    toastContainer = null;
                }
            }, 200);
        }, 2000);
    }
    
    document.addEventListener('click', async (e) => {
        const target = e.target.closest('[data-copy-text]');
        if (!target) return;
        
        const text = target.getAttribute('data-copy-text');
        if (!text) return;
        
        try {
            await navigator.clipboard.writeText(text);
            showToast('Panoya kopyalandı');
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showToast('Panoya kopyalandı');
            } catch (e) {
                console.error('Copy failed:', e);
            }
            document.body.removeChild(textArea);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLoginForm();
    initRegisterForm();
    initAuthFlip();
    initDealerEmailVerify();
    initAdminCategorySort();
    initConfirmDeleteModal();
    initConfirmDealerStatusModal();
    initCopyToClipboard();
});

// Şifremi unuttum - tasarım amaçlı, e-posta gönderilmiş gibi kapatır
window.handleForgotPassword = function () {
    const modal = document.getElementById('forgot_password_modal');
    const input = document.getElementById('forgot_email');
    if (input) input.value = '';
    modal?.close();
};

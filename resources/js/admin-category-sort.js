import Sortable from 'sortablejs';

export function initAdminCategorySort() {
    const tbody = document.getElementById('categories-sortable');
    if (!tbody) return;

    const enabled = tbody.dataset.sortable === '1';
    if (!enabled) return;

    if (tbody.dataset.sortableReady === '1') return;

    const reorderUrl = tbody.dataset.reorderUrl;
    const ust = tbody.dataset.ust || 'tumu';
    if (!reorderUrl) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let savingToast = null;
    function showToast(type, message) {
        // DaisyUI toast-ish (simple, inline)
        const containerId = 'admin-category-sort-toast';
        let container = document.getElementById(containerId);
        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(container);
        }
        const el = document.createElement('div');
        el.className = `alert ${type === 'success' ? 'alert-success' : type === 'error' ? 'alert-error' : 'alert-info'} shadow-lg`;
        el.innerHTML = `<span class="text-sm">${message}</span>`;
        container.appendChild(el);
        setTimeout(() => el.remove(), 2500);
    }

    async function persistOrder() {
        const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map((tr) => Number(tr.dataset.id));

        showToast('info', 'Sıralama kaydediliyor...');

        const res = await fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({ ids, ust }),
        });

        if (!res.ok) {
            let msg = 'Sıralama kaydedilemedi.';
            try {
                const data = await res.json();
                msg = data.message || msg;
            } catch (_) {}
            showToast('error', msg);
            return;
        }

        // UI: sıra numaralarını güncelle (0..)
        Array.from(tbody.querySelectorAll('tr[data-id]')).forEach((tr, i) => {
            tr.querySelector('.sort-order-label')?.replaceChildren(document.createTextNode(String(i)));
        });

        showToast('success', 'Sıralama kaydedildi.');
    }

    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: () => {
            persistOrder();
        },
    });

    tbody.dataset.sortableReady = '1';
}


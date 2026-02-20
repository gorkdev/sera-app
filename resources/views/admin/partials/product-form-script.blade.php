@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('product-form');
            if (!form) return;

            const units = JSON.parse(form.dataset.units || '{}');

    // --- SKU: sadece UPPERCASE ---
    const skuInput = document.getElementById('sku');
    const applySkuUppercase = () => {
        if (!skuInput) return;
        const next = String(skuInput.value || '').toUpperCase();
        if (skuInput.value !== next) skuInput.value = next;
    };
    if (skuInput) {
        skuInput.addEventListener('input', applySkuUppercase);
        skuInput.addEventListener('blur', applySkuUppercase);
        applySkuUppercase();
    }

            // --- AJAX submit: validasyon hatasında sayfa yenilenmez, görseller korunur ---
            if (form.dataset.ajaxSubmit === 'true') {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    // Trim
                    form.querySelectorAll('input[type="text"], textarea').forEach(el => {
                        el.value = (el.value || '').trim();
                    });

            applySkuUppercase();

                    // Boş unit_conversions satırlarını kaldır
                    const tbody = document.getElementById('unit-conversions-tbody');
                    if (tbody) {
                        tbody.querySelectorAll('tr').forEach(tr => {
                            const unitSel = tr.querySelector('select');
                            const adetInp = tr.querySelector('input[type="number"]');
                            if (unitSel && adetInp && (!unitSel.value || !adetInp.value)) {
                                unitSel.removeAttribute('name');
                                adetInp.removeAttribute('name');
                            }
                        });
                    }

                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn?.innerHTML;
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<span class="loading loading-spinner loading-sm"></span> Gönderiliyor...';
                    }

                    try {
                        const formData = new FormData(form);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content');
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                ...(csrfToken && {
                                    'X-CSRF-TOKEN': csrfToken
                                }),
                            },
                            redirect: 'manual',
                        });

                        if (response.status === 422) {
                            const data = await response.json();
                            showFormErrors(form, data.errors || {});
                            scrollToFirstError(form);
                        } else if (response.status === 302) {
                            window.scrollTo(0, 0);
                            const redirectUrl = response.headers.get('Location') || form.dataset.successRedirect || form.action;
                            window.location.href = redirectUrl;
                            return;
                        } else if ((response.status === 0 || response.type === 'opaqueredirect') && form.dataset.successRedirect) {
                            // redirect: 'manual' ile 302 bazen opaqueredirect (status 0) döner; yine listeye git
                            window.scrollTo(0, 0);
                            window.location.href = form.dataset.successRedirect;
                            return;
                        } else {
                            window.location.reload();
                        }
                    } catch (err) {
                        alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText || 'Ürün Oluştur';
                        }
                    }
                });
            }

            function showFormErrors(form, errors) {
                const ajaxAlert = document.getElementById('product-form-ajax-error');
                if (ajaxAlert) ajaxAlert.classList.remove('hidden');

                form.querySelectorAll('.product-ajax-error').forEach(el => el.remove());
                form.querySelectorAll('.input-error, .select-error, .textarea-error').forEach(el => el.classList
                    .remove('input-error', 'select-error', 'textarea-error'));

                const tabFields = {
                    genel: ['category_id', 'name', 'slug', 'sku', 'description', 'image', 'gallery_images'],
                    fiyat: ['cost_price', 'price', 'unit', 'unit_conversions'],
                    ozellikler: ['featured_badges', 'origin', 'shelf_life_days'],
                    durum: ['is_active'],
                };

                const addedToContainer = {
                    gallery: false,
                    unit: false
                };

                for (const [field, messages] of Object.entries(errors)) {
                    const msg = Array.isArray(messages) ? messages[0] : messages;

                    if (field.startsWith('gallery_images')) {
                        const formControl = form.querySelector('#gallery-container')?.closest('.form-control');
                        if (formControl && !addedToContainer.gallery) {
                            addedToContainer.gallery = true;
                            const errEl = document.createElement('p');
                            errEl.className = 'text-error text-sm mt-1 product-ajax-error';
                            errEl.textContent = msg;
                            formControl.appendChild(errEl);
                            formControl.querySelectorAll('.file-input').forEach(el => el.classList.add(
                                'input-error'));
                        }
                        continue;
                    }
                    if (field.startsWith('unit_conversions')) {
                        const formControl = form.querySelector('#unit-conversions-tbody')?.closest('.form-control');
                        if (formControl && !addedToContainer.unit) {
                            addedToContainer.unit = true;
                            const errEl = document.createElement('p');
                            errEl.className = 'text-error text-sm mt-1 product-ajax-error';
                            errEl.textContent = msg;
                            formControl.appendChild(errEl);
                        }
                        continue;
                    }

                    const input = form.querySelector(`[name="${field}"]`);
                    const formControl = input?.closest('.form-control');
                    if (formControl) {
                        const errEl = document.createElement('p');
                        errEl.className = 'text-error text-sm mt-1 product-ajax-error';
                        errEl.textContent = msg;
                        formControl.appendChild(errEl);
                        if (input) input.classList.add('input-error', 'select-error', 'textarea-error');
                    }
                }

                Object.keys(tabFields).forEach(tab => {
                    const fields = tabFields[tab];
                    const hasError = Object.keys(errors).some(key => fields.some(f => key === f || key
                        .startsWith(f + '.')));
                    const radio = form.querySelector(`input[name="product_tabs"][data-tab="${tab}"]`);
                    if (radio) radio.classList.toggle('tab-has-error', hasError);
                });

                const firstTabWithError = Object.keys(tabFields).find(tab =>
                    Object.keys(errors).some(key => tabFields[tab].some(f => key === f || key.startsWith(f +
                        '.')))
                );
                if (firstTabWithError) {
                    const radio = form.querySelector(`input[name="product_tabs"][data-tab="${firstTabWithError}"]`);
                    if (radio) radio.checked = true;
                }
            }

            function scrollToFirstError(form) {
                const first = form.querySelector('.product-ajax-error');
                if (first) first.closest('section')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            // --- Gönderimden önce tüm text/textarea inputları trim et (normal submit için) ---
            if (form.dataset.ajaxSubmit !== 'true') {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], textarea').forEach(el => {
                        el.value = (el.value || '').trim();
                    });
            applySkuUppercase();
                });
            }

            // --- Alt görseller (max 3) ---
            const galleryContainer = document.getElementById('gallery-container');
            const addGalleryBtn = document.getElementById('add-gallery-btn');
            const MAX_GALLERY = 3;

            if (addGalleryBtn && galleryContainer) {
                function countExistingGallery() {
                    const existingWrap = document.getElementById('gallery-existing');
                    if (!existingWrap) return 0;
                    const total = existingWrap.querySelectorAll('.gallery-existing-item').length;
                    const removed = existingWrap.querySelectorAll('input:checked').length;
                    return total - removed;
                }

                function addGalleryRow() {
                    const existingCount = countExistingGallery();
                    const newCount = galleryContainer.querySelectorAll('.gallery-row').length;
                    if (existingCount + newCount >= MAX_GALLERY) return;
                    const row = document.createElement('div');
                    row.className = 'gallery-row flex items-center gap-2';
                    row.innerHTML = `
                <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" class="file-input file-input-bordered file-input-sm flex-1 max-w-xs" />
                <button type="button" class="btn btn-ghost btn-sm btn-square btn-error gallery-remove" aria-label="Kaldır">
                    @svg('heroicon-o-x-mark', 'h-4 w-4')
                </button>
            `;
                    row.querySelector('.gallery-remove').addEventListener('click', () => {
                        row.remove();
                    });
                    galleryContainer.appendChild(row);
                }
                addGalleryBtn.addEventListener('click', addGalleryRow);
                // Create formunda en az 1 alt görsel slotu aç
                if (!document.getElementById('gallery-existing') && galleryContainer.querySelectorAll(
                        '.gallery-row').length === 0) {
                    addGalleryRow();
                }
                // Hata durumunda alt görsel inputuna kırmızı border
                const errorKeys = JSON.parse(form.dataset.errors || '[]');
                if (errorKeys.some(k => k === 'gallery_images' || String(k).startsWith('gallery_images'))) {
                    const firstGalleryInput = galleryContainer.querySelector('.gallery-row input[type="file"]');
                    if (firstGalleryInput) firstGalleryInput.classList.add('input-error');
                }
            }

            // --- Birim dönüşüm tablosu ---
            const tbody = document.getElementById('unit-conversions-tbody');
            const addUnitRowBtn = document.getElementById('add-unit-row');

            if (addUnitRowBtn && tbody) {
                let rowIndex = 0;
                const existingConversions = JSON.parse(form.dataset.unitConversions || '[]');
                const getDefaultUnit = () => document.getElementById('unit')?.value || 'adet';

                function addUnitRow(unit = '', adet = '') {
                    const tr = document.createElement('tr');
                    const unitOptions = Object.entries(units).map(([k, v]) =>
                        `<option value="${k}" ${unit === k ? 'selected' : ''}>${v}</option>`
                    ).join('');
                    tr.innerHTML = `
                <td>
                    <select name="unit_conversions[${rowIndex}][unit]" class="select select-sm select-bordered w-full">
                        <option value="">— Birim —</option>
                        ${unitOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="unit_conversions[${rowIndex}][adet]" class="input input-sm input-bordered w-24" min="1" placeholder="Adet" value="${adet}" />
                </td>
                <td>
                    <button type="button" class="btn btn-ghost btn-sm btn-square unit-row-remove" aria-label="Satır sil">
                        @svg('heroicon-o-trash', 'h-4 w-4')
                    </button>
                </td>
            `;
                    tr.querySelector('.unit-row-remove').addEventListener('click', () => tr.remove());
                    tbody.appendChild(tr);
                    rowIndex++;
                }

                addUnitRowBtn.addEventListener('click', () => addUnitRow(getDefaultUnit(), ''));

                if (existingConversions.length > 0) {
                    existingConversions.forEach(c => addUnitRow(c.unit || '', c.adet || ''));
                } else {
                    // İlk satır “default” gelsin: kullanıcı sadece adet girsin
                    addUnitRow(getDefaultUnit(), '');
                }

                // Gönderimden önce boş satırları kaldır (validasyon hatası önleme)
                form.addEventListener('submit', () => {
                    tbody.querySelectorAll('tr').forEach(tr => {
                        const unitSel = tr.querySelector('select');
                        const adetInp = tr.querySelector('input[type="number"]');
                        if (unitSel && adetInp && (!unitSel.value || !adetInp.value)) {
                            unitSel.removeAttribute('name');
                            adetInp.removeAttribute('name');
                        }
                    });
                });
            }

            // --- Maliyet → Satış fiyatı %50 ---
            const costInput = document.getElementById('cost_price');
            const priceInput = document.getElementById('price');
            if (costInput && priceInput) {
                let priceManuallyEdited = false;
                costInput.addEventListener('input', () => {
                    if (priceManuallyEdited) return;
                    const cost = parseFloat(costInput.value) || 0;
                    if (cost > 0) {
                        priceInput.value = (cost * 1.5).toFixed(2);
                    }
                });
                priceInput.addEventListener('input', () => {
                    priceManuallyEdited = true;
                });
            }

        });
    </script>
@endpush

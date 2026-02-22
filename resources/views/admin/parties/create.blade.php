@extends('layouts.admin')

@section('title', 'Yeni Parti')

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.parties.index') }}" class="hover:text-base-content">Partiler</a>
            <span>/</span>
            <span class="text-base-content">Yeni</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Yeni Parti</h1>
                <p class="mt-1 text-sm text-base-content/60">Yeni bir satış partisi oluşturun</p>
            </div>
            <a href="{{ route('admin.parties.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.parties.store') }}" class="admin-form max-w-4xl" id="party-form"
        data-party-form>
        @csrf

        <div role="tablist" class="tabs tabs-boxed tabs-lg mb-4 bg-base-200/50 p-1 rounded-lg" id="party-tabs">
            <input type="radio" name="party_tabs" role="tab" class="tab" aria-label="Genel" data-tab="genel"
                checked />
            <input type="radio" name="party_tabs" role="tab" class="tab" aria-label="Lojistik ve Tedarik"
                data-tab="lojistik" />
            <input type="radio" name="party_tabs" role="tab" class="tab" aria-label="Stok ve Varyant"
                data-tab="stok" />
            <input type="radio" name="party_tabs" role="tab" class="tab" aria-label="Bayi ve Zamanlama"
                data-tab="bayi" />
        </div>

        <div class="space-y-6">
            {{-- Tab 1: Genel Bilgiler --}}
            <div role="tabpanel" class="party-tab-panel pt-0" data-tab-panel="genel">
                <section class="admin-form-section">
                    <h2 class="form-section-title">
                        @svg('heroicon-o-document-text', 'h-4 w-4')
                        Genel Bilgiler
                    </h2>
                    <div class="form-section-body">
                        <div class="form-row form-row-2">
                            <div class="form-control">
                                <label for="party_code" class="label">
                                    <span class="label-text font-medium">Parti Kodu</span>
                                </label>
                                <input type="text" id="party_code" name="party_code" value="{{ old('party_code') }}"
                                    class="input input-bordered input-md w-full @error('party_code') input-error @enderror"
                                    placeholder="NL-TR-2026-02-0001" data-party-code-input />
                                <label class="label"><span class="label-text-alt">Boş bırakılırsa otomatik
                                        oluşturulur</span></label>
                                @error('party_code')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control">
                                <label for="name" class="label"><span class="label-text font-medium">Parti Adı <span
                                            class="text-error">*</span></span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    class="input input-bordered input-md w-full @error('name') input-error @enderror"
                                    placeholder="Örn: Şubat 2026 Partisi" required />
                                @error('name')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-control">
                            <label for="description" class="label"><span
                                    class="label-text font-medium">Açıklama</span></label>
                            <textarea id="description" name="description" rows="3"
                                class="textarea textarea-bordered textarea-md w-full resize-y @error('description') textarea-error @enderror"
                                placeholder="Bu parti hakkında kısa açıklama...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-control pt-2">
                            <label class="label"><span class="label-text font-medium">Durum</span></label>
                            <div class="badge badge-warning badge-lg">Taslak</div>
                            <label class="label"><span class="label-text-alt">Parti oluşturulunca taslak kaydedilir;
                                    aktivasyon zamanında otomatik açılır.</span></label>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Tab 2: Lojistik ve Tedarik --}}
            <div role="tabpanel" class="party-tab-panel pt-0 hidden" data-tab-panel="lojistik">
                <section class="admin-form-section">
                    <h2 class="form-section-title">
                        @svg('heroicon-o-truck', 'h-4 w-4')
                        Lojistik ve Tedarik
                    </h2>
                    <div class="form-section-body">
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Tedarikçi ve Tır</h3>
                            <div class="form-row form-row-2">
                                <div class="form-control">
                                    <label for="supplier_name" class="label"><span
                                            class="label-text font-medium">Tedarikçi <span
                                                class="text-error">*</span></span></label>
                                    <input type="text" id="supplier_name" name="supplier_name"
                                        value="{{ old('supplier_name') }}"
                                        class="input input-bordered input-md w-full @error('supplier_name') input-error @enderror"
                                        placeholder="Örn: Hollanda Green Bloom Ltd" required />
                                    @error('supplier_name')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-control">
                                    <label for="truck_plate" class="label"><span class="label-text font-medium">Tır
                                            Plakası <span class="text-error">*</span></span></label>
                                    <input type="text" id="truck_plate" name="truck_plate"
                                        value="{{ old('truck_plate') }}"
                                        class="input input-bordered input-md w-full @error('truck_plate') input-error @enderror"
                                        placeholder="34 ABC 123" required data-plate-input />
                                    @error('truck_plate')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Sürücü ve İletişim</h3>
                            <div class="form-row form-row-2">
                                <div class="form-control">
                                    <label for="driver_name" class="label"><span class="label-text font-medium">Sürücü
                                            Adı Soyadı</span></label>
                                    <input type="text" id="driver_name" name="driver_name"
                                        value="{{ old('driver_name') }}"
                                        class="input input-bordered input-md w-full @error('driver_name') input-error @enderror"
                                        placeholder="Örn: Ahmet Yılmaz" />
                                    @error('driver_name')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-control">
                                    <label for="driver_contact" class="label"><span
                                            class="label-text font-medium">Sürücü İletişim</span></label>
                                    <input type="tel" id="driver_contact" name="driver_contact"
                                        value="{{ old('driver_contact') }}"
                                        class="input input-bordered input-md w-full @error('driver_contact') input-error @enderror"
                                        placeholder="0555 555 55 55" inputmode="tel" data-phone-format />
                                    @error('driver_contact')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Tır Durumu ve Tarihler</h3>
                            @php
                                $defaultFlorist = old('florist_delivery_at');
                                if (!$defaultFlorist && old('arrived_at')) {
                                    $defaultFlorist = \Carbon\Carbon::parse(old('arrived_at'))
                                        ->addDay()
                                        ->format('Y-m-d\TH:i');
                                }
                                if (!$defaultFlorist && old('estimated_arrival_at')) {
                                    $defaultFlorist = \Carbon\Carbon::parse(old('estimated_arrival_at'))
                                        ->addDay()
                                        ->format('Y-m-d\TH:i');
                                }
                                if ($defaultFlorist && strlen($defaultFlorist) === 10) {
                                    $defaultFlorist = \Carbon\Carbon::parse($defaultFlorist)->format('Y-m-d\TH:i');
                                }
                            @endphp
                            <livewire:admin.party-lojistik-dates :truck_status="old('truck_status', '')" :departure_at="old('departure_at')" :arrived_at="old('arrived_at', old('estimated_arrival_at'))"
                                :florist_delivery_at="$defaultFlorist ?? ''" />
                        </div>
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Maliyetler</h3>
                            <div class="form-row form-row-3">
                                <div class="form-control">
                                    <label for="logistics_cost" class="label"><span
                                            class="label-text font-medium">Lojistik Maliyeti <span
                                                class="text-error">*</span></span></label>
                                    <input type="number" id="logistics_cost" name="logistics_cost"
                                        value="{{ old('logistics_cost') }}"
                                        class="input input-bordered input-md w-full @error('logistics_cost') input-error @enderror"
                                        step="0.01" min="0" placeholder="0.00" required />
                                    @error('logistics_cost')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-control">
                                    <label for="purchase_price_per_unit" class="label"><span
                                            class="label-text font-medium">Birim Maliyeti <span
                                                class="text-error">*</span></span></label>
                                    <input type="number" id="purchase_price_per_unit" name="purchase_price_per_unit"
                                        value="{{ old('purchase_price_per_unit') }}"
                                        class="input input-bordered input-md w-full @error('purchase_price_per_unit') input-error @enderror"
                                        step="0.01" min="0" placeholder="0.00" required />
                                    @error('purchase_price_per_unit')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-control">
                                    <label for="customs_cost" class="label"><span class="label-text font-medium">Gümrük
                                            / Vergi <span class="text-error">*</span></span></label>
                                    <input type="number" id="customs_cost" name="customs_cost"
                                        value="{{ old('customs_cost') }}"
                                        class="input input-bordered input-md w-full @error('customs_cost') input-error @enderror"
                                        step="0.01" min="0" placeholder="0.00" required />
                                    @error('customs_cost')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Tab 3: Stok ve Varyant --}}
            <div role="tabpanel" class="party-tab-panel pt-0 hidden" data-tab-panel="stok">
                <section class="admin-form-section">
                    <h2 class="form-section-title">
                        @svg('heroicon-o-square-3-stack-3d', 'h-4 w-4')
                        Stok ve Varyant
                    </h2>
                    <div class="form-section-body">
                        <p class="text-sm text-base-content/70 mb-4">Kategori seçin; ürünler yüklenecektir. Sadece adet
                            girilen satırlar partiye eklenir.</p>
                        <div class="form-control mb-4 max-w-sm">
                            <label for="stock_category" class="label"><span
                                    class="label-text font-medium">Kategori</span></label>
                            <select id="stock_category" class="select select-bordered select-md w-full"
                                data-stock-category-select>
                                <option value="">— Kategori seçin —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="stock_loader" class="hidden py-12" data-stock-loader>
                            <div class="flex justify-center">
                                <span class="loading loading-spinner loading-lg text-primary"></span>
                            </div>
                        </div>
                        <div id="stock_table_wrapper" class="overflow-x-auto hidden rounded-lg border border-base-300"
                            data-stock-table-wrapper>
                            <table class="table table-sm table-zebra admin-table">
                                <thead>
                                    <tr>
                                        <th>Kategori / Varyant</th>
                                        <th class="w-16 text-right">Adet</th>
                                        <th class="w-14 text-right">Çöp</th>
                                        <th class="w-20 text-right">Net</th>
                                        <th class="w-24 text-right">Alış (₺)</th>
                                        <th class="w-24 text-right">Satış (₺)</th>
                                        <th class="w-32 text-right">Kar (₺)</th>
                                        <th class="w-28 text-right">KDV'li Toplam (₺)</th>
                                    </tr>
                                </thead>
                                <tbody id="stock_table_body" data-stock-table-body>
                                </tbody>
                            </table>
                        </div>
                        <p id="stock_empty" class="text-base-content/50 text-sm py-6" data-stock-empty>Kategori seçerek
                            ürünleri yükleyin.</p>
                    </div>
                </section>
            </div>

            {{-- Tab 4: Bayi Erişim ve Zamanlama --}}
            <div role="tabpanel" class="party-tab-panel pt-0 hidden" data-tab-panel="bayi">
                <section class="admin-form-section">
                    <h2 class="form-section-title">
                        @svg('heroicon-o-calendar-days', 'h-4 w-4')
                        Bayi Erişim ve Zamanlama
                    </h2>
                    <div class="form-section-body">
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Zamanlama</h3>
                            <div class="form-row form-row-2">
                                <div class="form-control">
                                    <label for="starts_at" class="label"><span class="label-text font-medium">Aktivasyon
                                            Zamanı <span class="text-error">*</span></span></label>
                                    <input type="datetime-local" id="starts_at" name="starts_at"
                                        value="{{ old('starts_at') }}"
                                        class="input input-bordered input-md w-full @error('starts_at') input-error @enderror"
                                        required />
                                    <label class="label"><span class="label-text-alt">Bu tarihte parti sipariş almaya
                                            başlar</span></label>
                                    @error('starts_at')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-control" id="ends_at_wrapper">
                                    <label for="ends_at" class="label"><span class="label-text font-medium">Bitiş
                                            Tarihi</span></label>
                                    <input type="datetime-local" id="ends_at" name="ends_at"
                                        value="{{ old('ends_at') }}"
                                        class="input input-bordered input-md w-full @error('ends_at') input-error @enderror" />
                                    @error('ends_at')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-control mt-2">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" name="close_when_stock_runs_out" value="1"
                                        {{ old('close_when_stock_runs_out') ? 'checked' : '' }}
                                        class="checkbox checkbox-primary checkbox-sm" id="close_when_stock_runs_out" />
                                    <span class="label-text font-medium">Stok bitene kadar açık tut</span>
                                </label>
                                <label class="label"><span class="label-text-alt">İşaretlenirse bitiş tarihi
                                        gerekmez.</span></label>
                            </div>
                        </div>
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Grup Geciktirmeleri</h3>
                            <p class="text-sm text-base-content/70 mb-3">Varsayılan süreler grup ayarlarından gelir; parti
                                bazlı değiştirebilirsiniz.</p>
                            <div class="overflow-x-auto rounded-lg border border-base-300">
                                <table class="table table-sm admin-table">
                                    <thead>
                                        <tr>
                                            <th>Grup</th>
                                            <th class="w-32 text-right">Gecikme (dk)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dealerGroups as $group)
                                            <tr>
                                                <td>{{ $group->name }}</td>
                                                <td class="text-right">
                                                    <input type="number" name="group_delays[{{ $group->id }}]"
                                                        min="0"
                                                        value="{{ old("group_delays.{$group->id}", $group->delay_minutes) }}"
                                                        class="input input-bordered input-sm w-24 text-right"
                                                        placeholder="{{ $group->delay_minutes }}" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-subsection">
                            <h3 class="form-subsection-title">Görünürlük</h3>
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" name="visible_to_all" value="1"
                                        {{ old('visible_to_all', true) ? 'checked' : '' }}
                                        class="checkbox checkbox-primary" id="visible_to_all" />
                                    <span class="label-text font-medium">Tüm bayiler görsün</span>
                                </label>
                                <label class="label"><span class="label-text-alt">Kapalıysa aşağıdan sadece seçili
                                        gruplar partiyi görür.</span></label>
                            </div>
                            <div class="form-control mt-2 hidden" id="dealer_groups_wrapper">
                                <label class="label"><span class="label-text font-medium">Sadece bu
                                        gruplar</span></label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach ($dealerGroups as $group)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="dealer_group_ids[]" value="{{ $group->id }}"
                                                class="checkbox checkbox-sm"
                                                {{ in_array($group->id, old('dealer_group_ids', [])) ? 'checked' : '' }} />
                                            <span class="text-sm">{{ $group->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-6 mt-6 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Parti Oluştur
            </button>
            <a href="{{ route('admin.parties.index') }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const vatRate = {{ config('sera.tax.vat_rate', 20) }} / 100;
                const baseUrl = '{{ url('/yonetim/partiler/kategori') }}';

                // Tab switching: show/hide panels by data-tab; sadece Stok sekmesinde form genişler
                const tabRadios = document.querySelectorAll('input[name="party_tabs"]');
                const panels = document.querySelectorAll('[data-tab-panel]');
                const partyFormEl = document.querySelector('[data-party-form]');

                function showPanel(tabName) {
                    panels.forEach(p => {
                        p.classList.toggle('hidden', p.dataset.tabPanel !== tabName);
                    });
                    if (partyFormEl) partyFormEl.classList.toggle('party-form--wide', tabName === 'stok');
                }
                tabRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) showPanel(this.dataset.tab);
                    });
                });
                showPanel('genel');

                // Parti kodu: sadece EN harf, rakam, tire + büyük harf
                const partyCodeInput = document.querySelector('[data-party-code-input]');
                if (partyCodeInput) {
                    partyCodeInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^A-Za-z0-9\-]/g, '').toUpperCase();
                    });
                }

                const plateInput = document.querySelector('[data-plate-input]');
                if (plateInput) {
                    plateInput.addEventListener('input', function() {
                        this.value = this.value.toUpperCase();
                    });
                }

                const partyForm = document.querySelector('[data-party-form]');
                if (partyForm) {
                    partyForm.addEventListener('submit', function(e) {
                        const dep = (this.querySelector('[name="departure_at"]')?.value || '').trim();
                        const arr = (this.querySelector('[name="arrived_at"]')?.value || '').trim();
                        const flor = (this.querySelector('[name="florist_delivery_at"]')?.value || '').trim();
                        const d = dep ? new Date(dep).getTime() : 0;
                        const a = arr ? new Date(arr).getTime() : 0;
                        const f = flor ? new Date(flor).getTime() : 0;
                        let invalid = false;
                        if (d && a && d >= a) invalid = true;
                        if (a && f && f <= a) invalid = true;
                        if (invalid) {
                            e.preventDefault();
                            const lojistikTab = document.querySelector(
                                'input[name="party_tabs"][data-tab="lojistik"]');
                            if (lojistikTab) {
                                lojistikTab.checked = true;
                                showPanel('lojistik');
                                lojistikTab.closest('#party-tabs')?.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        }
                    });
                }

                function formatTrPhone(value) {
                    let digits = String(value ?? '').replace(/\D/g, '').slice(0, 11);
                    if (digits.startsWith('5')) digits = ('0' + digits).slice(0, 11);
                    const p1 = digits.slice(0, 4),
                        p2 = digits.slice(4, 7),
                        p3 = digits.slice(7, 9),
                        p4 = digits.slice(9, 11);
                    return [p1, p2, p3, p4].filter(Boolean).join(' ');
                }
                const phoneInput = document.querySelector('[data-phone-format]');
                if (phoneInput) {
                    const applyPhone = () => {
                        const n = formatTrPhone(phoneInput.value);
                        if (phoneInput.value !== n) phoneInput.value = n;
                    };
                    phoneInput.addEventListener('input', applyPhone);
                    phoneInput.addEventListener('blur', applyPhone);
                }

                const categorySelect = document.querySelector('[data-stock-category-select]');
                const loader = document.querySelector('[data-stock-loader]');
                const tableWrapper = document.querySelector('[data-stock-table-wrapper]');
                const tableBody = document.querySelector('[data-stock-table-body]');
                const emptyMsg = document.querySelector('[data-stock-empty]');
                let allProductsMap = {};
                let stockData = {};
                const loadedCategoryIds = new Set();

                function saveCurrentTableToStockData() {
                    if (!tableBody) return;
                    tableBody.querySelectorAll('.party-stock-row').forEach(row => {
                        const pid = row.dataset.productId;
                        if (!pid) return;
                        const qty = row.querySelector('.stock-qty')?.value ?? 0;
                        const cost = row.querySelector('.stock-cost')?.value ?? '';
                        const price = row.querySelector('.stock-price')?.value ?? '';
                        const waste = row.querySelector('.stock-waste')?.value ?? 0;
                        if (qty || waste || cost || price) stockData[pid] = {
                            quantity: qty,
                            waste,
                            cost_price: cost,
                            price
                        };
                    });
                }

                function renderTable() {
                    const products = Object.values(allProductsMap);
                    const oldStocks = @json(old('stocks', []));
                    if (products.length === 0 || !tableBody) return;
                    tableBody.innerHTML = products.map(p => {
                        const saved = stockData[p.id] || oldStocks[p.id] || {};
                        const costPrice = saved.cost_price ?? p.cost_price ?? 0;
                        const priceVal = saved.price ?? p.price ?? 0;
                        const qty = parseInt(saved.quantity || 0, 10) || 0;
                        const waste = parseInt(saved.waste || 0, 10) || 0;
                        return `<tr class="party-stock-row" data-product-id="${p.id}">
                        <td>
                            <span class="text-xs text-base-content/50">${(p.category_name || '').replace(/</g, '&lt;')}</span>
                            <div class="font-medium">${(p.name || '').replace(/</g, '&lt;')}</div>
                            ${p.sku ? `<div class="text-xs text-base-content/50">SKU: ${String(p.sku).replace(/</g, '&lt;')}</div>` : ''}
                            <input type="hidden" name="stocks[${p.id}][product_id]" value="${p.id}">
                        </td>
                        <td class="text-right"><input type="number" name="stocks[${p.id}][quantity]" min="0" value="${qty}" class="input input-bordered input-sm w-14 text-right stock-qty" data-product-id="${p.id}"></td>
                        <td class="text-right"><input type="number" name="stocks[${p.id}][waste]" min="0" value="${waste}" class="input input-bordered input-sm w-12 text-right stock-waste" data-product-id="${p.id}"></td>
                        <td class="text-right"><span class="stock-net text-sm font-medium tabular-nums" data-product-id="${p.id}">0</span></td>
                        <td class="text-right"><input type="number" name="stocks[${p.id}][cost_price]" step="0.01" min="0" value="${costPrice}" class="input input-bordered input-sm w-24 text-right stock-cost" data-product-id="${p.id}" placeholder="${(p.cost_price ?? 0).toFixed(2)}"></td>
                        <td class="text-right"><input type="number" name="stocks[${p.id}][price]" step="0.01" min="0" value="${priceVal}" readonly class="input input-bordered input-sm w-24 text-right stock-price bg-base-200 read-only:bg-base-200 cursor-default" data-product-id="${p.id}" placeholder="${(p.price ?? 0).toFixed(2)}"></td>
                        <td class="text-right"><span class="stock-profit text-sm font-medium" data-product-id="${p.id}">—</span></td>
                        <td class="text-right"><span class="stock-vat-total text-sm font-medium" data-product-id="${p.id}">—</span></td>
                    </tr>`;
                    }).join('');
                    delegateStockInputs();
                }

                function updateStockRow(productId) {
                    const row = document.querySelector(`.party-stock-row[data-product-id="${productId}"]`);
                    if (!row) return;
                    const qty = parseInt(row.querySelector('.stock-qty')?.value || 0, 10) || 0;
                    const waste = parseInt(row.querySelector('.stock-waste')?.value || 0, 10) || 0;
                    const cost = parseFloat(row.querySelector('.stock-cost')?.value || 0) || 0;
                    const price = parseFloat(row.querySelector('.stock-price')?.value || 0) || 0;
                    const netSpan = row.querySelector('.stock-net');
                    if (netSpan) netSpan.textContent = Math.max(0, qty - waste);
                    const vatSpan = row.querySelector('.stock-vat-total');
                    if (vatSpan) {
                        const total = qty * price * (1 + vatRate);
                        vatSpan.textContent = total > 0 ? total.toLocaleString('tr-TR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) : '—';
                    }
                    const profitSpan = row.querySelector('.stock-profit');
                    const profit = qty > 0 ? (price - cost) * qty : 0;
                    if (profitSpan) {
                        profitSpan.textContent = profit !== 0 ? profit.toLocaleString('tr-TR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ' ₺' : '—';
                        profitSpan.className = 'stock-profit text-sm font-medium ' + (profit < 0 ? 'text-error' : (
                            profit > 0 ? 'text-success' : ''));
                    }
                }

                function delegateStockInputs() {
                    if (!tableBody) return;
                    tableBody.querySelectorAll('.stock-qty, .stock-waste, .stock-cost, .stock-price').forEach(el => {
                        const pid = el.dataset.productId;
                        if (!pid) return;
                        el.removeEventListener('input', el._stockHandler);
                        el._stockHandler = () => updateStockRow(pid);
                        el.addEventListener('input', el._stockHandler);
                        updateStockRow(pid);
                    });
                }

                categorySelect?.addEventListener('change', async function() {
                    const catId = this.value;
                    if (!catId) {
                        loader?.classList.add('hidden');
                        tableWrapper?.classList.add('hidden');
                        if (emptyMsg) {
                            emptyMsg.classList.remove('hidden');
                            emptyMsg.textContent = 'Kategori seçerek ürünleri yükleyin.';
                        }
                        return;
                    }
                    saveCurrentTableToStockData();
                    if (loadedCategoryIds.has(catId)) {
                        renderTable();
                        tableWrapper?.classList.remove('hidden');
                        emptyMsg?.classList.add('hidden');
                        return;
                    }
                    loader?.classList.remove('hidden');
                    tableWrapper?.classList.add('hidden');
                    emptyMsg?.classList.add('hidden');
                    try {
                        const r = await fetch(`${baseUrl}/${catId}/urunler`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const products = await r.json();
                        loader?.classList.add('hidden');
                        if (!products || products.length === 0) {
                            if (emptyMsg) {
                                emptyMsg.textContent = 'Bu kategoride ürün yok.';
                                emptyMsg.classList.remove('hidden');
                            }
                            return;
                        }
                        products.forEach(p => {
                            allProductsMap[p.id] = p;
                        });
                        loadedCategoryIds.add(catId);
                        renderTable();
                        tableWrapper?.classList.remove('hidden');
                        emptyMsg?.classList.add('hidden');
                    } catch (e) {
                        loader?.classList.add('hidden');
                        if (emptyMsg) {
                            emptyMsg.textContent = 'Ürünler yüklenirken hata oluştu.';
                            emptyMsg.classList.remove('hidden');
                        }
                    }
                });

                const closeCheckbox = document.getElementById('close_when_stock_runs_out');
                const endsAtWrapper = document.getElementById('ends_at_wrapper');
                const endsAtInput = document.getElementById('ends_at');

                function toggleEndsAt() {
                    if (closeCheckbox?.checked) {
                        if (endsAtWrapper) endsAtWrapper.style.opacity = '0.5';
                        if (endsAtInput) {
                            endsAtInput.disabled = true;
                            endsAtInput.removeAttribute('required');
                        }
                    } else {
                        if (endsAtWrapper) endsAtWrapper.style.opacity = '1';
                        if (endsAtInput) {
                            endsAtInput.disabled = false;
                            endsAtInput.setAttribute('required', 'required');
                        }
                    }
                }
                closeCheckbox?.addEventListener('change', toggleEndsAt);
                toggleEndsAt();

                const visibleCheckbox = document.getElementById('visible_to_all');
                const groupsWrapper = document.getElementById('dealer_groups_wrapper');

                function toggleVisibility() {
                    if (groupsWrapper) groupsWrapper.classList.toggle('hidden', !!visibleCheckbox?.checked);
                }
                visibleCheckbox?.addEventListener('change', toggleVisibility);
                toggleVisibility();
            });
        </script>
    @endpush

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const errorKeys = @json($errors->keys());
                const tabFields = {
                    genel: ['party_code', 'name', 'description'],
                    lojistik: ['supplier_name', 'truck_plate', 'driver_name', 'driver_contact', 'emergency_contact',
                        'truck_status', 'departure_at', 'estimated_arrival_at', 'arrived_at',
                        'florist_delivery_at', 'logistics_cost', 'purchase_price_per_unit', 'customs_cost',
                        'currency'
                    ],
                    stok: ['stocks'],
                    bayi: ['starts_at', 'ends_at', 'visible_to_all', 'dealer_group_ids', 'group_delays']
                };
                const tabsWithErrors = [];
                for (const tab of Object.keys(tabFields)) {
                    const hasError = errorKeys.some(key => tabFields[tab].some(f => key === f || key.startsWith(f +
                        '.')));
                    if (hasError) tabsWithErrors.push(tab);
                }
                tabsWithErrors.forEach(tab => {
                    const radio = document.querySelector(`input[name="party_tabs"][data-tab="${tab}"]`);
                    if (radio) radio.classList.add('tab-has-error');
                });
                if (tabsWithErrors.length > 0) {
                    const firstTab = document.querySelector(
                        `input[name="party_tabs"][data-tab="${tabsWithErrors[0]}"]`);
                    if (firstTab) {
                        firstTab.checked = true;
                        document.querySelectorAll('[data-tab-panel]').forEach(p => p.classList.toggle('hidden', p
                            .dataset.tabPanel !== firstTab.dataset.tab));
                        firstTab.closest('#party-tabs')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });
        </script>
    @endif
@endsection

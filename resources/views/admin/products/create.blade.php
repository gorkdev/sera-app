@extends('layouts.admin')

@section('title', 'Yeni Ürün')

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.products.index') }}" class="hover:text-base-content">Ürünler</a>
            <span>/</span>
            <span class="text-base-content">Yeni</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Yeni Ürün</h1>
                <p class="mt-1 text-sm text-base-content/60">Yeni bir ürün ekleyin</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    @php
        $productUnits = config('sera.product_units', ['adet' => 'Adet']);
        $featuredBadges = config('sera.product_featured_badges', []);
        $originOptions = config('sera.origin_options', []);
    @endphp

    <form method="POST" action="{{ route('admin.products.store') }}" class="admin-form space-y-6 max-w-3xl"
        enctype="multipart/form-data" id="product-form" data-units='@json($productUnits)' data-unit-conversions='[]'
        data-existing-gallery="0" data-errors='@json($errors->keys())' data-ajax-submit="true">
        @csrf

        <div role="tablist" class="tabs tabs-boxed tabs-lg mb-6 bg-base-200/50 p-1 rounded-lg" id="product-tabs">
            <input type="radio" name="product_tabs" role="tab" class="tab" aria-label="Genel" data-tab="genel"
                checked />
            {{-- Tab 1: Genel --}}
            <div role="tabpanel" class="tab-content py-4">
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                        @svg('heroicon-o-document-text', 'h-4 w-4')
                        Temel Bilgiler
                    </h2>
                    <div class="space-y-4">
                        <div class="form-control">
                            <label for="category_id" class="label"><span class="label-text font-medium">Kategori <span
                                        class="text-error">*</span></span></label>
                            <select name="category_id" id="category_id"
                                class="select select-md w-full @error('category_id') select-error @enderror">
                                <option disabled selected>Kategori seçin</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label for="name" class="label"><span class="label-text font-medium">Ürün Adı <span
                                        class="text-error">*</span></span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="input input-bordered input-md w-full @error('name') input-error @enderror"
                                placeholder="Örn: Kırmızı Gül Demeti" />
                            @error('name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-control">
                                <label for="slug" class="label"><span class="label-text font-medium">URL Slug <span
                                            class="text-error">*</span></span><span class="label-text-alt">Otomatik
                                        oluşur</span></label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                                    class="input input-bordered input-md w-full @error('slug') input-error @enderror" />
                                @error('slug')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control">
                                <label for="sku" class="label"><span class="label-text font-medium">SKU / Ürün Kodu
                                        <span class="text-error">*</span></span></label>
                                <input type="text" id="sku" name="sku" value="{{ old('sku') }}"
                                    class="input input-bordered input-md w-full @error('sku') input-error @enderror"
                                    placeholder="GR-001" />
                                @error('sku')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-control">
                            <label for="description" class="label"><span class="label-text font-medium">Ürün Açıklaması
                                    <span class="text-error">*</span></span></label>
                            <textarea id="description" name="description" rows="4"
                                class="textarea textarea-bordered textarea-md w-full resize-y @error('description') textarea-error @enderror"
                                placeholder="Ürün açıklaması (en az 10 karakter)...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Ana Görsel <span
                                        class="text-error">*</span></span><span class="label-text-alt">Max
                                    2MB</span></label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="file-input file-input-bordered file-input-md w-full bg-base-100 @error('image') input-error @enderror" />
                            @error('image')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Alt Görseller <span
                                        class="text-error">*</span></span><span class="label-text-alt">En az 1, en fazla
                                    3</span></label>
                            <div id="gallery-container" class="space-y-2"></div>
                            <button type="button" id="add-gallery-btn" class="btn btn-ghost btn-sm gap-2 mt-2">
                                @svg('heroicon-o-plus', 'h-4 w-4')
                                Görsel Ekle
                            </button>
                            @error('gallery_images')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>
            <input type="radio" name="product_tabs" role="tab" class="tab" aria-label="Fiyat & Birim"
                data-tab="fiyat" />
            {{-- Tab 2: Fiyat & Birim --}}
            <div role="tabpanel" class="tab-content py-4">
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
                        @svg('heroicon-o-banknotes', 'h-4 w-4')
                        Fiyat & Birim
                    </h2>
                    <div class="alert alert-info mb-4">
                        @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                        <div>
                            <p class="font-medium">Maliyet ve satış fiyatı</p>
                            <p class="text-sm opacity-90">Maliyet girildiğinde satış fiyatı otomatik %50 kâr ile
                                hesaplanır. İsterseniz değiştirebilirsiniz.</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 mb-6">
                        <div class="form-control">
                            <label for="cost_price" class="label"><span class="label-text font-medium">Maliyet (₺) <span
                                        class="text-error">*</span></span></label>
                            <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price') }}"
                                class="input input-bordered input-md w-full @error('cost_price') input-error @enderror"
                                step="0.01" min="0" placeholder="0.00" />
                            @error('cost_price')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label for="price" class="label"><span class="label-text font-medium">Satış Fiyatı (₺)
                                    <span class="text-error">*</span></span></label>
                            <input type="number" id="price" name="price" value="{{ old('price') }}"
                                class="input input-bordered input-md w-full @error('price') input-error @enderror"
                                step="0.01" min="0" placeholder="0.00" />
                            @error('price')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="form-control mb-6">
                        <label for="unit" class="label"><span class="label-text font-medium">Birim <span
                                    class="text-error">*</span></span></label>
                        <select name="unit" id="unit"
                            class="select select-md w-full @error('unit') select-error @enderror">
                            @foreach ($productUnits as $key => $label)
                                <option value="{{ $key }}" {{ old('unit', 'adet') == $key ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('unit')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Birim Dönüşümü <span
                                    class="text-error">*</span></span><span class="label-text-alt">En az 1 satır zorunlu —
                                1 birim = kaç adet?</span></label>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Birim</th>
                                        <th>1 birim = ? adet</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="unit-conversions-tbody"></tbody>
                            </table>
                        </div>
                        <button type="button" id="add-unit-row" class="btn btn-ghost btn-sm gap-2 mt-2">
                            @svg('heroicon-o-plus', 'h-4 w-4')
                            Satır Ekle
                        </button>
                    </div>
                </section>
            </div>
            <input type="radio" name="product_tabs" role="tab" class="tab" aria-label="Stok"
                data-tab="stok" />
            {{-- Tab 3: Stok --}}
            <div role="tabpanel" class="tab-content py-4">
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
                        @svg('heroicon-o-cube', 'h-4 w-4')
                        Stok
                    </h2>
                    <div class="alert alert-warning mb-4">
                        @svg('heroicon-o-exclamation-triangle', 'h-5 w-5 shrink-0')
                        <div>
                            <p class="font-medium">Kritik stok uyarısı</p>
                            <p class="text-sm opacity-90">Stok belirlediğiniz seviyeye (yüzde veya adet) ulaştığında uyarı
                                gönderilir. Örn: 10</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="form-control">
                            <label for="stock_quantity" class="label"><span class="label-text font-medium">Stok Miktarı
                                    <span class="text-error">*</span></span></label>
                            <input type="number" id="stock_quantity" name="stock_quantity"
                                value="{{ old('stock_quantity') }}"
                                class="input input-bordered input-md w-full @error('stock_quantity') input-error @enderror"
                                min="0" placeholder="0" />
                            @error('stock_quantity')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label for="min_order_quantity" class="label"><span class="label-text font-medium">Min.
                                    Sipariş <span class="text-error">*</span></span></label>
                            <input type="number" id="min_order_quantity" name="min_order_quantity"
                                value="{{ old('min_order_quantity') }}"
                                class="input input-bordered input-md w-full @error('min_order_quantity') input-error @enderror"
                                min="1" placeholder="1" />
                            @error('min_order_quantity')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label for="critical_stock_type" class="label"><span class="label-text font-medium">Kritik
                                    stok tipi</span></label>
                            <select name="critical_stock_type" id="critical_stock_type" class="select select-md w-full">
                                <option value="">— Seçin —</option>
                                <option value="percent" {{ old('critical_stock_type') == 'percent' ? 'selected' : '' }}>
                                    Yüzde (%)</option>
                                <option value="quantity" {{ old('critical_stock_type') == 'quantity' ? 'selected' : '' }}>
                                    Adet</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label for="critical_stock_value" class="label"><span class="label-text font-medium">Kritik
                                    değer</span></label>
                            <input type="number" id="critical_stock_value" name="critical_stock_value"
                                value="{{ old('critical_stock_value') }}" class="input input-bordered input-md w-full"
                                min="0" placeholder="10" />
                        </div>
                    </div>
                    <div class="form-control mt-4" id="critical-ref-wrap" style="display:none">
                        <label for="critical_stock_reference" class="label"><span
                                class="label-text font-medium">Referans (yüzde için)</span><span
                                class="label-text-alt">Örn: 100 — stok 100 iken %10 = 10 adet</span></label>
                        <input type="number" id="critical_stock_reference" name="critical_stock_reference"
                            value="{{ old('critical_stock_reference', 100) }}"
                            class="input input-bordered input-md w-full max-w-xs" min="1" />
                    </div>
                </section>
            </div>
            <input type="radio" name="product_tabs" role="tab" class="tab" aria-label="Özellikler"
                data-tab="ozellikler" />
            {{-- Tab 4: Özellikler --}}
            <div role="tabpanel" class="tab-content py-4">
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
                        @svg('heroicon-o-sparkles', 'h-4 w-4')
                        Özellikler
                    </h2>
                    <div class="space-y-6">
                        @if (count($featuredBadges) > 0)
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Öne çıkan etiketler</span><span
                                        class="label-text-alt">Birden fazla seçilebilir</span></label>
                                <div class="flex flex-wrap gap-3 mt-2">
                                    @foreach ($featuredBadges as $key => $label)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="featured_badges[]" value="{{ $key }}"
                                                class="checkbox checkbox-sm"
                                                {{ in_array($key, old('featured_badges', [])) ? 'checked' : '' }} />
                                            <span class="text-sm">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if (count($originOptions) > 0)
                            <div class="form-control">
                                <label for="origin" class="label"><span
                                        class="label-text font-medium">Menşei</span></label>
                                <select name="origin" id="origin" class="select select-md w-full max-w-xs">
                                    <option value="">— Seçin —</option>
                                    @foreach ($originOptions as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ old('origin') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-control">
                            <label for="shelf_life_days" class="label"><span class="label-text font-medium">Raf ömrü
                                    (gün)</span><span class="label-text-alt">Satışa uygun kalma süresi</span></label>
                            <input type="number" id="shelf_life_days" name="shelf_life_days"
                                value="{{ old('shelf_life_days') }}"
                                class="input input-bordered input-md w-full max-w-xs" min="1" max="365"
                                placeholder="7" />
                        </div>
                    </div>
                </section>
            </div>
            <input type="radio" name="product_tabs" role="tab" class="tab" aria-label="Durum"
                data-tab="durum" />
            {{-- Tab 5: Durum --}}
            <div role="tabpanel" class="tab-content py-4">
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
                        @svg('heroicon-o-eye', 'h-4 w-4')
                        Görünürlük
                    </h2>
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="hidden" name="is_active" value="0" />
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                                {{ old('is_active', true) ? 'checked' : '' }} />
                            <div>
                                <span class="label-text font-medium">Aktif</span>
                                <p class="text-xs text-base-content/60 mt-0.5">Aktif ürünler bayi panelinde listelenir</p>
                            </div>
                        </label>
                    </div>
                </section>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Ürün Oluştur
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>

    @include('admin.partials.category-slug-script')
    @include('admin.partials.product-form-script')

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const errorKeys = @json($errors->keys());
                const tabFields = {
                    genel: ['category_id', 'name', 'slug', 'sku', 'description', 'image', 'gallery_images'],
                    fiyat: ['cost_price', 'price', 'unit', 'unit_conversions'],
                    stok: ['stock_quantity', 'min_order_quantity', 'critical_stock_type', 'critical_stock_value',
                        'critical_stock_reference'
                    ],
                    ozellikler: ['featured_badges', 'origin', 'shelf_life_days'],
                    durum: ['is_active']
                };
                const tabsWithErrors = [];
                for (const tab of Object.keys(tabFields)) {
                    const fields = tabFields[tab];
                    const hasError = errorKeys.some(key =>
                        fields.some(f => key === f || key.startsWith(f + '.'))
                    );
                    if (hasError) tabsWithErrors.push(tab);
                }
                tabsWithErrors.forEach(tab => {
                    const radio = document.querySelector(`input[name="product_tabs"][data-tab="${tab}"]`);
                    if (radio) radio.classList.add('tab-has-error');
                });
                if (tabsWithErrors.length > 0) {
                    const firstTab = document.querySelector(
                        `input[name="product_tabs"][data-tab="${tabsWithErrors[0]}"]`);
                    if (firstTab) {
                        firstTab.checked = true;
                        firstTab.closest('#product-tabs')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });
        </script>
    @endif
@endsection

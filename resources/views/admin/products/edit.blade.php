@extends('layouts.admin')

@section('title', 'Ürün Düzenle — ' . $product->name)

@section('content')
<div class="admin-page-header mb-6">
    <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
        <a href="{{ route('admin.products.index') }}" class="hover:text-base-content">Ürünler</a>
        <span>/</span>
        <span class="text-base-content">{{ $product->name }}</span>
    </nav>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Ürün Düzenle</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $product->category->name }}
                @if($product->sku)
                    · <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $product->sku }}</code>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
            @svg('heroicon-o-chevron-left', 'h-4 w-4')
            Listeye dön
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" class="admin-form space-y-6 max-w-3xl">
    @csrf
    @method('PUT')

    {{-- Temel Bilgiler --}}
    <section class="admin-form-section">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
            @svg('heroicon-o-document-text', 'h-4 w-4')
            Temel Bilgiler
        </h2>
        <div class="space-y-4">
            <div class="form-control">
                <label for="category_id" class="label">
                    <span class="label-text font-medium">Kategori <span class="text-error">*</span></span>
                </label>
                <select name="category_id" id="category_id" required class="select select-md w-full @error('category_id') select-error @enderror">
                    <option disabled selected>Kategori seçin</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="name" class="label">
                    <span class="label-text font-medium">Ürün Adı <span class="text-error">*</span></span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                    class="input input-bordered input-md w-full @error('name') input-error @enderror"
                    placeholder="Örn: Kırmızı Gül Demeti" required />
                @error('name')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="form-control">
                    <label for="slug" class="label">
                        <span class="label-text font-medium">URL Slug</span>
                        <span class="label-text-alt">SEO için benzersiz</span>
                    </label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}"
                        class="input input-bordered input-md w-full @error('slug') input-error @enderror" />
                    @error('slug')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-control">
                    <label for="sku" class="label">
                        <span class="label-text font-medium">SKU / Stok Kodu</span>
                    </label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                        class="input input-bordered input-md w-full @error('sku') input-error @enderror"
                        placeholder="GR-001" />
                    @error('sku')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-control">
                <label for="description" class="label">
                    <span class="label-text font-medium">Açıklama</span>
                    <span class="label-text-alt">Ürün detayları (opsiyonel)</span>
                </label>
                <div class="textarea textarea-md w-full min-h-28 @error('description') textarea-error @enderror">
                    <textarea id="description" name="description" rows="5" class="resize-y"
                        placeholder="Ürün açıklaması...">{{ old('description', $product->description) }}</textarea>
                </div>
                @error('description')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- Fiyat & Stok --}}
    <section class="admin-form-section">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
            @svg('heroicon-o-banknotes', 'h-4 w-4')
            Fiyat & Stok
        </h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="form-control">
                <label for="price" class="label">
                    <span class="label-text font-medium">Fiyat (₺) <span class="text-error">*</span></span>
                </label>
                <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}"
                    class="input input-bordered input-md w-full @error('price') input-error @enderror"
                    step="0.01" min="0" required />
                @error('price')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-control">
                <label for="unit" class="label">
                    <span class="label-text font-medium">Birim <span class="text-error">*</span></span>
                </label>
                <select name="unit" id="unit" required class="select select-md w-full @error('unit') select-error @enderror">
                    <option disabled selected>Birim seçin</option>
                    @foreach(config('sera.product_units', ['adet' => 'Adet']) as $key => $label)
                        <option value="{{ $key }}" {{ old('unit', $product->unit) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('unit')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-control">
                <label for="stock_quantity" class="label">
                    <span class="label-text font-medium">Stok Miktarı</span>
                </label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}"
                    class="input input-bordered input-md w-full @error('stock_quantity') input-error @enderror"
                    min="0" />
                @error('stock_quantity')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-control">
                <label for="min_order_quantity" class="label">
                    <span class="label-text font-medium">Min. Sipariş</span>
                </label>
                <input type="number" id="min_order_quantity" name="min_order_quantity" value="{{ old('min_order_quantity', $product->min_order_quantity) }}"
                    class="input input-bordered input-md w-full @error('min_order_quantity') input-error @enderror"
                    min="1" />
                @error('min_order_quantity')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- Durum --}}
    <section class="admin-form-section">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
            @svg('heroicon-o-eye', 'h-4 w-4')
            Görünürlük
        </h2>
        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                    {{ old('is_active', $product->is_active) ? 'checked' : '' }} />
                <div>
                    <span class="label-text font-medium">Aktif</span>
                    <p class="text-xs text-base-content/60 mt-0.5">Aktif ürünler bayi panelinde listelenir</p>
                </div>
            </label>
        </div>
    </section>

    {{-- İşlemler --}}
    <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
        <button type="submit" class="btn btn-primary gap-2">
            @svg('heroicon-o-check', 'h-4 w-4')
            Değişiklikleri Kaydet
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">İptal</a>
    </div>
</form>
@endsection

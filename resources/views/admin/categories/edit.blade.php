@extends('layouts.admin')

@section('title', 'Kategori Düzenle — ' . $category->name)

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.categories.index') }}" class="hover:text-base-content">Kategoriler</a>
            <span>/</span>
            <span class="text-base-content">{{ $category->name }}</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Kategori Düzenle</h1>
                <p class="mt-1 text-sm text-base-content/60">Slug: <code
                        class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $category->slug }}</code></p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="admin-form space-y-6 max-w-3xl"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Temel Bilgiler --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-document-text', 'h-4 w-4')
                Temel Bilgiler
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Ne doldurmalıyım?</p>
                    <p class="text-sm opacity-90">Üst kategori: Alt kategori ise ana seçin. Örn: "Güller" için "Kesme
                        Çiçekler". Ad: Katalogda görünecek isim. Slug: Adres çubuğu. Örn: "kesme-cicekler". Açıklama: Kısa
                        bilgi. Görsel: Kategori kartı resmi.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="form-control">
                    <label for="parent_id" class="label">
                        <span class="label-text font-medium">Üst Kategori</span>
                    </label>
                    <select name="parent_id" id="parent_id"
                        class="select select-md w-full @error('parent_id') select-error @enderror">
                        <option value="" {{ !old('parent_id', $category->parent_id) ? 'selected' : '' }}>— Ana
                            kategori —</option>
                        @foreach ($parentCategories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="name" class="label">
                            <span class="label-text font-medium">Kategori Adı <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                            class="input input-bordered input-md w-full @error('name') input-error @enderror"
                            placeholder="Örn: Kesme Çiçekler" required />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="slug" class="label">
                            <span class="label-text font-medium">URL Slug</span>
                        </label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
                            class="input input-bordered input-md w-full @error('slug') input-error @enderror"
                            placeholder="kesme-cicekler" />
                        @error('slug')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label for="description" class="label">
                        <span class="label-text font-medium">Açıklama</span>
                    </label>
                    <div class="textarea textarea-md w-full min-h-28 @error('description') textarea-error @enderror">
                        <textarea id="description" name="description" rows="5" class="resize-y"
                            placeholder="Bu kategorideki ürünler hakkında açıklama...">{{ old('description', $category->description) }}</textarea>
                    </div>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="image" class="label">
                        <span class="label-text font-medium">Görsel</span>
                        <span class="label-text-alt">Max 2MB, JPG/PNG</span>
                    </label>
                    @if ($category->image)
                        <div class="flex items-center gap-4 mb-2">
                            <img src="{{ Storage::url($category->image) }}" alt=""
                                class="h-16 w-16 object-cover rounded-lg border border-base-300" />
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-error">
                                <input type="checkbox" name="remove_image" value="1" class="checkbox checkbox-sm" />
                                Görseli kaldır
                            </label>
                        </div>
                    @endif
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                        class="file-input file-input-bordered file-input-md w-full bg-base-100 @error('image') input-error @enderror" />
                    @error('image')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Durum --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
                @svg('heroicon-o-adjustments-horizontal', 'h-4 w-4')
                Durum
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Aktif / Pasif</p>
                    <p class="text-sm opacity-90">Aktif kategoriler katalogda listelenir. Pasif kategoriler gizlenir.</p>
                </div>
            </div>
            <input type="hidden" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" />
            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                        {{ old('is_active', $category->is_active) ? 'checked' : '' }} />
                    <span class="label-text font-medium">Aktif</span>
                </label>
            </div>
        </section>

        {{-- İşlemler --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Değişiklikleri Kaydet
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">İptal</a>
            <span class="text-sm text-base-content/50 ml-auto">{{ $category->products_count }} ürün bu kategoride</span>
        </div>
    </form>

    @include('admin.partials.category-slug-script')
@endsection

@extends('layouts.admin')

@section('title', 'Stok Ekle')

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.stocks.index', ['partyId' => $partyId]) }}" class="hover:text-base-content">Stoklar</a>
            <span>/</span>
            <span class="text-base-content">Yeni</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Stok Ekle</h1>
                <p class="mt-1 text-sm text-base-content/60">Parti bazlı stok kaydı oluşturun</p>
            </div>
            <a href="{{ route('admin.stocks.index', ['partyId' => $partyId]) }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.stocks.store') }}" class="admin-form space-y-6 max-w-3xl">
        @csrf

        {{-- Parti ve Ürün Seçimi --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-document-text', 'h-4 w-4')
                Parti ve Ürün
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Ne doldurmalıyım?</p>
                    <p class="text-sm opacity-90">Parti: Stok kaydının ekleneceği parti. Kategori: Ürünleri filtrelemek için kategori seçin. Ürün: Stok tanımlanacak ürün. Bir parti için bir ürün sadece bir kez tanımlanabilir.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="form-control">
                    <label for="party_id" class="label">
                        <span class="label-text font-medium">Parti <span class="text-error">*</span></span>
                    </label>
                    <select name="party_id" id="party_id"
                        class="select select-bordered select-md w-full @error('party_id') select-error @enderror" required>
                        <option value="">— Parti seçin —</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}" {{ old('party_id', $partyId) == $party->id ? 'selected' : '' }}>
                                {{ $party->name }}
                                @if($party->isActive())
                                    (Aktif)
                                @elseif($party->isDraft())
                                    (Taslak)
                                @else
                                    (Kapalı)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('party_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="category_id" class="label">
                        <span class="label-text font-medium">Kategori (Filtre)</span>
                    </label>
                    <select name="category_id" id="category_id"
                        class="select select-bordered select-md w-full"
                        onchange="filterProductsByCategory(this.value)">
                        <option value="">— Tüm kategoriler —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $categoryId) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label for="product_id" class="label">
                        <span class="label-text font-medium">Ürün <span class="text-error">*</span></span>
                    </label>
                    <select name="product_id" id="product_id"
                        class="select select-bordered select-md w-full @error('product_id') select-error @enderror" required>
                        <option value="">— Ürün seçin —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                data-category-id="{{ $product->category_id }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                                @if($product->sku)
                                    ({{ $product->sku }})
                                @endif
                                @if($product->category)
                                    — {{ $product->category->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if($products->isEmpty())
                        <label class="label">
                            <span class="label-text-alt text-warning">Seçili kategoride aktif ürün bulunamadı.</span>
                        </label>
                    @endif
                    @error('product_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Stok Detayları --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-adjustments-horizontal', 'h-4 w-4')
                Stok Detayları
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Stok Bilgileri</p>
                    <p class="text-sm opacity-90">Lokasyon: Çiçeklerin serada nerede saklandığı (örn: Sera A Bölgesi, 4. Raf). Toplam Stok: Tırdan gelen brüt miktar.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="form-control">
                    <label for="location" class="label">
                        <span class="label-text font-medium">Lokasyon</span>
                    </label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                        class="input input-bordered input-md w-full @error('location') input-error @enderror"
                        placeholder="Örn: Sera A Bölgesi, 4. Raf" />
                    @error('location')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="total_quantity" class="label">
                        <span class="label-text font-medium">Toplam Stok Miktarı (Brüt) <span class="text-error">*</span></span>
                    </label>
                    <input type="number" id="total_quantity" name="total_quantity" value="{{ old('total_quantity', 0) }}"
                        class="input input-bordered input-md w-full @error('total_quantity') input-error @enderror"
                        min="0" step="1" required />
                    <label class="label">
                        <span class="label-text-alt">Tırdan gelen toplam miktar</span>
                    </label>
                    @error('total_quantity')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- İşlemler --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Stok Ekle
            </button>
            <a href="{{ route('admin.stocks.index', ['partyId' => $partyId]) }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>

    <script>
        function filterProductsByCategory(categoryId) {
            const productSelect = document.getElementById('product_id');
            const options = productSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    // "Ürün seçin" seçeneği her zaman görünür
                    return;
                }
                
                const productCategoryId = option.getAttribute('data-category-id');
                if (categoryId === '' || productCategoryId === categoryId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    // Eğer seçili ürün gizleniyorsa, seçimi temizle
                    if (option.selected) {
                        productSelect.value = '';
                    }
                }
            });
        }
        
        // Sayfa yüklendiğinde kategori filtresini uygula
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');
            if (categorySelect.value) {
                filterProductsByCategory(categorySelect.value);
            }
        });
    </script>
@endsection

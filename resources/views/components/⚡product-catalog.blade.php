<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

new class extends Component {
    use WithPagination;

    public ?int $selectedCategory = null;
    public string $search = '';

    protected $queryString = [
        'selectedCategory' => ['except' => null],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedCategory = null;
        $this->search = '';
        $this->resetPage();
    }

    public function addToCart(int $productId, int $quantity = 1): void
    {
        if (! auth()->guard('dealer')->check()) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Fiyatları görmek ve sepete eklemek için önce bayi girişi yapmalısınız.',
            ]);

            return;
        }

        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Bu ürün şu anda satışta değil.',
            ]);
            return;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        $totalQuantity = array_sum(array_column($cart, 'quantity'));

        $this->dispatch('cart-updated', totalQuantity: $totalQuantity);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => $product->name . ' sepete eklendi.',
        ]);
    }

    public function getCategoriesProperty()
    {
        return Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getProductsProperty()
    {
        $query = Product::where('is_active', true)->with('category');

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate(12);
    }

    public function render()
    {
        return view('components.⚡product-catalog');
    }
};
?>

<div class="min-h-screen overflow-y-auto">
    <div class="container mx-auto px-4 lg:px-6 py-6 lg:py-8">
        <div class="flex flex-col lg:flex-row gap-6">
            {{-- Sol: Kategoriler Sidebar --}}
            <aside class="lg:w-56 shrink-0">
                <div class="bg-base-100 rounded-lg shadow-sm border border-base-300/50 sticky top-0 overflow-hidden">
                    <div class="px-4 py-3 border-b border-base-300/50 bg-base-50">
                        <h2
                            class="text-sm font-semibold uppercase tracking-wide text-base-content/70 flex items-center gap-2 mb-2">
                            @svg('heroicon-o-squares-2x2', 'h-4 w-4')
                            <span>Kategoriler</span>
                        </h2>
                        {{-- Arama: Kategorilerin üstünde, admin panel stilinde input (ikon yok) --}}
                        <div class="form-control mt-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Ürün, kategori veya SKU ara..."
                                class="input input-bordered input-sm w-full focus:outline-none" />
                        </div>
                    </div>
                    <div class="p-2 max-h-[calc(100vh-12rem)] overflow-y-auto">
                        <nav class="space-y-0.5 mt-1.5">
                            {{-- Tümü --}}
                            <a wire:click="selectCategory(null)" wire:key="cat-all"
                                class="flex items-center justify-between px-3 py-2 rounded-md text-sm transition-all duration-150 {{ $selectedCategory === null ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-200 text-base-content/80' }}"
                                href="javascript:void(0)">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="w-4 h-4 rounded-full border border-base-300 flex items-center justify-center text-[10px]">∞</span>
                                    <span>Tümü</span>
                                </span>
                                @if ($selectedCategory === null)
                                    @svg('heroicon-o-check-circle', 'h-4 w-4 text-primary')
                                @endif
                            </a>

                            {{-- Ana Kategoriler --}}
                            @foreach ($this->categories as $category)
                                @php
                                    $panelId = 'cat-children-' . $category->id;
                                @endphp
                                <div wire:key="cat-{{ $category->id }}" class="category-item">
                                    <button type="button"
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm transition-all duration-150 hover:bg-base-200 text-base-content/80 {{ $selectedCategory == $category->id ? 'bg-primary/10 text-primary font-medium' : '' }}"
                                        data-cat-toggle="{{ $panelId }}"
                                        onclick="toggleCategoryPanel('{{ $panelId }}')">
                                        <span class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center justify-center w-4 h-4 rounded-full border border-base-300 text-[10px]">
                                                {{ mb_substr($category->name, 0, 1) }}
                                            </span>
                                            <span class="truncate">{{ $category->name }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            @if ($selectedCategory == $category->id)
                                                @svg('heroicon-o-check-circle', 'h-4 w-4 text-primary')
                                            @endif
                                            @svg('heroicon-o-chevron-down', 'h-3.5 w-3.5 text-base-content/60 transition-transform duration-200')
                                        </span>
                                    </button>

                                    {{-- Alt Kategoriler (açılır/kapanır) --}}
                                    @php
                                        $isSelectedSelf = $selectedCategory == $category->id;
                                        $isSelectedChild = $category->children->contains('id', $selectedCategory);
                                        $isOpen = $isSelectedSelf || $isSelectedChild;
                                    @endphp
                                    <div id="{{ $panelId }}"
                                        class="ml-5 mt-0.5 space-y-0.5 overflow-hidden transition-all duration-200 {{ $isOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}"
                                        data-open="{{ $isOpen ? '1' : '0' }}">
                                        @if ($category->children->count() > 0)
                                            @foreach ($category->children as $child)
                                                <a wire:click="selectCategory({{ $child->id }})"
                                                    wire:key="cat-child-{{ $child->id }}"
                                                    class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition-all duration-150 {{ $selectedCategory == $child->id ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-200 text-base-content/70' }}"
                                                    href="javascript:void(0)">
                                                    <span class="truncate">{{ $child->name }}</span>
                                                    @if ($selectedCategory == $child->id)
                                                        @svg('heroicon-o-check-circle', 'h-3.5 w-3.5 text-primary')
                                                    @endif
                                                </a>
                                            @endforeach
                                        @else
                                            <div class="px-2.5 py-1.5 text-[11px] text-base-content/40 italic">
                                                Alt kategori yok
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </aside>

            {{-- Sağ: Ürünler Grid --}}
            <div class="flex-1 min-w-0">
                {{-- Filtreler ve Sonuç Sayısı --}}
                <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($selectedCategory || $search)
                            <span
                                class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Filtreler:</span>
                            @if ($selectedCategory)
                                @php
                                    $cat = \App\Models\Category::find($selectedCategory);
                                @endphp
                                <span class="badge badge-primary badge-sm gap-1.5 px-2.5 py-1">
                                    <span class="text-xs">{{ $cat->name ?? 'Kategori' }}</span>
                                    <button wire:click="selectCategory(null)"
                                        class="btn btn-xs btn-circle btn-ghost h-4 w-4 min-h-0 p-0 hover:bg-primary/20">
                                        @svg('heroicon-o-x-mark', 'h-2.5 w-2.5')
                                    </button>
                                </span>
                            @endif
                            @if ($search)
                                <span class="badge badge-primary badge-sm gap-1.5 px-2.5 py-1">
                                    <span class="text-xs">"{{ $search }}"</span>
                                    <button wire:click="$set('search', '')"
                                        class="btn btn-xs btn-circle btn-ghost h-4 w-4 min-h-0 p-0 hover:bg-primary/20">
                                        @svg('heroicon-o-x-mark', 'h-2.5 w-2.5')
                                    </button>
                                </span>
                            @endif
                            <button wire:click="clearFilters" class="btn btn-xs btn-ghost gap-1 h-7 px-2">
                                @svg('heroicon-o-x-circle', 'h-3 w-3')
                                <span class="text-xs">Temizle</span>
                            </button>
                        @else
                            <h2 class="text-lg font-semibold">Tüm Ürünler</h2>
                        @endif
                    </div>
                    <div class="text-xs text-base-content/60">
                        <span class="font-medium">{{ $this->products->total() }}</span> ürün
                    </div>
                </div>

                {{-- Ürün Grid / Boş durum aynı grid içinde --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                    @forelse($this->products as $product)
                        <div class="group bg-base-100 rounded-lg shadow-sm border border-base-300/50 hover:shadow-md hover:border-primary/30 hover:-translate-y-1 transition-all duration-200 overflow-hidden"
                            wire:key="product-{{ $product->id }}">
                            {{-- Ürün Görseli --}}
                            <div class="relative aspect-square overflow-hidden bg-base-200">
                                @if ($product->image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                        loading="lazy" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        @svg('heroicon-o-photo', 'h-10 w-10 text-base-content/20')
                                    </div>
                                @endif

                                {{-- Badges --}}
                                @if ($product->featured_badges && count($product->featured_badges) > 0)
                                    <div class="absolute top-1.5 left-1.5 flex flex-col gap-1">
                                        @foreach ($product->featured_badges as $badge)
                                            <span
                                                class="badge badge-warning badge-xs shadow-sm">{{ $badge }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Stok Durumu Overlay --}}
                                @if ($product->stock_quantity <= 0)
                                    <div
                                        class="absolute inset-0 bg-base-content/50 flex items-center justify-center backdrop-blur-sm">
                                        <span class="badge badge-error badge-sm shadow-md">Stokta Yok</span>
                                    </div>
                                @endif

                                {{-- Hover Overlay --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-base-content/70 via-base-content/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-end">
                                    <div class="w-full p-2">
                                        @if(auth()->guard('dealer')->check())
                                            <button wire:click="addToCart({{ $product->id }})"
                                                class="w-full btn btn-primary btn-xs gap-1 shadow-lg"
                                                @disabled($product->stock_quantity <= 0)
                                                title="{{ $product->stock_quantity <= 0 ? 'Stokta yok' : 'Sepete Ekle' }}">
                                                @svg('heroicon-o-shopping-cart', 'h-3.5 w-3.5')
                                                <span class="text-xs">Ekle</span>
                                            </button>
                                        @else
                                            <a
                                                href="{{ route('dealer.login') }}"
                                                class="w-full btn btn-outline btn-xs gap-1 shadow-lg"
                                            >
                                                Bayi Girişi Yap
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Ürün Bilgileri --}}
                            <div class="p-3">
                                @if ($product->category)
                                    <p class="text-[10px] font-medium text-primary mb-1 uppercase tracking-wider">
                                        {{ $product->category->name }}
                                    </p>
                                @endif

                                <h3
                                    class="text-sm font-semibold text-base-content mb-1.5 line-clamp-2 min-h-10 group-hover:text-primary transition-colors leading-tight">
                                    {{ $product->name }}
                                </h3>

                                <div class="flex items-center justify-between pt-2 border-t border-base-300/50 mt-2">
                                    <div class="min-w-0 flex-1">
                                        @if(auth()->guard('dealer')->check())
                                            <p class="text-base font-bold text-primary mb-0.5">
                                                {{ number_format($product->price, 2, ',', '.') }} ₺
                                            </p>
                                        @else
                                            <p class="text-xs text-base-content/60 mb-0.5 italic">
                                                Fiyatları görmek için bayi girişi yapın.
                                            </p>
                                        @endif
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @if ($product->stock_quantity > 0)
                                                <span class="badge badge-success badge-xs gap-0.5">
                                                    @svg('heroicon-o-check-circle', 'h-2.5 w-2.5')
                                                    <span class="text-[10px]">Stokta</span>
                                                </span>
                                            @else
                                                <span class="badge badge-error badge-xs gap-0.5">
                                                    @svg('heroicon-o-x-circle', 'h-2.5 w-2.5')
                                                    <span class="text-[10px]">Yok</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Mobile Sepete Ekle Butonu --}}
                                    @if(auth()->guard('dealer')->check())
                                        <button wire:click="addToCart({{ $product->id }})"
                                            class="btn btn-primary btn-xs gap-1 shrink-0 lg:hidden"
                                            @disabled($product->stock_quantity <= 0)
                                            title="{{ $product->stock_quantity <= 0 ? 'Stokta yok' : 'Sepete Ekle' }}">
                                            @svg('heroicon-o-shopping-cart', 'h-3.5 w-3.5')
                                        </button>
                                    @else
                                        <a
                                            href="{{ route('dealer.login') }}"
                                            class="btn btn-outline btn-xs gap-1 shrink-0 lg:hidden"
                                        >
                                            Giriş Yap
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Boş Durum: Grid içinde tek kart, ürün kartı genişliğinde --}}
                        <div
                            class="bg-base-100 rounded-lg shadow-sm border border-base-300/50 p-4 col-span-2 sm:col-span-3 lg:col-span-4 xl:col-span-5">
                            <div class="text-center max-w-sm mx-auto">
                                <div
                                    class="w-12 h-12 mx-auto mb-3 rounded-full bg-base-200 flex items-center justify-center">
                                    @svg('heroicon-o-magnifying-glass', 'h-6 w-6 text-base-content/30')
                                </div>
                                <h3 class="text-sm font-semibold mb-2">Ürün bulunamadı</h3>
                                <p class="text-xs text-base-content/70 mb-3">
                                    @if ($search || $selectedCategory)
                                        Arama kriterlerinize uygun ürün bulunamadı.
                                    @else
                                        Henüz katalogda ürün bulunmuyor.
                                    @endif
                                </p>
                                @if ($search || $selectedCategory)
                                    <button wire:click="clearFilters" class="btn btn-primary btn-xs gap-1">
                                        @svg('heroicon-o-arrow-path', 'h-3 w-3')
                                        <span>Filtreleri Temizle</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($this->products->hasPages())
                    <div class="mt-8 flex justify-center">
                        <div class="join">
                            {{ $this->products->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="toast-container" class="toast toast-top toast-center z-50" style="display: none;">
        <div id="toast-alert" class="alert shadow-lg min-w-[300px]">
            <span id="toast-message" class="font-medium"></span>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-toast', (event) => {
                const container = document.getElementById('toast-container');
                const alert = document.getElementById('toast-alert');
                const message = document.getElementById('toast-message');

                const isSuccess = event[0].type === 'success';
                alert.className =
                    `alert shadow-lg min-w-[300px] ${isSuccess ? 'alert-success' : 'alert-error'}`;
                message.textContent = event[0].message;
                container.style.display = 'block';

                setTimeout(() => {
                    container.style.display = 'none';
                }, 3000);
            });
        });

        // Kategori aç/kapa animasyonu
        function toggleCategoryPanel(panelId) {
            const panel = document.getElementById(panelId);
            if (!panel) return;

            const isOpen = panel.dataset.open === '1';
            if (isOpen) {
                panel.style.maxHeight = '0px';
                panel.classList.add('opacity-0');
                panel.dataset.open = '0';
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                panel.classList.remove('opacity-0');
                panel.dataset.open = '1';
            }
        }
    </script>
</div>

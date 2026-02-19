<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;

new class extends Component
{
    use WithPagination;

    public string $ust = 'tumu';   // tumu | ana | {id}
    public string $durum = 'tumu'; // tumu | aktif | pasif
    public string $ara = '';

    protected $queryString = [
        'ust' => ['except' => 'tumu'],
        'durum' => ['except' => 'tumu'],
        'ara' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        // Eski query parametreleriyle gelirse normalize et (geriye uyumlu)
        $parent = request('parent');
        $status = request('status');
        $search = request('search');

        if (! request()->has('ust') && $parent !== null) {
            $this->ust = $parent === 'all' ? 'tumu' : ($parent === 'root' ? 'ana' : (string) $parent);
        }
        if (! request()->has('durum') && $status !== null) {
            $this->durum = $status === 'all' ? 'tumu' : ($status === 'active' ? 'aktif' : ($status === 'passive' ? 'pasif' : (string) $status));
        }
        if (! request()->has('ara') && $search !== null) {
            $this->ara = (string) $search;
        }
    }

    public function updatingUst(): void { $this->resetPage(); }
    public function updatingDurum(): void { $this->resetPage(); }
    public function updatingAra(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->ust = 'tumu';
        $this->durum = 'tumu';
        $this->ara = '';
        $this->resetPage();
    }

    public function getRootCategoriesProperty()
    {
        return Category::whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getSortableEnabledProperty(): bool
    {
        return $this->ust !== 'tumu' && trim($this->ara) === '';
    }

    public function getCategoriesProperty()
    {
        $query = Category::query()
            ->with('parent')
            ->withCount('products');

        if ($this->ust === 'ana') {
            $query->whereNull('parent_id');
        } elseif (ctype_digit($this->ust)) {
            $query->where('parent_id', (int) $this->ust);
        }

        if ($this->durum === 'aktif') {
            $query->where('is_active', true);
        } elseif ($this->durum === 'pasif') {
            $query->where('is_active', false);
        }

        $search = trim($this->ara);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('sort_order')->orderBy('name')->paginate(20);
    }
};
?>

<div>
    <div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Kategoriler</h1>
            <p class="mt-1 text-sm text-base-content/60">Ürün kategorilerini yönetin</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm sm:btn-md gap-2 shrink-0">
            @svg('heroicon-o-plus', 'h-5 w-5')
            Yeni Kategori
        </a>
    </div>

    {{-- Filtreler (Livewire) --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
        <select wire:model.live="ust" class="select select-bordered select-sm w-56">
            <option value="tumu">Tüm kategoriler</option>
            <option value="ana">Ana kategoriler</option>
            @foreach($this->rootCategories as $root)
                <option value="{{ $root->id }}">{{ $root->name }} (alt kategoriler)</option>
            @endforeach
        </select>

        <select wire:model.live="durum" class="select select-bordered select-sm w-40">
            <option value="tumu">Tüm durumlar</option>
            <option value="aktif">Aktif</option>
            <option value="pasif">Pasif</option>
        </select>

        <input type="text"
            wire:model.live.debounce.400ms="ara"
            class="input input-bordered input-sm w-56"
            placeholder="Kategori adı veya slug..." />

        <button type="button" wire:click="resetFilters" class="btn btn-warning btn-sm gap-2"
            @disabled($ust === 'tumu' && $durum === 'tumu' && trim($ara) === '')>
            @svg('heroicon-o-arrow-path', 'h-4 w-4')
            Sıfırla
        </button>

        @if($this->sortableEnabled)
            <span class="text-xs text-base-content/60 ml-auto">
                Sürükle-bırak ile sıralayabilirsiniz.
            </span>
        @else
            <span class="text-xs text-base-content/60 ml-auto">
                Sıralama için “Ana kategoriler” veya bir üst kategori seçin. (Arama açıkken kapalıdır.)
            </span>
        @endif
    </div>

    <div class="rounded-xl border border-base-300 bg-base-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th class="w-16">Sıra</th>
                        <th>Kategori</th>
                        <th class="hidden md:table-cell">Üst Kategori</th>
                        <th class="w-24 text-center">Ürün</th>
                        <th class="w-24">Durum</th>
                        <th class="w-32 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="categories-sortable"
                    data-sortable="{{ $this->sortableEnabled ? '1' : '0' }}"
                    data-reorder-url="{{ route('admin.categories.reorder') }}"
                    data-ust="{{ $ust }}">
                    @forelse($this->categories as $category)
                        <tr class="hover" data-id="{{ $category->id }}" wire:key="c-{{ $category->id }}">
                            <td class="font-mono text-sm text-base-content/70">
                                <div class="flex items-center gap-2">
                                    @if ($this->sortableEnabled)
                                        <button type="button" class="btn btn-ghost btn-xs btn-square drag-handle" title="Sürükle">
                                            @svg('heroicon-o-bars-3', 'h-4 w-4')
                                        </button>
                                    @endif
                                    <span class="sort-order-label">{{ $category->sort_order }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $category->name }}</div>
                                @if ($category->description)
                                    <div class="text-sm text-base-content/50 mt-0.5 line-clamp-1 max-w-xs">
                                        {{ Str::limit($category->description, 45) }}
                                    </div>
                                @endif
                            </td>
                            <td class="hidden md:table-cell">
                                @if ($category->parent)
                                    <span class="badge badge-ghost badge-sm">{{ $category->parent->name }}</span>
                                @else
                                    <span class="text-base-content/40 text-sm">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="font-medium">{{ $category->products_count }}</span>
                            </td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge badge-success badge-sm">Aktif</span>
                                @else
                                    <span class="badge badge-ghost badge-sm">Pasif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                        @svg('heroicon-o-pencil-square', 'h-4 w-4')
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                        class="inline"
                                        data-confirm="delete"
                                        data-confirm-title="Kategoriyi Sil"
                                        data-confirm-item="{{ $category->name }}"
                                        data-confirm-message="kategorisini silmek istediğinize emin misiniz?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10"
                                            title="Sil">
                                            @svg('heroicon-o-trash', 'h-4 w-4')
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-base-content/60">
                                    @svg('heroicon-o-folder', 'h-12 w-12 opacity-40')
                                    <p class="font-medium">Henüz kategori yok</p>
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm gap-2">
                                        @svg('heroicon-o-plus', 'h-4 w-4')
                                        İlk kategoriyi oluştur
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->categories->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $this->categories->links() }}
            </div>
        @endif
    </div>
</div>
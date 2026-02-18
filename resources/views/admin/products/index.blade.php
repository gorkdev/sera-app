@extends('layouts.admin')

@section('title', 'Ürünler')

@section('content')
<div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-base-content">Ürünler</h1>
        <p class="mt-1 text-sm text-base-content/60">Ürün kataloğunu yönetin</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm sm:btn-md gap-2 shrink-0">
        @svg('heroicon-o-plus', 'h-5 w-5')
        Yeni Ürün
    </a>
</div>

{{-- Filtreler --}}
<form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
    <select name="category" class="select select-bordered select-sm w-48">
        <option value="" {{ !request('category') ? 'selected' : '' }}>Tüm kategoriler</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <input type="text" name="search" value="{{ request('search') }}"
        class="input input-bordered input-sm w-48"
        placeholder="Ürün adı veya SKU..." />
    <button type="submit" class="btn btn-ghost btn-sm gap-2">
        @svg('heroicon-o-magnifying-glass', 'h-4 w-4')
        Filtrele
    </button>
</form>

<div class="rounded-xl border border-base-300 bg-base-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th class="hidden sm:table-cell">Kategori</th>
                    <th class="hidden md:table-cell w-28">SKU</th>
                    <th class="w-24">Fiyat</th>
                    <th class="w-16 text-center">Stok</th>
                    <th class="hidden lg:table-cell w-20">Birim</th>
                    <th class="w-20">Durum</th>
                    <th class="w-24 text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="hover">
                    <td>
                        <div class="flex items-center gap-3">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="" class="h-10 w-10 object-cover rounded border border-base-300 shrink-0" />
                            @endif
                            <div>
                                <div class="font-medium">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-sm text-base-content/50 mt-0.5 line-clamp-1 max-w-xs">{{ Str::limit($product->description, 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="hidden sm:table-cell">
                        <span class="badge badge-ghost badge-sm">{{ $product->category->name }}</span>
                    </td>
                    <td class="hidden md:table-cell">
                        <code class="text-xs bg-base-200 px-1.5 py-0.5 rounded">{{ $product->sku ?? '—' }}</code>
                    </td>
                    <td class="font-medium tabular-nums">{{ $product->formatted_price }}</td>
                    <td class="text-center tabular-nums">{{ $product->stock_quantity }}</td>
                    <td class="hidden lg:table-cell text-sm text-base-content/70">{{ config('sera.product_units')[$product->unit] ?? $product->unit }}</td>
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-success badge-sm">Aktif</span>
                        @else
                            <span class="badge badge-ghost badge-sm">Pasif</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                @svg('heroicon-o-pencil-square', 'h-4 w-4')
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10" title="Sil">
                                    @svg('heroicon-o-trash', 'h-4 w-4')
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-16">
                        <div class="flex flex-col items-center gap-3 text-base-content/60">
                            @svg('heroicon-o-cube', 'h-12 w-12 opacity-40')
                            <p class="font-medium">Henüz ürün yok</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm gap-2">
                                @svg('heroicon-o-plus', 'h-4 w-4')
                                İlk ürünü oluştur
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="border-t border-base-300 px-4 py-3">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection

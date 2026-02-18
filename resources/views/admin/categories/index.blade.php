@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
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
            <tbody>
                @forelse($categories as $category)
                <tr class="hover">
                    <td class="font-mono text-sm text-base-content/70">{{ $category->sort_order }}</td>
                    <td>
                        <div class="font-medium">{{ $category->name }}</div>
                        @if($category->description)
                            <div class="text-sm text-base-content/50 mt-0.5 line-clamp-1 max-w-xs">{{ Str::limit($category->description, 45) }}</div>
                        @endif
                    </td>
                    <td class="hidden md:table-cell">
                        @if($category->parent)
                            <span class="badge badge-ghost badge-sm">{{ $category->parent->name }}</span>
                        @else
                            <span class="text-base-content/40 text-sm">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="font-medium">{{ $category->products_count }}</span>
                    </td>
                    <td>
                        @if($category->is_active)
                            <span class="badge badge-success badge-sm">Aktif</span>
                        @else
                            <span class="badge badge-ghost badge-sm">Pasif</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                @svg('heroicon-o-pencil-square', 'h-4 w-4')
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?');">
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
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="admin-page-header mb-6">
    <h1 class="text-2xl font-semibold text-base-content">Dashboard</h1>
    <p class="mt-1 text-sm text-base-content/60">Yönetim paneline hoş geldiniz</p>
</div>

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-base-300 bg-base-100 p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-primary/10 p-3">
                @svg('heroicon-o-folder', 'h-6 w-6 text-primary')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums">{{ \App\Models\Category::count() }}</p>
                <p class="text-sm text-base-content/60">Kategori</p>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-base-300 bg-base-100 p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-primary/10 p-3">
                @svg('heroicon-o-cube', 'h-6 w-6 text-primary')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums">{{ \App\Models\Product::count() }}</p>
                <p class="text-sm text-base-content/60">Ürün</p>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-base-300 bg-base-100 p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-primary/10 p-3">
                @svg('heroicon-o-users', 'h-6 w-6 text-primary')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums">{{ \App\Models\Dealer::count() }}</p>
                <p class="text-sm text-base-content/60">Bayi</p>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-base-300 bg-base-100 p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-primary/10 p-3">
                @svg('heroicon-o-shopping-cart', 'h-6 w-6 text-primary')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums">0</p>
                <p class="text-sm text-base-content/60">Sipariş</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 rounded-xl border border-base-300 bg-base-100 p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-2">Hızlı Erişim</h2>
    <p class="text-sm text-base-content/60 mb-4">Sık kullanılan sayfalara hızlıca gidin</p>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-outline btn-sm gap-2">
            @svg('heroicon-o-plus', 'h-4 w-4')
            Yeni Kategori
        </a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-outline btn-sm gap-2">
            @svg('heroicon-o-plus', 'h-4 w-4')
            Yeni Ürün
        </a>
    </div>
</div>
@endsection

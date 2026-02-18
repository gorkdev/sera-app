@extends('layouts.app')

@section('title', 'Anasayfa')
@section('meta_description', 'Sera - Çiçek üreticileri ve bayiler için B2B toptan çiçek satış platformu.')

@section('content')
<div class="container mx-auto px-4 lg:px-6 py-12 lg:py-16">
    {{-- Hero --}}
    <section class="text-center max-w-3xl mx-auto mb-16">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-base-content mb-4">
            B2B Toptan Çiçek Satış Platformu
        </h1>
        <p class="text-lg text-base-content/80 mb-8">
            Çiçek üreticileri ve tedarikçileri ile bayileri bir araya getiren profesyonel alışveriş platformu.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('dealer.login') }}" class="btn btn-primary btn-lg gap-2">
                @svg('heroicon-o-arrow-right-on-rectangle', 'h-5 w-5 shrink-0')
                Bayi Girişi
            </a>
            <a href="{{ route('admin.login') }}" class="btn btn-outline btn-lg gap-2">
                @svg('heroicon-o-cog-6-tooth', 'h-5 w-5 shrink-0')
                Yönetim Paneli
            </a>
        </div>
    </section>

    {{-- Özellikler --}}
    <section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <div class="card bg-base-100 shadow-sm border border-base-300/50">
            <div class="card-body">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
                    @svg('heroicon-o-cube', 'h-6 w-6 text-primary')
                </div>
                <h2 class="card-title text-base">Parti Bazlı Satış</h2>
                <p class="text-base-content/70 text-sm">Stoklar parti bazında yönetilir. Bayiler zamanlayıcılı sepet ile güvenli alışveriş yapar.</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-300/50">
            <div class="card-body">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
                    @svg('heroicon-o-clock', 'h-6 w-6 text-primary')
                </div>
                <h2 class="card-title text-base">Zamanlayıcılı Sepet</h2>
                <p class="text-base-content/70 text-sm">Ürün sepete eklenince süre başlar. Stok rezervasyonu ile adil satış süreci.</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-300/50 sm:col-span-2 lg:col-span-1">
            <div class="card-body">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
                    @svg('heroicon-o-user-group', 'h-6 w-6 text-primary')
                </div>
                <h2 class="card-title text-base">Bayi Grupları</h2>
                <p class="text-base-content/70 text-sm">VIP, Standart ve Yeni bayiler için farklı stok erişim zamanlamaları.</p>
            </div>
        </div>
    </section>
</div>
@endsection

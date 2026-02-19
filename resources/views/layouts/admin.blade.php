<!DOCTYPE html>
<html lang="tr" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Yönetim') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-base-200 antialiased">
    {{-- Top navbar --}}
    <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm">
        <div class="navbar px-4 lg:px-6">
            {{-- Mobile sidebar toggle --}}
            <label for="admin-drawer" class="btn btn-ghost btn-square drawer-button lg:hidden" aria-label="Menüyü aç">
                @svg('heroicon-o-bars-3', 'h-6 w-6')
            </label>

            {{-- Brand --}}
            <div class="flex-1">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost font-semibold text-lg">
                    <span class="text-primary">Sera</span>
                    <span class="hidden sm:inline text-base-content/70">Yönetim</span>
                </a>
            </div>

            {{-- User / Logout --}}
            <div class="flex-none gap-2">
                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm gap-2">
                    @svg('heroicon-o-arrow-right-on-rectangle', 'h-4 w-4 shrink-0')
                    Çıkış
                </button>
                </form>
            </div>
        </div>
    </header>

    <div class="drawer lg:drawer-open flex-1">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col">
            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-6">
                @if(session('success'))
                    <div role="alert" class="alert alert-success mb-4 shadow-sm" id="flash-success">
                        @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div role="alert" class="alert alert-error mb-4 shadow-sm">
                        @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side">
            <label for="admin-drawer" class="drawer-overlay" aria-label="Menüyü kapat"></label>
            <aside class="bg-base-100 border-r border-base-300 w-64 min-h-full flex flex-col">
                <nav class="admin-sidebar flex-1 overflow-y-auto py-4">
                    <ul class="menu px-3 gap-1 w-full">
                        <li class="menu-title px-3 py-2 text-base-content/50 text-xs font-semibold uppercase tracking-wider">Yönetim</li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 {{ request()->routeIs('admin.dashboard') ? 'active bg-primary/10 text-primary' : '' }}">
                                @svg('heroicon-o-squares-2x2', 'h-5 w-5 shrink-0 opacity-70')
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="menu-title px-3 py-2 mt-4 text-base-content/50 text-xs font-semibold uppercase tracking-wider">İçerik</li>
                        <li>
                            <a href="{{ route('admin.categories.index') }}" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 {{ request()->routeIs('admin.categories.*') ? 'active bg-primary/10 text-primary' : '' }}">
                                @svg('heroicon-o-folder', 'h-5 w-5 shrink-0 opacity-70')
                                <span>Kategoriler</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products.index') }}" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 {{ request()->routeIs('admin.products.*') ? 'active bg-primary/10 text-primary' : '' }}">
                                @svg('heroicon-o-cube', 'h-5 w-5 shrink-0 opacity-70')
                                <span>Ürünler</span>
                            </a>
                        </li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-photo', 'h-5 w-5 shrink-0')
                            <span>Bannerlar</span>
                        </a></li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-bars-3', 'h-5 w-5 shrink-0')
                            <span>Menüler</span>
                        </a></li>
                        <li class="menu-title px-3 py-2 mt-4 text-base-content/50 text-xs font-semibold uppercase tracking-wider">Satış</li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-calendar-days', 'h-5 w-5 shrink-0')
                            <span>Partiler</span>
                        </a></li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-square-3-stack-3d', 'h-5 w-5 shrink-0')
                            <span>Stoklar</span>
                        </a></li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-shopping-cart', 'h-5 w-5 shrink-0')
                            <span>Siparişler</span>
                        </a></li>
                        <li class="menu-title px-3 py-2 mt-4 text-base-content/50 text-xs font-semibold uppercase tracking-wider">Bayiler</li>
                        <li>
                            <a href="{{ route('admin.dealers.index') }}"
                               class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 {{ request()->routeIs('admin.dealers.*') ? 'active bg-primary/10 text-primary' : '' }}">
                                @svg('heroicon-o-users', 'h-5 w-5 shrink-0 opacity-70')
                                <span>Bayi Listesi</span>
                            </a>
                        </li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-user-group', 'h-5 w-5 shrink-0')
                            <span>Gruplar</span>
                        </a></li>
                        <li class="menu-title px-3 py-2 mt-4 text-base-content/50 text-xs font-semibold uppercase tracking-wider">Sistem</li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-shield-check', 'h-5 w-5 shrink-0')
                            <span>Yöneticiler</span>
                        </a></li>
                        <li><a href="#" class="w-full flex items-center gap-3 rounded-lg py-2.5 px-3 opacity-60">
                            @svg('heroicon-o-cog-6-tooth', 'h-5 w-5 shrink-0')
                            <span>Ayarlar</span>
                        </a></li>
                    </ul>
                </nav>
            </aside>
        </div>
    </div>

    {{-- Global confirm modal (delete) --}}
    <dialog id="confirm_delete_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-semibold text-lg flex items-center gap-2">
                @svg('heroicon-o-exclamation-triangle', 'h-5 w-5 text-warning')
                <span id="confirm_delete_title">Silme Onayı</span>
            </h3>
            <p class="py-4 text-base-content/70" id="confirm_delete_message">
                Bu öğeyi silmek istediğinize emin misiniz?
            </p>
            <div class="modal-action">
                <form method="dialog" class="flex items-center gap-2">
                    <button class="btn btn-ghost">Vazgeç</button>
                    <button type="button" class="btn btn-error" id="confirm_delete_yes">
                        Evet, sil
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button aria-label="Kapat">close</button>
        </form>
    </dialog>

    {{-- Global confirm modal (dealer approve / reject) --}}
    <dialog id="confirm_dealer_status_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-semibold text-lg flex items-center gap-2">
                @svg('heroicon-o-exclamation-triangle', 'h-5 w-5 text-warning')
                <span id="confirm_dealer_status_title">Onay</span>
            </h3>
            <p class="py-4 text-base-content/70" id="confirm_dealer_status_message">
                Bu işlemi yapmak istediğinize emin misiniz?
            </p>
            <div class="modal-action">
                <form method="dialog" class="flex items-center gap-2">
                    <button class="btn btn-ghost">Vazgeç</button>
                    <button type="button" class="btn" id="confirm_dealer_status_yes" data-wire-method="" data-wire-params="">
                        Evet
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button aria-label="Kapat">close</button>
        </form>
    </dialog>

    @livewireScripts
    @stack('scripts')
</body>
</html>

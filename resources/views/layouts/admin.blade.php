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
                <a href="{{ route('dashboard') }}" class="btn btn-ghost font-semibold text-lg">
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
                    <div role="alert" class="alert alert-success mb-4 shadow-sm">
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
            <aside class="bg-base-100 border-r border-base-300 w-64 min-h-full">
                <ul class="menu p-4 gap-1 font-medium">
                    <li class="menu-title text-base-content/60 text-xs uppercase tracking-wider">Yönetim</li>
                    <li><a href="{{ route('dashboard') }}" class="rounded-lg">Dashboard</a></li>
                    <li class="menu-title text-base-content/60 text-xs uppercase tracking-wider mt-4">İçerik</li>
                    <li><a href="#" class="rounded-lg">Kategoriler</a></li>
                    <li><a href="#" class="rounded-lg">Ürünler</a></li>
                    <li><a href="#" class="rounded-lg">Bannerlar</a></li>
                    <li><a href="#" class="rounded-lg">Menüler</a></li>
                    <li class="menu-title text-base-content/60 text-xs uppercase tracking-wider mt-4">Satış</li>
                    <li><a href="#" class="rounded-lg">Partiler</a></li>
                    <li><a href="#" class="rounded-lg">Stoklar</a></li>
                    <li><a href="#" class="rounded-lg">Siparişler</a></li>
                    <li class="menu-title text-base-content/60 text-xs uppercase tracking-wider mt-4">Bayiler</li>
                    <li><a href="#" class="rounded-lg">Bayi Listesi</a></li>
                    <li><a href="#" class="rounded-lg">Gruplar</a></li>
                    <li class="menu-title text-base-content/60 text-xs uppercase tracking-wider mt-4">Sistem</li>
                    <li><a href="#" class="rounded-lg">Yöneticiler</a></li>
                    <li><a href="#" class="rounded-lg">Ayarlar</a></li>
                </ul>
            </aside>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

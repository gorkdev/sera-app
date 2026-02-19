<!DOCTYPE html>
<html lang="tr" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Sera - B2B Toptan Çiçek Satış Platformu')">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-base-200 antialiased">
    {{-- Navbar --}}
    <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 transition-none">
        <nav class="navbar container mx-auto px-4 lg:px-6" aria-label="Ana menü">
            {{-- Logo --}}
            <div class="navbar-start min-w-0">
                <a href="{{ url('/') }}" class="btn btn-ghost gap-2 px-2 text-xl font-semibold tracking-tight">
                    <span class="text-primary">Sera</span>
                </a>
            </div>

            {{-- Desktop nav --}}
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal gap-1 px-1 font-medium">
                    <li><a href="{{ url('/') }}" class="rounded-lg">Anasayfa</a></li>
                    <li><a href="{{ route('dealer.login') }}" class="rounded-lg">Bayi Girişi</a></li>
                    <li><a href="{{ route('admin.login') }}" class="rounded-lg">Yönetim</a></li>
                </ul>
            </div>

            {{-- Mobile menu + Desktop actions --}}
            <div class="navbar-end gap-2">
                <div class="dropdown dropdown-end lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-square" aria-label="Menüyü aç">
                        @svg('heroicon-o-bars-3', 'h-6 w-6')
                    </div>
                    <ul tabindex="0" class="menu dropdown-content menu-sm dropdown-content mt-3 w-52 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                        <li><a href="{{ url('/') }}">Anasayfa</a></li>
                        <li><a href="{{ route('dealer.login') }}">Bayi Girişi</a></li>
                        <li><a href="{{ route('admin.login') }}">Yönetim</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="container mx-auto px-4 pt-4">
            <div role="alert" class="alert alert-success shadow-lg">
                @svg('heroicon-s-check-circle', 'h-6 w-6 shrink-0')
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="container mx-auto px-4 pt-4">
            <div role="alert" class="alert alert-error shadow-lg">
                @svg('heroicon-s-x-circle', 'h-6 w-6 shrink-0')
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-base-300 bg-base-100 mt-auto">
        <div class="container mx-auto px-4 lg:px-6 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <span class="font-semibold text-primary">Sera</span>
                    <span class="text-base-content/70"> — B2B Toptan Çiçek Satış Platformu</span>
                </div>
                <div class="flex gap-6 text-sm text-base-content/70">
                    <a href="{{ url('/') }}" class="link-hover link">Anasayfa</a>
                    <a href="{{ route('dealer.login') }}" class="link-hover link">Bayi Girişi</a>
                    <a href="{{ route('admin.login') }}" class="link-hover link">Yönetim</a>
                </div>
            </div>
            <div class="mt-6 pt-6 border-t border-base-300 text-center text-sm text-base-content/60">
                &copy; {{ date('Y') }} Sera. Tüm hakları saklıdır.
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>

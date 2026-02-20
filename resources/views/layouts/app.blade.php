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
                    @guest('dealer')
                        <li><a href="{{ route('dealer.login') }}" class="rounded-lg">Bayi Girişi</a></li>
                        <li><a href="{{ route('admin.login') }}" class="rounded-lg">Yönetim</a></li>
                    @else
                        <li>
                            <a href="{{ route('dealer.login') }}" class="rounded-lg"
                                onclick="event.preventDefault(); document.getElementById('nav-logout-form').submit();">Çıkış yap</a>
                        </li>
                    @endguest
                </ul>
            </div>

            {{-- Mobile menu + Desktop actions --}}
            <div class="navbar-end gap-2">
                @auth('dealer')
                    <span class="hidden sm:inline text-sm text-base-content/70 truncate max-w-[120px] lg:max-w-[180px]" title="{{ auth()->guard('dealer')->user()->contact_name }}">
                        {{ auth()->guard('dealer')->user()->contact_name }}
                    </span>
                @endauth
                <livewire:cart-icon />

                <div class="dropdown dropdown-end lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-square" aria-label="Menüyü aç">
                        @svg('heroicon-o-bars-3', 'h-6 w-6')
                    </div>
                    <ul tabindex="0"
                        class="menu dropdown-content menu-sm dropdown-content mt-3 w-52 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                        <li><a href="{{ url('/') }}">Anasayfa</a></li>
                        @guest('dealer')
                            <li><a href="{{ route('dealer.login') }}">Bayi Girişi</a></li>
                            <li><a href="{{ route('admin.login') }}">Yönetim</a></li>
                        @else
                            <li>
                                <a href="{{ route('dealer.login') }}"
                                    onclick="event.preventDefault(); document.getElementById('nav-logout-form').submit();">Çıkış yap</a>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @auth('dealer')
            <form id="nav-logout-form" method="POST" action="{{ route('dealer.logout') }}" class="hidden">
                @csrf
            </form>
        @endauth
    </header>

    {{-- Flash messages --}}

    @if (session('error'))
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
                    @guest('dealer')
                        <a href="{{ route('dealer.login') }}" class="link-hover link">Bayi Girişi</a>
                        <a href="{{ route('admin.login') }}" class="link-hover link">Yönetim</a>
                    @endguest
                </div>
            </div>
            <div class="mt-6 pt-6 border-t border-base-300 text-center text-sm text-base-content/60">
                &copy; {{ date('Y') }} Sera. Tüm hakları saklıdır.
            </div>
        </div>
    </footer>

    {{-- Global toast (any component can dispatch show-toast) --}}
    <div id="toast-container" class="toast toast-top toast-center z-[100]" style="display: none;">
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
                if (!container || !alert || !message) return;
                const payload = Array.isArray(event) ? event[0] : (event?.detail ?? event);
                const type = payload?.type === 'success' ? 'alert-success' : 'alert-error';
                alert.className = 'alert shadow-lg min-w-[300px] ' + type;
                message.textContent = payload?.message ?? '';
                container.style.display = 'block';
                setTimeout(() => { container.style.display = 'none'; }, 3000);
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>

</html>

<!DOCTYPE html>
<html lang="tr" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Bayi Paneli - Sera')">
    <title>@yield('title', 'Bayi Paneli') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-base-200 antialiased">
    {{-- Top navbar --}}
    <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm">
        <nav class="navbar container mx-auto px-4 lg:px-6" aria-label="Bayi menüsü">
            {{-- Logo --}}
            <div class="navbar-start min-w-0">
                <a href="{{ url('/') }}" class="btn btn-ghost gap-2 px-2 font-semibold">
                    <span class="text-primary">Sera</span>
                    <span class="hidden sm:inline text-base-content/70 text-sm">Bayi</span>
                </a>
            </div>

            {{-- Desktop nav --}}
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal gap-1 px-1 font-medium">
                    <li><a href="{{ url('/') }}" class="rounded-lg">Anasayfa</a></li>
                    <li><a href="#" class="rounded-lg">Katalog</a></li>
                    <li><a href="#" class="rounded-lg">Siparişlerim</a></li>
                    <li><a href="#" class="rounded-lg">Profilim</a></li>
                </ul>
            </div>

            {{-- Actions: Cart + Logout --}}
            <div class="navbar-end gap-2">
                <a href="{{ route('panel') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Panel</a>
                <form method="POST" action="{{ route('dealer.logout') }}" class="hidden sm:inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm gap-2">
                    @svg('heroicon-o-arrow-right-on-rectangle', 'h-4 w-4 shrink-0')
                    Çıkış
                </button>
                </form>
                <a href="#" class="btn btn-ghost btn-square btn-sm relative" aria-label="Sepet">
                    @svg('heroicon-o-shopping-cart', 'h-5 w-5')
                    {{-- Cart badge placeholder --}}
                </a>

                {{-- Mobile menu --}}
                <div class="dropdown dropdown-end lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-square" aria-label="Menüyü aç">
                        @svg('heroicon-o-bars-3', 'h-6 w-6')
                    </div>
                    <ul tabindex="0" class="menu dropdown-content menu-sm dropdown-content mt-3 w-52 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                        <li><a href="{{ url('/') }}">Anasayfa</a></li>
                        <li><a href="{{ route('panel') }}">Panel</a></li>
                        <li><a href="#">Katalog</a></li>
                        <li><a href="#">Siparişlerim</a></li>
                        <li><a href="#">Profilim</a></li>
                        <li><a href="#">Sepet</a></li>
                        <li>
                            <form method="POST" action="{{ route('dealer.logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left py-2 px-4 hover:bg-base-200 rounded-lg flex items-center gap-2">
                                @svg('heroicon-o-arrow-right-on-rectangle', 'h-4 w-4 shrink-0')
                                Çıkış
                            </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="container mx-auto px-4 pt-4">
            <div role="alert" class="alert alert-success shadow-sm">
                @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="container mx-auto px-4 pt-4">
            <div role="alert" class="alert alert-error shadow-sm">
                @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 container mx-auto px-4 lg:px-6 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-base-300 bg-base-100 mt-auto py-4">
        <div class="container mx-auto px-4 text-center text-sm text-base-content/60">
            &copy; {{ date('Y') }} Sera — Bayi Paneli
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

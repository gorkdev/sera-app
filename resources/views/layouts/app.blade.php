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
    {{-- Navbar: wire:ignore ile Livewire DOM değişikliklerinden korunur (tıklanabilirlik için) --}}
    <header wire:ignore class="sticky top-0 z-[100] border-b border-base-300 bg-base-100 transition-none">
        <nav class="navbar container mx-auto px-4 lg:px-6" aria-label="Ana menü">
            {{-- Logo --}}
            <div class="navbar-start min-w-0">
                <a href="{{ route('home') }}" class="btn btn-ghost gap-2 px-2 text-xl font-semibold tracking-tight">
                    <span class="text-primary">Sera</span>
                </a>
            </div>

            {{-- Desktop nav --}}
            <div class="navbar-center hidden lg:flex relative z-10">
                <ul class="menu menu-horizontal gap-1 px-1 font-medium">
                    <li><a href="{{ route('home') }}" class="rounded-lg">Anasayfa</a></li>
                    @guest('dealer')
                        <li><a href="{{ route('dealer.login') }}" class="rounded-lg">Bayi Girişi</a></li>
                        <li><a href="{{ route('admin.login') }}" class="rounded-lg">Yönetim</a></li>
                    @else
                        <li><a href="{{ route('dealer.orders') }}" class="rounded-lg">Siparişler</a></li>
                        <li>
                            <a href="#" class="rounded-lg" onclick="event.preventDefault(); document.getElementById('nav-logout-form').submit();">Çıkış yap</a>
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
                        <li><a href="{{ route('home') }}">Anasayfa</a></li>
                        @guest('dealer')
                            <li><a href="{{ route('dealer.login') }}">Bayi Girişi</a></li>
                            <li><a href="{{ route('admin.login') }}">Yönetim</a></li>
                        @else
                            <li><a href="{{ route('dealer.orders') }}">Siparişler</a></li>
                            <li>
                                <a href="#" class="rounded-lg" onclick="event.preventDefault(); document.getElementById('nav-logout-form').submit();">Çıkış yap</a>
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

    {{-- Ceza uyarısı: sepet süresi dolunca tüm ürünlerin üstünde gösterilir --}}
    @auth('dealer')
        @php $dealer = auth()->guard('dealer')->user(); $dealer->refresh(); @endphp
        @if ($dealer->hasPenalty())
            <div class="container mx-auto px-4 pt-4" role="alert">
                <div class="alert alert-warning shadow-lg flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <p class="font-semibold">Sepet süreniz doldu</p>
                        <p class="text-sm opacity-90">
                            Belirlenen süre içinde satın alımı tamamlamadığınız için rezervasyonlarınız serbest bırakıldı.
                            <strong>{{ $dealer->penaltyRemainingMinutes() }} dakika</strong> alışveriş yapamazsınız.
                        </p>
                    </div>
                    <span class="badge badge-warning badge-lg shrink-0">
                        Kalan: {{ $dealer->penaltyRemainingMinutes() }} dk
                    </span>
                </div>
            </div>
        @endif
    @endauth

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

    {{-- Global toast stack: üst üste, büyükten küçüğe (en yeni en büyük, max 5) --}}
    <style>
        #toast-stack-container { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 100; width: min(90vw, 360px); min-height: 3rem; pointer-events: none; }
        #toast-stack-container .toast-item { position: absolute; left: 0; right: 0; transition: all 0.2s ease; transform-origin: top center; pointer-events: auto; }
        #toast-stack-container .toast-item.toast-enter { animation: toastSlideIn 0.3s ease-out; }
        @keyframes toastSlideIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
    <div id="toast-stack-container"></div>
    <script>
        document.addEventListener('livewire:init', () => {
            const MAX_TOASTS = 5;
            const TOAST_DURATION = 3000;
            const STACK_OFFSET = 10;
            const MIN_SCALE = 0.82;
            const container = document.getElementById('toast-stack-container');
            if (!container) return;

            function updateToastStack() {
                const children = container.querySelectorAll('.toast-item');
                const n = children.length;
                children.forEach((el, i) => {
                    const scale = n <= 1 ? 1 : MIN_SCALE + (1 - MIN_SCALE) * (i / (n - 1));
                    const top = i * STACK_OFFSET;
                    el.style.top = top + 'px';
                    el.style.transform = 'scale(' + scale + ')';
                    el.style.zIndex = i;
                });
            }

            Livewire.on('show-toast', (...args) => {
                const event = args[0];
                let payload = {};
                if (event && typeof event === 'object') {
                    if (event.detail != null) {
                        const d = event.detail;
                        payload = Array.isArray(d) ? (d[0] ?? {}) : (d || {});
                    } else if ('message' in event || 'type' in event) {
                        payload = event;
                    }
                }
                const type = payload?.type === 'success' ? 'alert-success' : 'alert-error';
                const msg = payload?.message || '';
                if (!msg) return;
                const toast = document.createElement('div');
                toast.className = 'toast-item toast-enter alert shadow-lg min-w-[280px] ' + type;
                toast.innerHTML = '<span class="font-medium">' + String(msg).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                container.appendChild(toast);
                while (container.children.length > MAX_TOASTS) container.removeChild(container.firstChild);
                updateToastStack();
                setTimeout(() => {
                    toast.remove();
                    updateToastStack();
                }, TOAST_DURATION);
            });
        });
    </script>

    <script>
        (function() {
            const formatNumberTR = (num) => num.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const lastValues = {};
            window.runCartNumberAnimation = function() {
                document.querySelectorAll('.cart-animate-number').forEach((el) => {
                    const key = el.dataset.key;
                    const target = parseFloat(el.dataset.value || 0) || 0;
                    const start = lastValues[key];
                    lastValues[key] = target;
                    if (start === undefined || start === target) {
                        el.textContent = formatNumberTR(target);
                        return;
                    }
                    const duration = 400;
                    const startTime = performance.now();
                    function step(now) {
                        const elapsed = now - startTime;
                        const t = Math.min(elapsed / duration, 1);
                        const ease = 1 - Math.pow(1 - t, 2);
                        const current = start + (target - start) * ease;
                        el.textContent = formatNumberTR(current);
                        if (t < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                });
            };
        })();
        document.addEventListener('DOMContentLoaded', runCartNumberAnimation);
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ succeed }) => succeed(runCartNumberAnimation));
        });
    </script>
    @livewireScripts
    @stack('scripts')
</body>

</html>

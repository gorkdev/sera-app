<div class="relative"
    @if ($shouldPoll ?? false) wire:poll.{{ isset($timerRemainingSeconds) && $timerRemainingSeconds !== null && !($isExpired ?? false) ? '1s' : '5s' }} @endif>
    {{-- When dealer: poll every 5s so other devices/tabs see cart updates --}}
    {{-- Cart button in navbar --}}
    <button type="button" wire:click="toggle" class="btn btn-ghost btn-circle relative" aria-label="Sepet">
        @svg('heroicon-o-shopping-cart', 'h-6 w-6')
        @if ($totalQuantity > 0)
            <span class="badge badge-primary badge-sm absolute -top-1 -right-1 rounded-full px-1.5">
                {{ $totalQuantity }}
            </span>
        @endif
    </button>

    {{-- Right-side cart drawer --}}
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-base-content/40 backdrop-blur-sm z-40 transition-opacity duration-200 {{ $open ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none' }}"
        wire:click="toggle"></div>

    {{-- Drawer --}}
    <div
        class="fixed inset-y-0 right-0 w-full sm:w-96 bg-base-100 shadow-2xl z-50 flex flex-col transform transition-transform duration-200 {{ $open ? 'translate-x-0' : 'translate-x-full' }}">
        <div class="px-4 py-3 border-b border-base-300 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    @svg('heroicon-o-shopping-bag', 'h-5 w-5')
                    <span>Sepet</span>
                </h2>
                <p class="text-xs text-base-content/60 mt-0.5">
                    {{ $totalQuantity }} ürün ·
                    <span class="cart-animate-number" data-key="cart-subtotal-header"
                        data-value="{{ $subtotal }}">{{ number_format($subtotal, 2, ',', '.') }}</span> ₺
                </p>
                @if (isset($timerRemainingSeconds) && $timerRemainingSeconds !== null && !($isExpired ?? false))
                    <div class="mt-2 flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium {{ $showTimerWarning ?? false ? 'text-warning' : 'text-base-content/70' }}">
                            @svg('heroicon-o-clock', 'h-4 w-4 shrink-0 align-middle')
                            <span>Kalan: <span wire:key="cart-timer-{{ $timerExpiresAtTimestampMs ?? 0 }}"
                                    x-data="{
                                        expiresAt: {{ $timerExpiresAtTimestampMs ?? 0 }},
                                        sec: 0,
                                        startTimer() {
                                            const update = () => {
                                                this.sec = Math.max(0, Math.floor((this.expiresAt - Date.now()) / 1000));
                                            };
                                            update();
                                            setInterval(update.bind(this), 1000);
                                        }
                                    }" x-init="startTimer()"
                                    x-text="Math.floor(sec/60) + ':' + String(Math.floor(sec%60)).padStart(2,'0')"></span></span>
                        </span>
                        @if ($canExtend ?? false)
                            <button type="button" wire:click="extendTimer"
                                class="btn btn-ghost btn-xs h-6 min-h-0 gap-1 text-primary">
                                +5 dk uzat
                            </button>
                        @endif
                    </div>
                @elseif ($isExpired ?? false)
                    <p class="text-xs text-error mt-1">Sepet süresi doldu.</p>
                @endif
            </div>
            <button type="button" wire:click="toggle" class="btn btn-ghost btn-sm btn-circle" aria-label="Kapat">
                @svg('heroicon-o-x-mark', 'h-5 w-5')
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-3">
            @if (empty($cartItems))
                <div class="h-full flex flex-col items-center justify-center text-base-content/60">
                    @svg('heroicon-o-shopping-bag', 'h-12 w-12 mb-3 opacity-40')
                    <p class="text-sm font-medium">Sepetiniz boş</p>
                    <p class="text-xs mt-1">Ürünleri sepete ekleyerek burada görebilirsiniz.</p>
                </div>
            @else
                <ul class="space-y-3">
                    @foreach ($cartItems as $item)
                        <li class="flex gap-3 items-center border-b border-base-300/60 pb-3 last:border-0 last:pb-0">
                            <div
                                class="w-14 h-14 rounded-md bg-base-200 flex items-center justify-center overflow-hidden shrink-0">
                                @if (!empty($item['image']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($item['image']) }}"
                                        alt="{{ $item['name'] }}" class="w-full h-full object-cover" />
                                @else
                                    @svg('heroicon-o-photo', 'h-6 w-6 text-base-content/30')
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">
                                    {{ $item['name'] }}
                                </p>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    {{-- Miktar azalt-artır --}}
                                    <div class="relative cart-qty-group inline-flex items-center">
                                        <div class="join join-xs border border-base-300 rounded-md overflow-hidden inline-flex items-stretch"
                                            wire:key="qty-{{ $item['product_id'] }}-{{ (int) $item['quantity'] }}">
                                            <button type="button"
                                                wire:click="decrementOrConfirmRemove({{ $item['product_id'] }}, {{ (int) $item['quantity'] }})"
                                                class="join-item btn btn-ghost btn-xs px-2 h-7 min-h-0 flex items-center justify-center">
                                                -
                                            </button>
                                            <input type="number" min="1" step="1"
                                                value="{{ (int) $item['quantity'] }}"
                                                class="join-item input input-xs w-12 h-7 min-h-0 text-center text-xs font-medium px-1 focus:outline-none focus:ring-0 border-0 no-spinners"
                                                wire:change="setItemQuantityFromInput({{ $item['product_id'] }}, $event.target.value)" />
                                            <button type="button"
                                                wire:click="incrementItem({{ $item['product_id'] }})"
                                                class="join-item btn btn-ghost btn-xs px-2 h-7 min-h-0 flex items-center justify-center">
                                                +
                                            </button>
                                        </div>

                                    </div>
                                    <p class="text-xs text-base-content/60">
                                        × <span class="cart-animate-number"
                                            data-key="cart-item-{{ $item['product_id'] }}-price"
                                            data-value="{{ (float) $item['price'] }}">{{ number_format($item['price'], 2, ',', '.') }}</span>
                                        ₺
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1">
                                <p class="text-sm font-semibold">
                                    <span class="cart-animate-number"
                                        data-key="cart-item-{{ $item['product_id'] }}-total"
                                        data-value="{{ (float) $item['quantity'] * (float) $item['price'] }}">{{ number_format($item['quantity'] * $item['price'], 2, ',', '.') }}</span>
                                    ₺
                                </p>
                                <button type="button" wire:click="openRemoveConfirm({{ $item['product_id'] }})"
                                    class="btn btn-ghost btn-xs text-error gap-1" title="Sepetten çıkar">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                    <span>Kaldır</span>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="border-t border-base-300 px-4 py-3 bg-base-100">
            <div class="flex items-center justify-between mb-3">
                <div class="flex flex-col">
                    <span class="text-xs text-base-content/60">Toplam</span>
                    <span class="text-lg font-semibold">
                        <span class="cart-animate-number" data-key="cart-subtotal-footer"
                            data-value="{{ $subtotal }}">{{ number_format($subtotal, 2, ',', '.') }}</span> ₺
                    </span>
                </div>
                <button type="button" wire:click="openClearConfirm" class="btn btn-ghost btn-xs gap-1 text-base-content/70"
                    @disabled(empty($cartItems))>
                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                    <span>Sepeti Temizle</span>
                </button>
            </div>
            <button type="button" class="btn btn-primary btn-block" wire:click="openCheckoutModal"
                @disabled(empty($cartItems) || !($canCheckout ?? true)) title="{{ !($canCheckout ?? true) ? 'Stokta olmayan ürün var' : '' }}">
                Satın Al
            </button>
        </div>
    </div>

    @if ($showClearConfirm ?? false)
        <div class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="absolute inset-0 bg-base-content/50 backdrop-blur-sm" wire:click="cancelClearConfirm"></div>
            <div
                class="relative bg-base-100 rounded-lg shadow-2xl border border-base-300 w-full max-w-md mx-4 p-5 z-[61]">
                <h3 class="text-lg font-semibold mb-2">Sepeti temizle</h3>
                <p class="text-sm text-base-content/70 mb-4">
                    Sepetteki tüm ürünleri kaldırmak ve rezervasyonları serbest bırakmak istediğinize emin misiniz?
                </p>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelClearConfirm">
                        İptal
                    </button>
                    <button type="button" class="btn btn-error btn-sm" wire:click="confirmClearCart">
                        Sepeti Temizle
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($confirmRemoveProductId ?? null)
        <div class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="absolute inset-0 bg-base-content/50 backdrop-blur-sm" wire:click="cancelRemoveConfirm"></div>
            <div
                class="relative bg-base-100 rounded-lg shadow-2xl border border-base-300 w-full max-w-md mx-4 p-5 z-[61]">
                <h3 class="text-lg font-semibold mb-2">Ürünü sepetten çıkar</h3>
                <p class="text-sm text-base-content/70 mb-4">
                    <strong>{{ $confirmRemoveProductName ?? '' }}</strong> ürününü sepetten tamamen çıkarmak istediğinize emin misiniz? Rezervasyon kaldırılacak ve stok serbest kalacaktır.
                </p>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelRemoveConfirm">
                        İptal
                    </button>
                    <button type="button" class="btn btn-error btn-sm" wire:click="confirmRemoveItem">
                        Sepetten Çıkar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showCheckoutModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="absolute inset-0 bg-base-content/50 backdrop-blur-sm" wire:click="closeCheckoutModal"></div>
            <div
                class="relative bg-base-100 rounded-lg shadow-2xl border border-base-300 w-full max-w-md mx-4 p-5 z-[61]">
                <h3 class="text-lg font-semibold mb-2">Satın Almayı Onayla</h3>
                <p class="text-sm text-base-content/70 mb-4">
                    Sepetinizdeki ürünleri satın almak üzeresiniz.
                </p>
                <div class="mb-4 text-sm">
                    <div class="flex justify-between mb-1">
                        <span class="text-base-content/60">Ürün sayısı</span>
                        <span class="font-medium">{{ $totalQuantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Toplam tutar</span>
                        <span class="font-semibold"><span class="cart-animate-number" data-key="cart-subtotal-modal"
                                data-value="{{ $subtotal }}">{{ number_format($subtotal, 2, ',', '.') }}</span>
                            ₺</span>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="closeCheckoutModal">
                        Vazgeç
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="confirmCheckout">
                        Onayla ve Satın Al
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

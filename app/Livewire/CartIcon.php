<?php

namespace App\Livewire;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CartIcon extends Component
{
    public bool $open = false;
    public bool $showCheckoutModal = false;

    public function mount(): void
    {
        //
    }

    /** When another component dispatches cart-updated (e.g. add to cart), re-render so badge and drawer show fresh data. */
    #[On('cart-updated')]
    public function onCartUpdated(): void
    {
        // No-op: Livewire re-renders the component after this, so render() returns fresh cart data.
    }

    private function dealer(): ?Authenticatable
    {
        return Auth::guard('dealer')->user();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function removeItem(int $productId, CartService $cartService): void
    {
        $dealer = $this->dealer();
        if ($dealer) {
            $cartService->removeItem($dealer, $productId);
        } else {
            $cart = session('cart', []);
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                session()->put('cart', $cart);
            }
        }
    }

    public function clear(CartService $cartService): void
    {
        $dealer = $this->dealer();
        if ($dealer) {
            $cartService->clear($dealer);
        } else {
            session()->forget('cart');
        }
    }

    public function incrementItem(int $productId, CartService $cartService): void
    {
        $dealer = $this->dealer();
        if ($dealer) {
            try {
                $cartService->incrementItem($dealer, $productId);
            } catch (\InvalidArgumentException $e) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            $cart = session('cart', []);
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = (int) ($cart[$productId]['quantity'] ?? 0) + 1;
                session()->put('cart', $cart);
            }
        }
    }

    public function decrementItem(int $productId, CartService $cartService): void
    {
        $dealer = $this->dealer();
        if ($dealer) {
            $cartService->decrementItem($dealer, $productId);
        } else {
            $cart = session('cart', []);
            if (! isset($cart[$productId])) {
                return;
            }
            $current = (int) ($cart[$productId]['quantity'] ?? 0);
            if ($current <= 1) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $current - 1;
            }
            if (empty($cart)) {
                session()->forget('cart');
            } else {
                session()->put('cart', $cart);
            }
        }
    }

    public function extendTimer(CartService $cartService): void
    {
        $dealer = $this->dealer();
        if (! $dealer) {
            return;
        }

        if ($cartService->extendTimer($dealer)) {
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Sepet süresi 5 dakika uzatıldı.',
            ]);
        } else {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Süre uzatılamadı. Zaten kullanılmış veya süre dolmuş olabilir.',
            ]);
        }
    }

    public function changeItemQuantity(int $productId, int $delta, CartService $cartService): void
    {
        if ($delta === 0) {
            return;
        }
        $dealer = $this->dealer();
        if ($dealer) {
            try {
                $cartService->updateQuantity($dealer, $productId, $delta);
            } catch (\InvalidArgumentException $e) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            $cart = session('cart', []);
            if (! isset($cart[$productId])) {
                return;
            }
            $current = (int) ($cart[$productId]['quantity'] ?? 0);
            $new = $current + $delta;
            if ($new <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $new;
            }
            if (empty($cart)) {
                session()->forget('cart');
            } else {
                session()->put('cart', $cart);
            }
        }
    }

    /**
     * Inputa yazılan mutlak adet değerine göre sepetteki miktarı günceller.
     * Stoktan fazlaysa toast ile uyarı verir.
     */
    public function setItemQuantityFromInput(int $productId, $value, CartService $cartService): void
    {
        $qty = (int) preg_replace('/\D/', '', (string) $value);
        $dealer = $this->dealer();
        if (! $dealer) {
            return;
        }
        if ($qty < 1) {
            $cartService->removeItem($dealer, $productId);
            return;
        }
        try {
            $cartService->setItemQuantity($dealer, $productId, $qty);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openCheckoutModal(CartService $cartService): void
    {
        $dealer = $this->dealer();
        $cartModel = $dealer ? $cartService->getCartWithItems($dealer) : null;
        $cart = $dealer ? $cartService->getItemsForDisplay($dealer) : session('cart', []);

        if (empty($cart)) {
            return;
        }

        if ($cartModel && $cartModel->timer_expires_at && now()->gte($cartModel->timer_expires_at)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Sepet süresi dolmuş. Lütfen sepete tekrar ürün ekleyin.',
            ]);
            return;
        }

        if (! $dealer) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Satın almak için önce bayi girişi yapmalısınız.',
            ]);
            return;
        }

        foreach ($cart as $item) {
            if (! ($item['in_stock'] ?? true)) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Sepette stokta olmayan ürün var. Rezerve edilemez.',
                ]);
                return;
            }
        }

        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
    }

    public function confirmCheckout(CartService $cartService, CheckoutService $checkoutService): void
    {
        $dealer = $this->dealer();
        if (! $dealer) {
            $this->showCheckoutModal = false;
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Satın almak için önce bayi girişi yapmalısınız.',
            ]);
            return;
        }

        $cart = $cartService->getCartWithItems($dealer);
        if (! $cart || $cart->items->isEmpty()) {
            $this->showCheckoutModal = false;
            return;
        }

        $itemsForDisplay = $cartService->getItemsForDisplay($dealer);
        foreach ($itemsForDisplay as $item) {
            if (! ($item['in_stock'] ?? true)) {
                $this->showCheckoutModal = false;
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Sepette stokta olmayan ürün var. Rezerve edilemez.',
                ]);
                return;
            }
        }

        try {
            $order = $checkoutService->checkout($cart, 'pickup', null);
            $this->open = false;
            $this->showCheckoutModal = false;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Siparişiniz oluşturuldu. Sipariş no: ' . $order->order_number,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => $e->getMessage() ?: 'Sipariş oluşturulurken bir hata oluştu.',
            ]);
        }
    }

    public function render(CartService $cartService)
    {
        $dealer = $this->dealer();
        $cartModel = $dealer ? $cartService->getCartWithItems($dealer) : null;
        $cart = $cartModel ? $cartService->getItemsForDisplay($dealer) : session('cart', []);

        $total = 0;
        $subtotal = 0;
        $canCheckout = true;
        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $total += $qty;
            $subtotal += $qty * $price;
            if ($dealer && ! ($item['in_stock'] ?? true)) {
                $canCheckout = false;
            }
        }

        $timerExpiresAt = null;
        $timerExpiresAtTimestampMs = null;
        $timerRemainingSeconds = null;
        $canExtend = false;
        $isExpired = false;
        $showTimerWarning = false;

        if ($cartModel && $cartModel->timer_expires_at) {
            $timerExpiresAt = $cartModel->timer_expires_at;
            $timerExpiresAtTimestampMs = $cartModel->timer_expires_at->getTimestamp() * 1000;
            $remaining = $cartModel->timer_expires_at->diffInSeconds(now(), false);
            $timerRemainingSeconds = (int) max(0, (int) -$remaining);
            $isExpired = $remaining >= 0;
            $canExtend = $cartModel->canExtend();
            $warningMinutes = config('sera.cart.warning_before_minutes', 5);
            $showTimerWarning = ! $isExpired && $timerRemainingSeconds <= ($warningMinutes * 60);
        }

        return view('components.⚡cart-icon', [
            'cartItems' => $cart,
            'totalQuantity' => $total,
            'subtotal' => $subtotal,
            'canCheckout' => $canCheckout,
            'shouldPoll' => (bool) $dealer,
            'cartModel' => $cartModel,
            'timerExpiresAt' => $timerExpiresAt,
            'timerExpiresAtTimestampMs' => $timerExpiresAtTimestampMs,
            'timerRemainingSeconds' => $timerRemainingSeconds,
            'canExtend' => $canExtend,
            'isExpired' => $isExpired,
            'showTimerWarning' => $showTimerWarning,
        ]);
    }
}

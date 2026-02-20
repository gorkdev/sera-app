<?php

namespace App\Livewire;

use App\Services\CartService;
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
            $cartService->incrementItem($dealer, $productId);
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

    public function changeItemQuantity(int $productId, int $delta, CartService $cartService): void
    {
        if ($delta === 0) {
            return;
        }
        $dealer = $this->dealer();
        if ($dealer) {
            $cartService->updateQuantity($dealer, $productId, $delta);
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

    public function openCheckoutModal(CartService $cartService): void
    {
        $dealer = $this->dealer();
        $cart = $dealer
            ? $cartService->getItemsForDisplay($dealer)
            : session('cart', []);

        if (empty($cart)) {
            return;
        }

        if (! $dealer) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Satın almak için önce bayi girişi yapmalısınız.',
            ]);
            return;
        }

        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
    }

    public function confirmCheckout(CartService $cartService): void
    {
        $dealer = $this->dealer();
        $cart = $dealer
            ? $cartService->getItemsForDisplay($dealer)
            : session('cart', []);

        if (empty($cart)) {
            $this->showCheckoutModal = false;
            return;
        }

        if (! $dealer) {
            $this->showCheckoutModal = false;
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Satın almak için önce bayi girişi yapmalısınız.',
            ]);
            return;
        }

        $cartService->clear($dealer);
        $this->open = false;
        $this->showCheckoutModal = false;

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Sepetiniz ön rezerve edildi. Satın alımınız alınmıştır.',
        ]);
    }

    public function render(CartService $cartService)
    {
        $dealer = $this->dealer();
        $cart = $dealer
            ? $cartService->getItemsForDisplay($dealer)
            : session('cart', []);

        $total = 0;
        $subtotal = 0;
        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $total += $qty;
            $subtotal += $qty * $price;
        }

        return view('components.⚡cart-icon', [
            'cartItems' => $cart,
            'totalQuantity' => $total,
            'subtotal' => $subtotal,
            'shouldPoll' => (bool) $dealer,
        ]);
    }
}

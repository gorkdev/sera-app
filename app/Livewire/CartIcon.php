<?php

namespace App\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartIcon extends Component
{
    public int $count = 0;
    public bool $open = false;
    public bool $showCheckoutModal = false;

    protected $listeners = [
        'cart-updated' => 'onCartUpdated',
    ];

    public function mount(): void
    {
        $this->syncCount();
    }

    private function syncCount(): void
    {
        $cart = session('cart', []);
        $this->count = (int) array_sum(Arr::pluck($cart, 'quantity'));
    }

    public function onCartUpdated(?int $totalQuantity = null): void
    {
        if ($totalQuantity !== null) {
            $this->count = $totalQuantity;
        } else {
            $this->syncCount();
        }
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function removeItem(int $productId): void
    {
        $cart = session('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }
        $this->syncCount();
    }

    public function clear(): void
    {
        session()->forget('cart');
        $this->count = 0;
    }

    public function incrementItem(int $productId): void
    {
        $cart = session('cart', []);
        if (! isset($cart[$productId])) {
            return;
        }

        $cart[$productId]['quantity'] = (int) ($cart[$productId]['quantity'] ?? 0) + 1;
        session()->put('cart', $cart);
        $this->syncCount();
    }

    public function decrementItem(int $productId): void
    {
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

        $this->syncCount();
    }

    public function changeItemQuantity(int $productId, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

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

        $this->syncCount();
    }

    public function openCheckoutModal(): void
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return;
        }

        if (! Auth::guard('dealer')->check()) {
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

    public function confirmCheckout(): void
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            $this->showCheckoutModal = false;

            return;
        }

        if (! Auth::guard('dealer')->check()) {
            $this->showCheckoutModal = false;

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Satın almak için önce bayi girişi yapmalısınız.',
            ]);

            return;
        }

        session()->forget('cart');
        $this->count = 0;
        $this->open = false;
        $this->showCheckoutModal = false;

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Sepetiniz ön rezerve edildi. Satın alımınız alınmıştır.',
        ]);
    }

    public function render()
    {
        $cart = session('cart', []);
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
        ]);
    }
}


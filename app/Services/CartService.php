<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Dealer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create cart for dealer. Single query (firstOrCreate).
     */
    public function getOrCreateCart(Dealer $dealer): Cart
    {
        return Cart::firstOrCreate(
            ['dealer_id' => $dealer->id],
            ['dealer_id' => $dealer->id]
        );
    }

    /**
     * Load cart with items and products in one query for display.
     */
    public function getCartWithItems(Dealer $dealer): ?Cart
    {
        return Cart::where('dealer_id', $dealer->id)
            ->with(['items.product:id,name,image,price'])
            ->first();
    }

    /**
     * Return cart items in the same shape as session cart: [product_id => [product_id, name, price, image, quantity]].
     */
    public function getItemsForDisplay(Dealer $dealer): array
    {
        $cart = $this->getCartWithItems($dealer);
        if (! $cart || $cart->items->isEmpty()) {
            return [];
        }

        $out = [];
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }
            $out[$item->product_id] = [
                'product_id' => $item->product_id,
                'name' => $product->name,
                'price' => (float) $item->unit_price,
                'image' => $product->image,
                'quantity' => $item->quantity,
            ];
        }

        return $out;
    }

    /**
     * Total item count for the dealer's cart (single query).
     */
    public function getTotalQuantity(Dealer $dealer): int
    {
        return (int) CartItem::query()
            ->join('carts', 'carts.id', '=', 'cart_items.cart_id')
            ->where('carts.dealer_id', $dealer->id)
            ->sum('cart_items.quantity');
    }

    /**
     * Add or update quantity for a product.
     */
    public function addItem(Dealer $dealer, int $productId, int $quantity = 1): void
    {
        $product = Product::where('id', $productId)->where('is_active', true)->first();
        if (! $product) {
            return;
        }

        DB::transaction(function () use ($dealer, $product, $quantity) {
            $cart = $this->getOrCreateCart($dealer);
            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);
            if ($item->exists) {
                $item->increment('quantity', $quantity);
                $item->update(['unit_price' => $product->price]);
            } else {
                $item->quantity = $quantity;
                $item->unit_price = $product->price;
                $item->save();
            }
        });
    }

    public function removeItem(Dealer $dealer, int $productId): void
    {
        $cart = Cart::where('dealer_id', $dealer->id)->first();
        if (! $cart) {
            return;
        }
        $cart->items()->where('product_id', $productId)->delete();
    }

    public function updateQuantity(Dealer $dealer, int $productId, int $delta): void
    {
        $cart = Cart::where('dealer_id', $dealer->id)->first();
        if (! $cart) {
            return;
        }
        $item = $cart->items()->where('product_id', $productId)->first();
        if (! $item) {
            return;
        }
        $newQty = $item->quantity + $delta;
        if ($newQty <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $newQty]);
        }
    }

    public function setItemQuantity(Dealer $dealer, int $productId, int $quantity): void
    {
        $cart = Cart::where('dealer_id', $dealer->id)->first();
        if (! $cart) {
            return;
        }
        $item = $cart->items()->where('product_id', $productId)->first();
        if (! $item) {
            return;
        }
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }
    }

    public function incrementItem(Dealer $dealer, int $productId): void
    {
        $this->updateQuantity($dealer, $productId, 1);
    }

    public function decrementItem(Dealer $dealer, int $productId): void
    {
        $this->updateQuantity($dealer, $productId, -1);
    }

    public function clear(Dealer $dealer): void
    {
        $cart = Cart::where('dealer_id', $dealer->id)->first();
        if ($cart) {
            $cart->items()->delete();
        }
    }

    /**
     * Merge session cart into dealer cart (e.g. after login), then forget session cart.
     */
    public function mergeSessionCartIntoDealerCart(Dealer $dealer): void
    {
        $sessionCart = session('cart', []);
        if (empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $productId => $row) {
            $qty = (int) ($row['quantity'] ?? 0);
            if ($qty > 0) {
                $this->addItem($dealer, (int) $productId, $qty);
            }
        }

        session()->forget('cart');
    }
}

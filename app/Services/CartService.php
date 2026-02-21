<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Dealer;
use App\Models\Party;
use App\Models\PartyStock;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Aktif partiyi getir (status=active, arrived_at dolu).
     */
    public function getActiveParty(): ?Party
    {
        return Party::where('status', 'active')
            ->whereNotNull('arrived_at')
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Bayi için parti bazlı sepet bul veya oluştur.
     */
    public function getOrCreateCart(Dealer $dealer, ?Party $party = null): Cart
    {
        $party = $party ?? $this->getActiveParty();
        if (!$party) {
            throw new \InvalidArgumentException('Aktif parti bulunamadı. Sepete ürün ekleyemezsiniz.');
        }

        $cart = Cart::where('dealer_id', $dealer->id)
            ->where('party_id', $party->id)
            ->where('status', Cart::STATUS_ACTIVE)
            ->first();

        if ($cart) {
            return $cart;
        }

        $timerMinutes = config('sera.cart.timer_duration_minutes', 30);
        $timerExpires = now()->addMinutes($timerMinutes);

        return Cart::create([
            'dealer_id' => $dealer->id,
            'party_id' => $party->id,
            'status' => Cart::STATUS_ACTIVE,
            'timer_started_at' => now(),
            'timer_expires_at' => $timerExpires,
            'extension_used' => false,
        ]);
    }

    /**
     * Bayi için aktif sepeti getir. Süresi dolmuşsa expire edilir.
     */
    public function getActiveCart(Dealer $dealer): ?Cart
    {
        $party = $this->getActiveParty();
        $query = Cart::where('dealer_id', $dealer->id)
            ->where('status', Cart::STATUS_ACTIVE)
            ->with(['items.product:id,name,image,price,sku', 'items.partyStock']);

        if ($party) {
            $cart = (clone $query)->where('party_id', $party->id)->first();
            if ($cart) {
                return $this->expireIfOverdue($cart);
            }
        }

        $cart = $query->orderByDesc('updated_at')->first();

        return $this->expireIfOverdue($cart);
    }

    /**
     * Load cart with items for display.
     */
    public function getCartWithItems(Dealer $dealer): ?Cart
    {
        return $this->getActiveCart($dealer);
    }

    /**
     * Return cart items in display format.
     */
    public function getItemsForDisplay(Dealer $dealer): array
    {
        $cart = $this->getCartWithItems($dealer);
        if (!$cart || $cart->items->isEmpty()) {
            return [];
        }

        $productIds = $cart->items->pluck('product_id')->all();
        $available = Product::getAvailableStockForProductIds($productIds);

        $out = [];
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }
            $qty = $item->quantity;
            $freeStock = $available[$item->product_id] ?? 0;
            // Sepetteki miktar zaten rezerve; “erişilebilir” = serbest stok + bizim rezervasyonumuz
            $effectiveAvailable = $freeStock + $item->quantity;
            $out[$item->product_id] = [
                'product_id' => $item->product_id,
                'name' => $product->name,
                'price' => (float) $item->unit_price,
                'image' => $product->image,
                'quantity' => $qty,
                'available' => $effectiveAvailable,
                'in_stock' => $qty <= $effectiveAvailable,
            ];
        }

        return $out;
    }

    /**
     * Total item count for dealer's active cart.
     */
    public function getTotalQuantity(Dealer $dealer): int
    {
        $cart = $this->getActiveCart($dealer);
        if (!$cart) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    /**
     * Sepete ürün ekle (party_stock ile rezervasyon).
     */
    public function addItem(Dealer $dealer, int $productId, int $quantity = 1): void
    {
        $product = Product::where('id', $productId)->where('is_active', true)->first();
        if (!$product) {
            throw new \InvalidArgumentException('Ürün bulunamadı veya satışta değil.');
        }

        $party = $this->getActiveParty();
        if (!$party) {
            throw new \InvalidArgumentException('Aktif parti bulunamadı. Sepete ürün ekleyemezsiniz.');
        }

        $partyStock = PartyStock::where('party_id', $party->id)
            ->where('product_id', $product->id)
            ->first();

        if (!$partyStock) {
            throw new \InvalidArgumentException('Bu ürün için stok bulunamadı.');
        }

        $available = $partyStock->total_quantity
            - $partyStock->reserved_quantity
            - $partyStock->sold_quantity
            - ($partyStock->waste_quantity ?? 0);

        if ($dealer->hasPenalty()) {
            throw new \InvalidArgumentException('Süre dolduğu için geçici olarak alışveriş yapamazsınız.');
        }

        DB::transaction(function () use ($dealer, $product, $partyStock, $quantity, $available) {
            $cart = $this->getOrCreateCart($dealer, $partyStock->party);
            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);

            $currentQty = $item->exists ? $item->quantity : 0;
            $newQty = $currentQty + $quantity;

            // Rezerve eden kişi: kendi rezervasyonu + serbest stok kadar alabilir (başkası sadece serbest stok kadar)
            $maxWeCanHave = $available + $currentQty;
            if ($newQty > $maxWeCanHave) {
                throw new \InvalidArgumentException("Yetersiz stok. En fazla {$maxWeCanHave} adet alabilirsiniz.");
            }

            foreach ($item->reservations()->where('status', 'reserved')->get() as $reservation) {
                $this->stockService->releaseReservation($reservation);
            }

            $item->party_stock_id = $partyStock->id;
            $item->quantity = $newQty;
            $item->unit_price = $product->price;
            $item->save();

            $this->stockService->reserveStock($partyStock, $cart, $item, $newQty);
        });
    }

    /**
     * Sepetten ürün çıkar (rezervasyonları serbest bırak).
     */
    public function removeItem(Dealer $dealer, int $productId): void
    {
        $cart = $this->getActiveCart($dealer);
        if (!$cart) {
            return;
        }

        $item = $cart->items()->where('product_id', $productId)->first();
        if (!$item) {
            return;
        }

        DB::transaction(function () use ($item) {
            foreach ($item->reservations()->where('status', 'reserved')->get() as $reservation) {
                $this->stockService->releaseReservation($reservation);
            }
            $item->delete();
        });
    }

    /**
     * Miktar güncelle (delta: +1 veya -1).
     */
    public function updateQuantity(Dealer $dealer, int $productId, int $delta): void
    {
        $cart = $this->getActiveCart($dealer);
        if (!$cart) {
            return;
        }

        $item = $cart->items()->where('product_id', $productId)->first();
        if (!$item) {
            return;
        }

        $newQty = $item->quantity + $delta;
        if ($newQty <= 0) {
            $this->removeItem($dealer, $productId);
            return;
        }

        $partyStock = $item->partyStock ?? PartyStock::where('party_id', $cart->party_id)
            ->where('product_id', $productId)
            ->first();

        if (!$partyStock) {
            return;
        }

        $available = $partyStock->total_quantity
            - $partyStock->reserved_quantity
            - $partyStock->sold_quantity
            - ($partyStock->waste_quantity ?? 0);

        // Serbest stok + sepetteki mevcut miktar (önce serbest bırakıyoruz) = alabileceğimiz max
        $maxWeCanHave = $available + $item->quantity;
        if ($newQty > $maxWeCanHave) {
            throw new \InvalidArgumentException("Yetersiz stok. En fazla {$maxWeCanHave} adet alabilirsiniz.");
        }

        DB::transaction(function () use ($item, $partyStock, $newQty, $cart) {
            foreach ($item->reservations()->where('status', 'reserved')->get() as $reservation) {
                $this->stockService->releaseReservation($reservation);
            }
            $item->update([
                'quantity' => $newQty,
                'party_stock_id' => $partyStock->id,
            ]);
            $this->stockService->reserveStock($partyStock, $cart, $item, $newQty);
        });
    }

    public function incrementItem(Dealer $dealer, int $productId): void
    {
        $this->updateQuantity($dealer, $productId, 1);
    }

    public function decrementItem(Dealer $dealer, int $productId): void
    {
        $this->updateQuantity($dealer, $productId, -1);
    }

    public function setItemQuantity(Dealer $dealer, int $productId, int $quantity): void
    {
        $cart = $this->getActiveCart($dealer);
        if (!$cart) {
            return;
        }

        $item = $cart->items()->where('product_id', $productId)->first();
        if (!$item) {
            return;
        }

        $this->updateQuantity($dealer, $productId, $quantity - $item->quantity);
    }

    /**
     * Süresi dolmuş sepeti expire et: rezervasyonları serbest bırak, ceza uygula.
     */
    public function expireCart(Cart $cart): void
    {
        if ($cart->status !== Cart::STATUS_ACTIVE) {
            return;
        }

        DB::transaction(function () use ($cart) {
            $this->stockService->releaseCartReservations($cart);
            $cart->update(['status' => Cart::STATUS_EXPIRED]);

            $penaltyMinutes = config('sera.cart.penalty_duration_minutes', 10);
            $cart->dealer->update(['penalty_until' => now()->addMinutes($penaltyMinutes)]);
        });
    }

    /**
     * Sepet süresi dolmuşsa expire et.
     */
    public function expireIfOverdue(?Cart $cart): ?Cart
    {
        if (! $cart || $cart->status !== Cart::STATUS_ACTIVE || ! $cart->timer_expires_at) {
            return $cart;
        }

        if (now()->lt($cart->timer_expires_at)) {
            return $cart;
        }

        $this->expireCart($cart);

        return null;
    }

    /**
     * Sepet süresini uzat (+5 dk, tek seferlik).
     */
    public function extendTimer(Dealer $dealer): bool
    {
        $cart = $this->getActiveCart($dealer);
        if (! $cart || ! $cart->canExtend()) {
            return false;
        }

        $extensionMinutes = config('sera.cart.extension_minutes', 5);
        $cart->update([
            'timer_expires_at' => $cart->timer_expires_at->addMinutes($extensionMinutes),
            'extension_used' => true,
        ]);

        return true;
    }

    /**
     * Sepeti temizle (rezervasyonları serbest bırak).
     */
    public function clear(Dealer $dealer): void
    {
        $cart = $this->getActiveCart($dealer);
        if (!$cart) {
            return;
        }

        DB::transaction(function () use ($cart) {
            $this->stockService->releaseCartReservations($cart);
            $cart->items()->delete();
        });
    }

    /**
     * Session sepetini bayi sepetine merge et (login sonrası).
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
                try {
                    $this->addItem($dealer, (int) $productId, $qty);
                } catch (\Throwable) {
                    // Skip if party/stock unavailable
                }
            }
        }

        session()->forget('cart');
    }
}

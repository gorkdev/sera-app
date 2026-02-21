<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PartyStock;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Stok rezerve et (sepete eklenince).
     */
    public function reserveStock(PartyStock $partyStock, Cart $cart, CartItem $cartItem, int $quantity): StockReservation
    {
        return DB::transaction(function () use ($partyStock, $cart, $cartItem, $quantity) {
            // Serbest bırakma sonrası güncel reserved değerini al (release sonrası çağrılıyor olabilir)
            $partyStock->refresh();
            $available = $partyStock->total_quantity
                - $partyStock->reserved_quantity
                - $partyStock->sold_quantity
                - ($partyStock->waste_quantity ?? 0);

            if ($quantity > $available) {
                throw new \InvalidArgumentException("Yetersiz stok. Mevcut: {$available}, İstenen: {$quantity}");
            }

            $partyStock->increment('reserved_quantity', $quantity);

            return StockReservation::create([
                'cart_id' => $cart->id,
                'cart_item_id' => $cartItem->id,
                'party_stock_id' => $partyStock->id,
                'quantity' => $quantity,
                'status' => StockReservation::STATUS_RESERVED,
            ]);
        });
    }

    /**
     * Rezervasyonu onayla (satışa dönüştür).
     */
    public function confirmReservation(StockReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            if ($reservation->status !== StockReservation::STATUS_RESERVED) {
                return;
            }

            $partyStock = $reservation->partyStock;
            $partyStock->decrement('reserved_quantity', $reservation->quantity);
            $partyStock->increment('sold_quantity', $reservation->quantity);

            $reservation->update(['status' => StockReservation::STATUS_CONFIRMED]);
        });
    }

    /**
     * Rezervasyonu serbest bırak.
     */
    public function releaseReservation(StockReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            if ($reservation->status !== StockReservation::STATUS_RESERVED) {
                return;
            }

            $reservation->partyStock->decrement('reserved_quantity', $reservation->quantity);
            $reservation->update(['status' => StockReservation::STATUS_RELEASED]);
        });
    }

    /**
     * Sepetteki tüm rezervasyonları serbest bırak.
     */
    public function releaseCartReservations(Cart $cart): void
    {
        $reservations = $cart->reservations()->where('status', StockReservation::STATUS_RESERVED)->get();

        foreach ($reservations as $reservation) {
            $this->releaseReservation($reservation);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Sepetten sipariş oluştur.
     */
    public function checkout(Cart $cart, string $deliveryType = 'pickup', ?string $dealerNote = null): Order
    {
        return DB::transaction(function () use ($cart, $deliveryType, $dealerNote) {
            if (!$cart->isActive()) {
                throw new \InvalidArgumentException('Sepet aktif değil veya süresi dolmuş.');
            }

            if ($cart->timer_expires_at && now()->gte($cart->timer_expires_at)) {
                throw new \InvalidArgumentException('Sepet süresi dolmuş. Lütfen sepete tekrar ürün ekleyin.');
            }

            if ($cart->party_id === null) {
                throw new \InvalidArgumentException('Sepet parti bilgisi eksik.');
            }

            if ($cart->items->isEmpty()) {
                throw new \InvalidArgumentException('Sepet boş.');
            }

            $orderNumber = $this->generateOrderNumber();

            $subtotal = 0.0;
            foreach ($cart->items as $item) {
                $lineTotal = (float) ($item->quantity * $item->unit_price);
                $subtotal += $lineTotal;
            }

            $vatRate = (float) config('sera.tax.vat_rate', 20);
            $taxAmount = round($subtotal * $vatRate / 100, 2);
            $total = $subtotal + $taxAmount;

            $orderStatus = OrderStatus::where('slug', 'kesinlesti')->first()
                ?? OrderStatus::where('is_default', true)->first()
                ?? OrderStatus::orderBy('sort_order')->first();

            $order = Order::create([
                'order_number' => $orderNumber,
                'dealer_id' => $cart->dealer_id,
                'party_id' => $cart->party_id,
                'cart_id' => $cart->id,
                'order_status_id' => $orderStatus?->id,
                'delivery_type' => $deliveryType,
                'subtotal' => $subtotal,
                'tax_rate' => $vatRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'total_amount' => $total,
                'dealer_note' => $dealerNote,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $unitPrice = (float) $item->unit_price;
                $quantity = (int) $item->quantity;
                $lineTotal = $unitPrice * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'party_stock_id' => $item->party_stock_id,
                    'product_id' => $item->product_id,
                    'product_name' => $product?->name,
                    'product_sku' => $product?->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);

                foreach ($item->reservations()->where('status', StockReservation::STATUS_RESERVED)->get() as $reservation) {
                    $this->stockService->confirmReservation($reservation);
                }
            }

            $cart->update(['status' => Cart::STATUS_COMPLETED]);

            // Stok biten partiyi otomatik kapat (close_when_stock_runs_out)
            $party = $order->party;
            if ($party && $party->isActive() && $party->close_when_stock_runs_out && !$party->hasAvailableStock()) {
                $party->markClosed();
            }

            return $order;
        });
    }

    protected function generateOrderNumber(): string
    {
        $prefix = config('sera.order.number_prefix', 'SIP');
        $yearMonth = now()->format('Ym');

        $sequence = DB::table('order_number_sequences')
            ->where('year_month', $yearMonth)
            ->lockForUpdate()
            ->first();

        if ($sequence) {
            $next = $sequence->last_number + 1;
            DB::table('order_number_sequences')
                ->where('year_month', $yearMonth)
                ->update(['last_number' => $next]);
        } else {
            $next = 1;
            DB::table('order_number_sequences')->insert([
                'year_month' => $yearMonth,
                'last_number' => $next,
            ]);
        }

        return sprintf('%s-%s-%05d', $prefix, $yearMonth, $next);
    }
}

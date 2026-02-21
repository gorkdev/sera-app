<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Console\Command;

class ExpireCartsCommand extends Command
{
    protected $signature = 'carts:expire';

    protected $description = 'Süresi dolmuş sepetleri expire eder (rezervasyon serbest, ceza uygula)';

    public function handle(CartService $cartService): int
    {
        $expired = Cart::where('status', Cart::STATUS_ACTIVE)
            ->whereNotNull('timer_expires_at')
            ->where('timer_expires_at', '<=', now())
            ->get();

        foreach ($expired as $cart) {
            $cartService->expireCart($cart);
            $this->info("Sepet #{$cart->id} (dealer: {$cart->dealer_id}) süresi doldu, expire edildi.");
        }

        if ($expired->isEmpty()) {
            $this->info('Süresi dolmuş sepet yok.');
        } else {
            $this->info($expired->count() . ' sepet expire edildi.');
        }

        return self::SUCCESS;
    }
}

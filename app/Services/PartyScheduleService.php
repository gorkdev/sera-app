<?php

namespace App\Services;

use App\Models\Party;
use Illuminate\Support\Facades\DB;

class PartyScheduleService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Parti başlangıç/bitiş tarihlerine göre otomatik aktivasyon ve kapatma.
     * Bitişte kapanan partiye ait aktif sepetler rezervasyonları serbest bırakılarak sonlandırılır (ceza uygulanmaz).
     */
    public function processSchedules(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {
            // Başlangıç tarihi gelmiş taslak partileri aktif et (aynı anda tek parti: önce diğerini kapat)
            $toActivate = Party::where('status', 'draft')
                ->whereNotNull('starts_at')
                ->where('starts_at', '<=', $now)
                ->orderBy('starts_at')
                ->get();

            foreach ($toActivate as $party) {
                $otherActive = Party::where('status', 'active')->where('id', '!=', $party->id)->first();
                if ($otherActive) {
                    $this->closePartyAndExpireCarts($otherActive);
                }
                $update = [
                    'status' => 'active',
                    'activated_at' => $party->activated_at ?? $now,
                ];
                // Stok ve sepette görünmesi için arrived_at gerekli; yoksa başlangıç anında set et
                if ($party->arrived_at === null) {
                    $update['arrived_at'] = $now;
                }
                $party->update($update);
            }

            // Zamanlayıcıyla aktif olmuş ama arrived_at boş kalan partileri düzelt (stok/sepette görünsün)
            Party::where('status', 'active')
                ->whereNull('arrived_at')
                ->each(fn (Party $p) => $p->update(['arrived_at' => $p->activated_at ?? $now]));

            // Bitiş tarihi gelmiş aktif partileri kapat
            $toCloseByDate = Party::where('status', 'active')
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', $now)
                ->get();

            foreach ($toCloseByDate as $party) {
                $this->closePartyAndExpireCarts($party);
            }

            // Stok biten aktif partileri kapat (close_when_stock_runs_out)
            $activeWithStockCheck = Party::where('status', 'active')
                ->where('close_when_stock_runs_out', true)
                ->get();

            foreach ($activeWithStockCheck as $party) {
                if (! $party->hasAvailableStock()) {
                    $this->closePartyAndExpireCarts($party);
                }
            }
        });
    }

    /**
     * Partiyi kapat ve bu partiye ait aktif sepetleri sonlandır (ceza uygulanmaz).
     */
    protected function closePartyAndExpireCarts(Party $party): void
    {
        $party->markClosed();
        $this->cartService->expireCartsForParty($party);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartyStock extends Model
{
    protected $fillable = [
        'party_id',
        'product_id',
        'location',
        'cost_price_override',
        'price_override',
        'total_quantity',
        'reserved_quantity',
        'sold_quantity',
        'waste_quantity',
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'sold_quantity' => 'integer',
        'waste_quantity' => 'integer',
        'freshness_score' => 'decimal:2',
        'cost_price_override' => 'decimal:2',
        'price_override' => 'decimal:2',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Mevcut stok miktarı (total - reserved - sold - waste)
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->total_quantity - $this->reserved_quantity - $this->sold_quantity - $this->waste_quantity);
    }

    /**
     * Stok mevcut mu?
     */
    public function isAvailable(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Net stok (brüt - zayiat)
     */
    public function getNetQuantityAttribute(): int
    {
        return max(0, $this->total_quantity - $this->waste_quantity);
    }

    /**
     * Tazelik skorunu hesapla (0-100 arası)
     * Parti geliş tarihinden itibaren geçen gün sayısına göre
     */
    public function calculateFreshnessScore(): ?float
    {
        if (!$this->party->arrived_at) {
            return null;
        }

        $daysSinceArrival = now()->diffInDays($this->party->arrived_at);
        
        // Ürünün raf ömrü (shelf_life_days) varsa ona göre hesapla
        $shelfLife = $this->product->shelf_life_days ?? 7; // Varsayılan 7 gün
        
        if ($daysSinceArrival >= $shelfLife) {
            return 0;
        }
        
        // Tazelik skoru: (1 - geçen_gün / raf_ömrü) * 100
        $score = (1 - ($daysSinceArrival / $shelfLife)) * 100;
        
        return max(0, min(100, round($score, 2)));
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }
}

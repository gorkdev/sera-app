<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'party_code',
        'name',
        'description',
        'supplier_name',
        'truck_plate',
        'driver_name',
        'driver_contact',
        'emergency_contact',
        'truck_status',
        'departure_at',
        'estimated_arrival_at',
        'journey_days',
        'purchase_price_per_unit',
        'logistics_cost',
        'customs_cost',
        'currency',
        'status',
        'activated_at',
        'arrived_at',
        'florist_delivery_at',
        'starts_at',
        'ends_at',
        'close_when_stock_runs_out',
        'visible_to_all',
        'closed_at',
        'created_by',
        'closed_by',
    ];

    protected $casts = [
        'journey_days' => 'integer',
        'purchase_price_per_unit' => 'decimal:2',
        'logistics_cost' => 'decimal:2',
        'customs_cost' => 'decimal:2',
        'activated_at' => 'datetime',
        'arrived_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'close_when_stock_runs_out' => 'boolean',
        'visible_to_all' => 'boolean',
        'departure_at' => 'datetime',
        'estimated_arrival_at' => 'datetime',
        'florist_delivery_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function closedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'closed_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Partide satılabilir (mevcut) stok var mı?
     * Mevcut = total - reserved - sold - waste
     */
    public function hasAvailableStock(): bool
    {
        $total = $this->stocks()
            ->get()
            ->sum(fn (\App\Models\PartyStock $ps) => max(0, $ps->total_quantity - $ps->reserved_quantity - $ps->sold_quantity - ($ps->waste_quantity ?? 0)));

        return $total > 0;
    }

    /**
     * Partiyi kapat (closed_at, closed_by set eder).
     */
    public function markClosed(?int $closedBy = null): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $closedBy ?? auth('admin')?->id(),
        ]);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(PartyStock::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function dealerGroups(): BelongsToMany
    {
        return $this->belongsToMany(DealerGroup::class, 'party_dealer_group')
            ->withPivot('delay_minutes')
            ->withTimestamps();
    }

    /**
     * Net maliyet hesapla (birim başına)
     */
    public function getNetCostPerUnitAttribute(): ?float
    {
        if (!$this->purchase_price_per_unit) {
            return null;
        }
        
        // Toplam stok adedi ile maliyetleri böl
        $totalStock = $this->stocks()->sum('total_quantity');
        if ($totalStock == 0) {
            return $this->purchase_price_per_unit;
        }
        
        $totalCost = $this->purchase_price_per_unit * $totalStock 
            + ($this->logistics_cost ?? 0) 
            + ($this->customs_cost ?? 0);
        
        return $totalCost / $totalStock;
    }

    /**
     * Parti kodu otomatik oluştur.
     * Silinmiş (soft-deleted) partiler de sayılır; böylece aynı kodu tekrar üretmeyiz.
     */
    protected static function booted(): void
    {
        static::creating(function (Party $party) {
            if (empty($party->party_code)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $lastParty = static::withTrashed()
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderByDesc('id')
                    ->first();

                $sequence = $lastParty ? (int) substr($lastParty->party_code ?? '', -4) + 1 : 1;
                $party->party_code = sprintf('NL-TR-%s-%s-%04d', $year, $month, $sequence);
            }
        });
    }
}

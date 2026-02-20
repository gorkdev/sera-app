<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'journey_days',
        'purchase_price_per_unit',
        'logistics_cost',
        'customs_cost',
        'currency',
        'status',
        'activated_at',
        'arrived_at',
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

    public function stocks(): HasMany
    {
        return $this->hasMany(PartyStock::class);
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
     * Parti kodu otomatik oluştur
     */
    protected static function booted(): void
    {
        static::creating(function (Party $party) {
            if (empty($party->party_code)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $lastParty = static::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderByDesc('id')
                    ->first();
                
                $sequence = $lastParty ? (int) substr($lastParty->party_code ?? '', -4) + 1 : 1;
                $party->party_code = sprintf('NL-TR-%s-%s-%04d', $year, $month, $sequence);
            }
        });
    }
}

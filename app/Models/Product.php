<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'image',
        'gallery_images',
        'price',
        'cost_price',
        'unit',
        'unit_conversions',
        'stock_quantity',
        'critical_stock_type',
        'critical_stock_value',
        'critical_stock_reference',
        'featured_badges',
        'origin',
        'shelf_life_days',
        'min_order_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'gallery_images' => 'array',
        'featured_badges' => 'array',
        'unit_conversions' => 'array',
    ];

    /** Stok kritik seviyeye ulaştı mı? */
    public function isCriticalStock(): bool
    {
        if (! $this->critical_stock_type || $this->critical_stock_value === null) {
            return false;
        }
        if ($this->critical_stock_type === 'percent') {
            $ref = $this->critical_stock_reference ?: 100;
            $threshold = (int) ceil($ref * $this->critical_stock_value / 100);
            return $this->stock_quantity <= $threshold;
        }
        return $this->stock_quantity <= $this->critical_stock_value;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function partyStocks(): HasMany
    {
        return $this->hasMany(PartyStock::class);
    }

    /**
     * Aktif partilerdeki mevcut stok miktarı (total - reserved - sold - waste)
     */
    public function getAvailableStock(): int
    {
        return (int) (static::getAvailableStockForProductIds([$this->id])[$this->id] ?? 0);
    }

    /**
     * Birden fazla ürün için mevcut stok miktarlarını tek sorguda döner [product_id => available]
     */
    public static function getAvailableStockForProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $rows = \Illuminate\Support\Facades\DB::table('party_stocks as ps')
            ->join('parties as p', 'p.id', '=', 'ps.party_id')
            ->whereIn('ps.product_id', $productIds)
            ->where('p.status', 'active')
            ->whereNull('p.deleted_at')
            ->whereNotNull('p.arrived_at')
            ->groupBy('ps.product_id')
            ->selectRaw('ps.product_id, COALESCE(SUM(ps.total_quantity - ps.reserved_quantity - ps.sold_quantity - COALESCE(ps.waste_quantity, 0)), 0) as available')
            ->get();

        $result = array_fill_keys($productIds, 0);
        foreach ($rows as $row) {
            $result[(int) $row->product_id] = (int) $row->available;
        }

        return $result;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', '.') . ' ₺';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && ! $product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}

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

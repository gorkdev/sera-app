<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'season_start_month',
        'season_end_month',
        'inactive_outside_season',
        'visible_to_group_ids',
        'max_quantity_per_dealer_per_party',
        'display_priority',
        'attribute_set',
        'region_restriction',
        'default_growth_days',
        'ideal_temp_min', 'ideal_temp_max',
        'ideal_humidity_min', 'ideal_humidity_max',
        'required_documents',
        'min_order_quantity', 'profit_margin_percent',
        'color_code', 'icon', 'featured_badges',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'inactive_outside_season' => 'boolean',
        'visible_to_group_ids' => 'array',
        'attribute_set' => 'array',
        'region_restriction' => 'array',
        'required_documents' => 'array',
        'featured_badges' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('name');
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true)->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isInSeason(?int $month = null): bool
    {
        if (! $this->season_start_month || ! $this->season_end_month) {
            return true;
        }
        $month ??= (int) now()->format('n');
        if ($this->season_start_month <= $this->season_end_month) {
            return $month >= $this->season_start_month && $month <= $this->season_end_month;
        }
        return $month >= $this->season_start_month || $month <= $this->season_end_month;
    }

    /** Katalogda görünür mü? (is_active + sezon dışı pasif kontrolü) */
    public function isEffectivelyActive(?int $month = null): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->inactive_outside_season && ! $this->isInSeason($month)) {
            return false;
        }
        return true;
    }

    public function isVisibleToDealerGroup(?int $groupId): bool
    {
        if (empty($this->visible_to_group_ids)) {
            return true;
        }
        return in_array($groupId, $this->visible_to_group_ids);
    }

    public function getSeasonLabelAttribute(): ?string
    {
        if (! $this->season_start_month || ! $this->season_end_month) {
            return null;
        }
        $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        return ($months[$this->season_start_month] ?? $this->season_start_month) . '–' . ($months[$this->season_end_month] ?? $this->season_end_month);
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (Category $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}

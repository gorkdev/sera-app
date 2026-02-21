<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'dealer_id',
        'party_id',
        'status',
        'timer_started_at',
        'timer_expires_at',
        'extension_used',
    ];

    protected $casts = [
        'timer_started_at' => 'datetime',
        'timer_expires_at' => 'datetime',
        'extension_used' => 'boolean',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isTimerRunning(): bool
    {
        return $this->timer_expires_at && now()->lt($this->timer_expires_at);
    }

    public function canExtend(): bool
    {
        return $this->isActive() && !$this->extension_used && $this->timer_expires_at && now()->lt($this->timer_expires_at);
    }
}

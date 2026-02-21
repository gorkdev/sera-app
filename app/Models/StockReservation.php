<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $fillable = [
        'cart_id',
        'cart_item_id',
        'party_stock_id',
        'quantity',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public const STATUS_RESERVED = 'reserved';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RELEASED = 'released';

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(CartItem::class);
    }

    public function partyStock(): BelongsTo
    {
        return $this->belongsTo(PartyStock::class);
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }
}

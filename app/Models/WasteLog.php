<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteLog extends Model
{
    protected $fillable = [
        'party_stock_id',
        'waste_type',
        'quantity',
        'waste_date',
        'days_since_party_arrival',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'waste_date' => 'datetime',
        'quantity' => 'integer',
        'days_since_party_arrival' => 'integer',
    ];

    public function partyStock(): BelongsTo
    {
        return $this->belongsTo(PartyStock::class);
    }

    public function recordedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'recorded_by');
    }

    public function getWasteTypeLabelAttribute(): string
    {
        return [
            'pest' => 'Böceklenme',
            'fungus' => 'Mantar',
            'dehydration' => 'Susuzluk',
            'breakage' => 'Kırılma',
            'expired' => 'Raf Ömrü Sonu',
        ][$this->waste_type] ?? $this->waste_type;
    }
}

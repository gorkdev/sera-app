<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealerGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
        'delay_minutes',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function dealers(): HasMany
    {
        return $this->hasMany(Dealer::class);
    }
}

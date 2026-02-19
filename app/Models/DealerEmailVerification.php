<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerEmailVerification extends Model
{
    protected $table = 'dealer_email_verifications';

    protected $fillable = [
        'dealer_id',
        'code_hash',
        'expires_at',
        'last_sent_at',
        'send_count',
        'attempts',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}


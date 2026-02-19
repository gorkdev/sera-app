<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Dealer extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'dealers';

    protected $fillable = [
        'dealer_group_id',
        'company_name',
        'contact_name',
        'email',
        'password',
        'phone',
        'tax_office',
        'tax_number',
        'tax_type',
        'city',
        'district',
        'address',
        'kvkk_consent',
        'status',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DealerGroup::class, 'dealer_group_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'kvkk_consent' => 'boolean',
        'email_verified_at' => 'datetime',
    ];
}

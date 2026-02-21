<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'penalty_until',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DealerGroup::class, 'dealer_group_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class)->where('status', Cart::STATUS_ACTIVE)->latest();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'kvkk_consent' => 'boolean',
        'email_verified_at' => 'datetime',
        'penalty_until' => 'datetime',
    ];

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function hasPenalty(): bool
    {
        return $this->penalty_until && now()->lt($this->penalty_until);
    }
}

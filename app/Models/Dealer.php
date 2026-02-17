<?php
// Dealer.php
namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; // Veri silinmesin, işaretlensin

class Dealer extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'dealers';

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'password',
        'phone',
        'status', // pending, active, passive
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

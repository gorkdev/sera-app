<?php
// AdminUser.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_users';

    // Bu alanlara kod tarafında veri girişi yapılabilir diyoruz
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // super_admin veya admin
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

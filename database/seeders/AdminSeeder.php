<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // İlk profesyonel kural: Şifreyi Hash'leyerek (şifreleyerek) kaydetmeliyiz.
        AdminUser::create([
            'name'      => 'Sera Yönetici',
            'email'     => 'admin@sera.com',
            'password'  => Hash::make('123456'), // Gerçek hayatta daha zor olmalı!
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Dealer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DealerSeeder extends Seeder
{
    public function run(): void
    {
        Dealer::create([
            'company_name' => 'Test Çiçekçi',
            'contact_name' => 'Test Yetkili',
            'email' => 'bayi@test.com',
            'password' => Hash::make('123456'),
            'phone' => '05551234567',
            'status' => 'active',
        ]);
    }
}

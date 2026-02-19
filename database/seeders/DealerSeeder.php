<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\DealerGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DealerSeeder extends Seeder
{
    public function run(): void
    {
        $defaultGroup = DealerGroup::where('is_default', true)->first();

        Dealer::create([
            'dealer_group_id' => $defaultGroup?->id,
            'company_name' => 'Test Çiçekçi',
            'contact_name' => 'Test Yetkili',
            'email' => 'bayi@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123456'),
            'phone' => '05551234567',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'status' => 'active',
        ]);
    }
}

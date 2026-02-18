<?php

namespace Database\Seeders;

use App\Models\DealerGroup;
use Illuminate\Database\Seeder;

class DealerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'VIP', 'code' => 'vip', 'delay_minutes' => 0, 'is_default' => false, 'sort_order' => 1],
            ['name' => 'Standart', 'code' => 'standart', 'delay_minutes' => 15, 'is_default' => true, 'sort_order' => 2],
            ['name' => 'Yeni', 'code' => 'yeni', 'delay_minutes' => 30, 'is_default' => false, 'sort_order' => 3],
        ];

        foreach ($groups as $group) {
            DealerGroup::updateOrCreate(
                ['code' => $group['code']],
                $group
            );
        }
    }
}

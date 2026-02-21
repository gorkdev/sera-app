<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Beklemede', 'slug' => 'pending', 'color' => 'warning', 'is_default' => true, 'is_system' => true, 'sort_order' => 1],
            ['name' => 'Onaylandı', 'slug' => 'confirmed', 'color' => 'info', 'is_default' => false, 'is_system' => true, 'sort_order' => 2],
            ['name' => 'Hazırlanıyor', 'slug' => 'preparing', 'color' => 'primary', 'is_default' => false, 'is_system' => true, 'sort_order' => 3],
            ['name' => 'Gönderildi', 'slug' => 'shipped', 'color' => 'info', 'is_default' => false, 'is_system' => true, 'sort_order' => 4],
            ['name' => 'Teslim Edildi', 'slug' => 'delivered', 'color' => 'success', 'is_default' => false, 'is_system' => true, 'sort_order' => 5],
            ['name' => 'İptal', 'slug' => 'cancelled', 'color' => 'error', 'is_default' => false, 'is_system' => true, 'sort_order' => 99],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}

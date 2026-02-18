<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kesme Çiçekler', 'slug' => 'kesme-cicekler', 'description' => 'Vazoda kullanıma uygun kesme çiçekler', 'sort_order' => 1],
            ['name' => 'Saksı Bitkileri', 'slug' => 'saksi-bitkileri', 'description' => 'Saksılı süs bitkileri', 'sort_order' => 2],
            ['name' => 'Yeşil Yapraklılar', 'slug' => 'yesil-yapraklilar', 'description' => 'Yaprak dökmeyen süs bitkileri', 'sort_order' => 3],
            ['name' => 'Buket & Aranjman', 'slug' => 'buket-aranjman', 'description' => 'Hazır buket ve aranjmanlar', 'sort_order' => 4],
        ];

        foreach ($categories as $data) {
            Category::create(array_merge($data, ['is_active' => true]));
        }
    }
}

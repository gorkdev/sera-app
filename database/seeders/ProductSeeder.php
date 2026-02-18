<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $kesme = Category::where('slug', 'kesme-cicekler')->first();
        $saksi = Category::where('slug', 'saksi-bitkileri')->first();
        $buket = Category::where('slug', 'buket-aranjman')->first();

        if (! $kesme) {
            return;
        }

        $products = [
            [
                'category_id' => $kesme->id,
                'name' => 'Kırmızı Gül (Ecuador)',
                'slug' => 'kirmizi-gul-ecuador',
                'sku' => 'GR-EC-001',
                'description' => 'Ecuador menşeli, 60-70 cm boyunda birinci sınıf kırmızı gül. Demet halinde.',
                'price' => 45.00,
                'unit' => 'demet',
                'stock_quantity' => 500,
                'min_order_quantity' => 10,
            ],
            [
                'category_id' => $kesme->id,
                'name' => 'Beyaz Lale',
                'slug' => 'beyaz-lale',
                'sku' => 'TL-WH-001',
                'description' => 'Hollanda lalesi, 50 cm. Demet (10 adet).',
                'price' => 35.00,
                'unit' => 'demet',
                'stock_quantity' => 300,
                'min_order_quantity' => 5,
            ],
            [
                'category_id' => $kesme->id,
                'name' => 'Pembe Karanfil',
                'slug' => 'pembe-karanfil',
                'sku' => 'CR-PK-001',
                'description' => 'Yerli pembe karanfil, 50 cm. Demet (20 adet).',
                'price' => 28.00,
                'unit' => 'demet',
                'stock_quantity' => 800,
                'min_order_quantity' => 10,
            ],
            [
                'category_id' => $kesme->id,
                'name' => 'Sarı Papatya',
                'slug' => 'sari-papatya',
                'sku' => 'CH-YL-001',
                'description' => 'Gerbera papatya, 40 cm. Demet (10 adet).',
                'price' => 22.00,
                'unit' => 'demet',
                'stock_quantity' => 400,
                'min_order_quantity' => 5,
            ],
        ];

        if ($saksi) {
            $products[] = [
                'category_id' => $saksi->id,
                'name' => 'Orkide (Phalaenopsis)',
                'slug' => 'orkide-phalaenopsis',
                'sku' => 'OR-PH-001',
                'description' => '2 dal beyaz orkide, saksılı. 60 cm boy.',
                'price' => 180.00,
                'unit' => 'adet',
                'stock_quantity' => 50,
                'min_order_quantity' => 1,
            ];
        }

        if ($buket) {
            $products[] = [
                'category_id' => $buket->id,
                'name' => 'Doğum Günü Buketi',
                'slug' => 'dogum-gunu-buketi',
                'sku' => 'BK-BD-001',
                'description' => 'Karışık çiçeklerden oluşan hazır doğum günü buketi.',
                'price' => 120.00,
                'unit' => 'buket',
                'stock_quantity' => 20,
                'min_order_quantity' => 1,
            ];
        }

        foreach ($products as $data) {
            Product::create(array_merge($data, ['is_active' => true]));
        }
    }
}

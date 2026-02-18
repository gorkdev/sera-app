<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('gallery_images')->nullable()->after('image');
            $table->string('critical_stock_type', 20)->nullable()->after('stock_quantity'); // 'percent' | 'quantity'
            $table->unsignedInteger('critical_stock_value')->nullable()->after('critical_stock_type');
            $table->unsignedInteger('critical_stock_reference')->nullable()->after('critical_stock_value'); // yüzde için referans (örn. 100)
            $table->json('featured_badges')->nullable()->after('critical_stock_value');
            $table->string('origin')->nullable()->after('featured_badges');
            $table->unsignedSmallInteger('shelf_life_days')->nullable()->after('origin');
            $table->decimal('cost_price', 12, 2)->nullable()->after('price');
            $table->json('unit_conversions')->nullable()->after('unit'); // [{"unit":"demet","adet":25}, ...]
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'gallery_images',
                'critical_stock_type',
                'critical_stock_value',
                'critical_stock_reference',
                'featured_badges',
                'origin',
                'shelf_life_days',
                'cost_price',
                'unit_conversions',
            ]);
        });
    }
};

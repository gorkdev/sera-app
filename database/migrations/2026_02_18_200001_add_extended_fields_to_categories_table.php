<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
            $table->unsignedTinyInteger('season_start_month')->nullable()->after('image'); // 1-12
            $table->unsignedTinyInteger('season_end_month')->nullable()->after('season_start_month'); // 1-12
            $table->json('visible_to_group_ids')->nullable()->after('season_end_month'); // [1,2,3] veya null=tümü
            $table->unsignedInteger('max_quantity_per_dealer_per_party')->nullable()->after('visible_to_group_ids'); // parti başına kota
            $table->integer('display_priority')->default(0)->after('max_quantity_per_dealer_per_party'); // katalog sıralama
            $table->json('attribute_set')->nullable()->after('display_priority'); // {"required":["expiry_date"],"visible":["litre"]}
            $table->json('region_restriction')->nullable()->after('attribute_set'); // {"cities":["izmir"],"regions":["ege"]}
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'image',
                'season_start_month',
                'season_end_month',
                'visible_to_group_ids',
                'max_quantity_per_dealer_per_party',
                'display_priority',
                'attribute_set',
                'region_restriction',
            ]);
        });
    }
};

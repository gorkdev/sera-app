<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'season_start_month',
                'season_end_month',
                'inactive_outside_season',
                'visible_to_group_ids',
                'max_quantity_per_dealer_per_party',
                'display_priority',
                'attribute_set',
                'region_restriction',
                'default_growth_days',
                'ideal_temp_min',
                'ideal_temp_max',
                'ideal_humidity_min',
                'ideal_humidity_max',
                'required_documents',
                'min_order_quantity',
                'profit_margin_percent',
                'color_code',
                'icon',
                'featured_badges',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('season_start_month')->nullable()->after('image');
            $table->unsignedTinyInteger('season_end_month')->nullable()->after('season_start_month');
            $table->boolean('inactive_outside_season')->default(false)->after('season_end_month');
            $table->json('visible_to_group_ids')->nullable()->after('inactive_outside_season');
            $table->unsignedInteger('max_quantity_per_dealer_per_party')->nullable()->after('visible_to_group_ids');
            $table->integer('display_priority')->default(0)->after('max_quantity_per_dealer_per_party');
            $table->json('attribute_set')->nullable()->after('display_priority');
            $table->json('region_restriction')->nullable()->after('attribute_set');
            $table->unsignedSmallInteger('default_growth_days')->nullable()->after('region_restriction');
            $table->decimal('ideal_temp_min', 5, 2)->nullable()->after('default_growth_days');
            $table->decimal('ideal_temp_max', 5, 2)->nullable()->after('ideal_temp_min');
            $table->decimal('ideal_humidity_min', 5, 2)->nullable()->after('ideal_temp_max');
            $table->decimal('ideal_humidity_max', 5, 2)->nullable()->after('ideal_humidity_min');
            $table->json('required_documents')->nullable()->after('ideal_humidity_max');
            $table->unsignedInteger('min_order_quantity')->nullable()->after('required_documents');
            $table->decimal('profit_margin_percent', 5, 2)->nullable()->after('min_order_quantity');
            $table->string('color_code', 20)->nullable()->after('profit_margin_percent');
            $table->string('icon', 50)->nullable()->after('color_code');
            $table->json('featured_badges')->nullable()->after('icon');
        });
    }
};

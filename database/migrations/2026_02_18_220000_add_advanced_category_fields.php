<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Hasat ve dikim
            $table->unsignedSmallInteger('default_growth_days')->nullable()->after('region_restriction');
            // İdeal iklim
            $table->decimal('ideal_temp_min', 5, 2)->nullable()->after('default_growth_days');
            $table->decimal('ideal_temp_max', 5, 2)->nullable()->after('ideal_temp_min');
            $table->decimal('ideal_humidity_min', 5, 2)->nullable()->after('ideal_temp_max');
            $table->decimal('ideal_humidity_max', 5, 2)->nullable()->after('ideal_humidity_min');
            // Zorunlu belgeler
            $table->json('required_documents')->nullable()->after('ideal_humidity_max');
            // Fiyatlandırma
            $table->unsignedInteger('min_order_quantity')->nullable()->after('required_documents');
            $table->decimal('profit_margin_percent', 5, 2)->nullable()->after('min_order_quantity');
            // Görsel
            $table->string('color_code', 20)->nullable()->after('profit_margin_percent');
            $table->string('icon', 50)->nullable()->after('color_code');
            // SEO / Pazarlama
            $table->json('featured_badges')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'default_growth_days',
                'ideal_temp_min', 'ideal_temp_max',
                'ideal_humidity_min', 'ideal_humidity_max',
                'required_documents',
                'min_order_quantity', 'profit_margin_percent',
                'color_code', 'icon', 'featured_badges',
            ]);
        });
    }
};

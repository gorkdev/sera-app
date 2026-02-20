<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('party_code')->unique()->nullable()->after('id');
            $table->string('supplier_name')->nullable()->after('description');
            $table->string('truck_plate')->nullable()->after('supplier_name');
            $table->unsignedSmallInteger('journey_days')->nullable()->after('truck_plate');
            $table->decimal('purchase_price_per_unit', 12, 2)->nullable()->after('journey_days');
            $table->decimal('logistics_cost', 12, 2)->nullable()->after('purchase_price_per_unit');
            $table->decimal('customs_cost', 12, 2)->nullable()->after('logistics_cost');
            $table->string('currency', 3)->default('EUR')->after('customs_cost');
            $table->timestamp('arrived_at')->nullable()->after('activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn([
                'party_code',
                'supplier_name',
                'truck_plate',
                'journey_days',
                'purchase_price_per_unit',
                'logistics_cost',
                'customs_cost',
                'currency',
                'arrived_at',
            ]);
        });
    }
};

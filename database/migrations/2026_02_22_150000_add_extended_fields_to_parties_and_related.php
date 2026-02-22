<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('truck_plate');
            $table->string('driver_contact')->nullable()->after('driver_name');
            $table->string('emergency_contact')->nullable()->after('driver_contact');
            $table->timestamp('departure_at')->nullable()->after('emergency_contact');
            $table->boolean('visible_to_all')->default(true)->after('close_when_stock_runs_out');
        });

        Schema::create('party_dealer_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dealer_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('delay_minutes')->nullable();
            $table->timestamps();
            $table->unique(['party_id', 'dealer_group_id']);
        });

        Schema::table('party_stocks', function (Blueprint $table) {
            $table->decimal('cost_price_override', 12, 2)->nullable()->after('location');
            $table->decimal('price_override', 12, 2)->nullable()->after('cost_price_override');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('activation_delay_minutes')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'driver_contact', 'emergency_contact', 'departure_at', 'visible_to_all']);
        });
        Schema::dropIfExists('party_dealer_group');
        Schema::table('party_stocks', function (Blueprint $table) {
            $table->dropColumn(['cost_price_override', 'price_override']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('activation_delay_minutes');
        });
    }
};

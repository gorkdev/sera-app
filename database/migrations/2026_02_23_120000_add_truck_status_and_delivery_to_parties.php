<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('truck_status', 20)->nullable()->after('emergency_contact'); // not_departed, on_road, arrived
            $table->timestamp('estimated_arrival_at')->nullable()->after('departure_at');
            $table->date('florist_delivery_at')->nullable()->after('arrived_at');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['truck_status', 'estimated_arrival_at', 'florist_delivery_at']);
        });
    }
};

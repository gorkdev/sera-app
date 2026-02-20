<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_stocks', function (Blueprint $table) {
            $table->string('location')->nullable()->after('product_id');
            $table->decimal('freshness_score', 5, 2)->nullable()->after('location'); // 0-100 arası yüzde
        });
    }

    public function down(): void
    {
        Schema::table('party_stocks', function (Blueprint $table) {
            $table->dropColumn(['location', 'freshness_score']);
        });
    }
};

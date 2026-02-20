<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_stocks', function (Blueprint $table) {
            $table->unsignedInteger('waste_quantity')->default(0)->after('sold_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('party_stocks', function (Blueprint $table) {
            $table->dropColumn('waste_quantity');
        });
    }
};

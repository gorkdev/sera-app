<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->timestamp('florist_delivery_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->date('florist_delivery_at')->nullable()->change();
        });
    }
};

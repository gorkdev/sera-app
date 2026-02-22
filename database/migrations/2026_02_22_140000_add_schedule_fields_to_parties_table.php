<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('arrived_at');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->boolean('close_when_stock_runs_out')->default(false)->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at', 'close_when_stock_runs_out']);
        });
    }
};

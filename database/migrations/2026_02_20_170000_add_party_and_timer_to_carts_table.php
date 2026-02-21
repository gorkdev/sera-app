<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('party_id')->nullable()->after('dealer_id')->constrained()->nullOnDelete();
            $table->string('status', 20)->default('active')->after('party_id');
            $table->timestamp('timer_started_at')->nullable()->after('status');
            $table->timestamp('timer_expires_at')->nullable()->after('timer_started_at');
            $table->boolean('extension_used')->default(false)->after('timer_expires_at');
        });

        // Tek aktif parti: dealer+party bazlı tek aktif sepet (unique kısıtı veri migrasyonu sonrası eklenebilir)
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['party_id', 'status', 'timer_started_at', 'timer_expires_at', 'extension_used']);
        });
    }
};

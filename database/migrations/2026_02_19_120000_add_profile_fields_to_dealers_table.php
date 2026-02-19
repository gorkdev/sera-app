<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            // Bu migration bazı ortamlarda kısmen uygulanmış olabilir.
            // Kolon zaten varsa tekrar eklemeye çalışma.
            if (! Schema::hasColumn('dealers', 'tax_office')) {
                $table->string('tax_office')->nullable();
            }
            if (! Schema::hasColumn('dealers', 'tax_number')) {
                $table->string('tax_number')->nullable();
            }
            if (! Schema::hasColumn('dealers', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('dealers', 'district')) {
                $table->string('district')->nullable();
            }
            if (! Schema::hasColumn('dealers', 'address')) {
                $table->text('address')->nullable();
            }
            if (! Schema::hasColumn('dealers', 'kvkk_consent')) {
                $table->boolean('kvkk_consent')->default(false);
            }
        });
    }

    public function down(): void
    {
        $drop = [];
        foreach (['tax_office', 'tax_number', 'city', 'district', 'address', 'kvkk_consent'] as $col) {
            if (Schema::hasColumn('dealers', $col)) {
                $drop[] = $col;
            }
        }

        if ($drop !== []) {
            Schema::table('dealers', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
};


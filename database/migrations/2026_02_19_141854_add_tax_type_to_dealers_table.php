<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            if (! Schema::hasColumn('dealers', 'tax_type')) {
                $table->string('tax_type', 10)->default('tax')->after('tax_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            if (Schema::hasColumn('dealers', 'tax_type')) {
                $table->dropColumn('tax_type');
            }
        });
    }
};

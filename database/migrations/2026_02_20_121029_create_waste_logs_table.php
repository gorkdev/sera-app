<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_stock_id')->constrained()->cascadeOnDelete();
            $table->enum('waste_type', ['pest', 'fungus', 'dehydration', 'breakage', 'expired'])->default('expired');
            $table->unsignedInteger('quantity');
            $table->date('waste_date');
            $table->unsignedSmallInteger('days_since_party_arrival')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
    }
};

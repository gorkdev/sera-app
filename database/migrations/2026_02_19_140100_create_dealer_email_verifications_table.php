<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_email_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->string('code_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedSmallInteger('send_count')->default(0);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique('dealer_id');
            $table->index(['expires_at', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_email_verifications');
    }
};


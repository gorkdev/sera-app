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
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name'); // Şirket Adı
            $table->string('contact_name'); // Yetkili Kişi
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable(); // Telefon (boş bırakılabilir)
            $table->enum('status', ['pending', 'active', 'passive'])->default('pending'); // Onay bekliyor (pending) olarak başlar
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Veriyi tamamen silmek yerine "silindi" olarak işaretler (güvenli yöntem)
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};

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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id(); // Benzersiz kimlik numarası
            $table->string('name'); // Admin adı
            $table->string('email')->unique(); // Giriş e-postası (benzersiz olmalı)
            $table->string('password'); // Şifre (şifrelenmiş tutulur)
            $table->enum('role', ['super_admin', 'admin'])->default('admin'); // Yetki seviyesi
            $table->boolean('is_active')->default(true); // Hesap aktif mi?
            $table->rememberToken(); // "Beni hatırla" özelliği için
            $table->timestamps(); // create_at ve update_at (ne zaman eklendi/güncellendi) tarihleri otomatik eklenir
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};

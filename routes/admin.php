<?php

use Illuminate\Support\Facades\Route;

// Dikkat: Burada 'yonetim' yazmamıza gerek yok, prefix zaten ekliyor.
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/', function () {
        return 'Tebrikler! Yönetim paneli içindesiniz.';
    })->name('dashboard');
});

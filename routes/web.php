<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Dealer\AuthController as DealerAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Admin Auth (giriş sayfası korumasız)
Route::prefix('yonetim')->name('admin.')->group(function () {
    Route::get('/giris', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/giris', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/cikis', [AdminAuthController::class, 'logout'])->name('logout');
});

// Bayi Auth (giriş sayfası korumasız)
Route::name('dealer.')->group(function () {
    Route::get('/giris', [DealerAuthController::class, 'showAuth'])->name('login');
    Route::post('/giris', [DealerAuthController::class, 'login'])->name('login.submit');
    Route::post('/kayit', [DealerAuthController::class, 'register'])->name('register.submit');
    Route::post('/cikis', [DealerAuthController::class, 'logout'])->name('logout');
});

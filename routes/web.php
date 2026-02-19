<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Dealer\AuthController as DealerAuthController;
use App\Http\Controllers\Dealer\EmailVerificationController as DealerEmailVerificationController;
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
    Route::get('/giris', [DealerAuthController::class, 'showLoginForm'])->name('login');
    Route::get('/kayit', [DealerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/giris', [DealerAuthController::class, 'login'])->name('login.submit');
    Route::post('/kayit', [DealerAuthController::class, 'register'])->name('register.submit');
    Route::get('/kayit/dogrula', [DealerEmailVerificationController::class, 'show'])->name('verify.show');
    Route::post('/kayit/dogrula', [DealerEmailVerificationController::class, 'verify'])->name('verify.submit');
    Route::post('/kayit/dogrula/tekrar-gonder', [DealerEmailVerificationController::class, 'resend'])->name('verify.resend');
    Route::post('/cikis', [DealerAuthController::class, 'logout'])->name('logout');
});

<?php
// web.php

use App\Http\Controllers\Dealer\DealerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Calisiyoruz!';
});

// Admin Giriş Sayfası (Kilitli değil, çünkü giriş yapabilmek için buraya erişmek lazım)
Route::get('/yonetim/giris', function () {
    return 'Burası Admin Giriş Formu Olacak';
})->name('admin.login');

// Bayi Giriş Sayfası
Route::get('/giris', function () {
    return 'Burası Bayi Giriş Formu Olacak';
})->name('dealer.login');

<?php

use Illuminate\Support\Facades\Route;

// Bayi rotaları 'dealer.auth' ile korunur.
Route::middleware(['dealer.auth'])->group(function () {

    Route::get('/panel', function () {
        return 'Hoş geldin Bayi! Burası senin sipariş ekranın olacak.';
    })->name('panel');
});

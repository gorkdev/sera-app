<?php

use App\Http\Controllers\Dealer\DealerController;
use App\Http\Controllers\Dealer\OrderController;
use Illuminate\Support\Facades\Route;

// Bayi rotaları 'dealer.auth' ile korunur.
Route::middleware(['dealer.auth'])->prefix('')->name('dealer.')->group(function () {
    Route::get('/panel', [DealerController::class, 'index'])->name('panel');
    Route::get('/siparisler', [OrderController::class, 'index'])->name('orders');
});

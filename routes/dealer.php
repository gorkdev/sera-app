<?php

use App\Http\Controllers\Dealer\DealerController;
use Illuminate\Support\Facades\Route;

// Bayi rotaları 'dealer.auth' ile korunur.
Route::middleware(['dealer.auth'])->group(function () {
    Route::get('/panel', [DealerController::class, 'index'])->name('panel');
});

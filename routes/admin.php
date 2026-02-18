<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth'])->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Kategoriler — /yonetim/kategoriler, /yonetim/kategoriler/olustur, /yonetim/kategoriler/{slug}/duzenle
    Route::get('kategoriler', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('kategoriler/olustur', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('kategoriler', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('kategoriler/{category:slug}/duzenle', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('kategoriler/{category:slug}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('kategoriler/{category:slug}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Ürünler — /yonetim/urunler, /yonetim/urunler/olustur, /yonetim/urunler/{slug}/duzenle
    Route::get('urunler', [ProductController::class, 'index'])->name('products.index');
    Route::get('urunler/olustur', [ProductController::class, 'create'])->name('products.create');
    Route::post('urunler', [ProductController::class, 'store'])->name('products.store');
    Route::get('urunler/{product:slug}/duzenle', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('urunler/{product:slug}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('urunler/{product:slug}', [ProductController::class, 'destroy'])->name('products.destroy');
});

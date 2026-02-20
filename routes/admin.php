<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DealerController;
use App\Http\Controllers\Admin\DealerGroupController;
use App\Http\Controllers\Admin\PartyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth'])->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Kategoriler — /yonetim/kategoriler, /yonetim/kategoriler/olustur, /yonetim/kategoriler/{slug}/duzenle
    Route::get('kategoriler', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('kategoriler/sirala', [CategoryController::class, 'reorder'])->name('categories.reorder');
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

    // Bayiler — /yonetim/bayiler
    Route::get('bayiler', [DealerController::class, 'index'])->name('dealers.index');
    Route::get('bayiler/{dealer}', [DealerController::class, 'edit'])->name('dealers.edit');
    Route::put('bayiler/{dealer}', [DealerController::class, 'update'])->name('dealers.update');
    Route::post('bayiler/{dealer}/onayla', [DealerController::class, 'approve'])->name('dealers.approve');
    Route::post('bayiler/{dealer}/reddet', [DealerController::class, 'reject'])->name('dealers.reject');

    // Bayi Grupları — /yonetim/gruplar
    Route::get('gruplar', [DealerGroupController::class, 'index'])->name('groups.index');
    Route::get('gruplar/olustur', [DealerGroupController::class, 'create'])->name('groups.create');
    Route::post('gruplar', [DealerGroupController::class, 'store'])->name('groups.store');
    Route::get('gruplar/{group}', [DealerGroupController::class, 'edit'])->name('groups.edit');
    Route::put('gruplar/{group}', [DealerGroupController::class, 'update'])->name('groups.update');
    Route::delete('gruplar/{group}', [DealerGroupController::class, 'destroy'])->name('groups.destroy');

    // Partiler — /yonetim/partiler
    Route::get('partiler', [PartyController::class, 'index'])->name('parties.index');
    Route::get('partiler/olustur', [PartyController::class, 'create'])->name('parties.create');
    Route::post('partiler', [PartyController::class, 'store'])->name('parties.store');
    Route::get('partiler/{party}', [PartyController::class, 'edit'])->name('parties.edit');
    Route::put('partiler/{party}', [PartyController::class, 'update'])->name('parties.update');
    Route::delete('partiler/{party}', [PartyController::class, 'destroy'])->name('parties.destroy');
    Route::post('partiler/{party}/aktif-et', [PartyController::class, 'activate'])->name('parties.activate');
    Route::post('partiler/{party}/kapat', [PartyController::class, 'close'])->name('parties.close');

    // Stoklar — /yonetim/stoklar
    Route::get('stoklar', [StockController::class, 'index'])->name('stocks.index');
    Route::get('stoklar/ekle', [StockController::class, 'create'])->name('stocks.create');
    Route::post('stoklar', [StockController::class, 'store'])->name('stocks.store');
    Route::get('stoklar/{stock}', [StockController::class, 'edit'])->name('stocks.edit');
    Route::put('stoklar/{stock}', [StockController::class, 'update'])->name('stocks.update');
    Route::delete('stoklar/{stock}', [StockController::class, 'destroy'])->name('stocks.destroy');
    Route::post('stoklar/{stock}/zayiat-ekle', [StockController::class, 'addWaste'])->name('stocks.add-waste');
    Route::delete('stoklar/zayiat/{wasteLog}', [StockController::class, 'deleteWaste'])->name('stocks.delete-waste');
});

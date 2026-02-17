<?php
// app.php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin rotalarını buraya düzgünce bağlayalım
            Route::middleware('web')
                ->prefix('yonetim') // Tüm rotaların başına /yonetim ekler
                ->group(base_path('routes/admin.php'));

            // Bayi rotalarını buraya bağlayalım
            Route::middleware('web')
                ->group(base_path('routes/dealer.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Bekçilerimizi (Middleware) burada takma adlarıyla tanımlıyoruz
        $middleware->alias([
            'admin.auth'  => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'dealer.auth' => \App\Http\Middleware\EnsureDealerAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

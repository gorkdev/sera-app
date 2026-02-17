<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auth::guard('admin') -> Admin anahtarıyla kontrol et demek.
        if (!auth()->guard('admin')->check()) {
            // Eğer giriş yapmamışsa, admin giriş sayfasına geri gönder.
            return redirect()->route('admin.login');
        }

        return $next($request); // Giriş yapmışsa, "geçebilirsin" de.
    }
}

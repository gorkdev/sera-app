<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showAuth()
    {
        if (Auth::guard('dealer')->check()) {
            return redirect()->route('panel');
        }

        return view('auth.dealer.auth');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('dealer')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'credentials' => ['Girdiğiniz bilgiler hatalı.'],
            ]);
        }

        $dealer = Auth::guard('dealer')->user();

        if ($dealer->status !== 'active') {
            Auth::guard('dealer')->logout();
            $message = match ($dealer->status) {
                'pending' => 'Hesabınız henüz onaylanmadı. Lütfen e-posta doğrulamasını tamamlayıp admin onayını bekleyin.',
                'passive' => 'Hesabınız pasif durumda. Yönetici ile iletişime geçin.',
                default => 'Hesabınızla giriş yapılamıyor.',
            };
            throw ValidationException::withMessages(['credentials' => [$message]]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function logout(Request $request)
    {
        Auth::guard('dealer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dealer.login');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:dealers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'company_name.required' => 'Şirket adı gerekli.',
            'contact_name.required' => 'Yetkili adı gerekli.',
            'email.required' => 'E-posta gerekli.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'password.required' => 'Şifre gerekli.',
            'password.min' => 'Şifre en az 6 karakter olmalı.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ]);

        Dealer::create([
            'company_name' => $validated['company_name'],
            'contact_name' => $validated['contact_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('dealer.login')
            ->with('success', 'Kayıt başarılı. Hesabınız admin onayından sonra aktif olacaktır.');
    }
}

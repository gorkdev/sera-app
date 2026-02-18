@extends('layouts.app')

@section('title', 'Yönetim Girişi')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6">
    <div class="w-full max-w-sm sm:max-w-md md:max-w-lg">
        <div class="auth-card bg-base-100 rounded-xl border border-base-300/40 shadow-sm">
            <h2 class="text-xl font-semibold text-base-content mb-1">Yönetim Paneli</h2>
            <p class="text-base-content/60 mb-6">Yönetici hesabınızla giriş yapın</p>

            @if($errors->has('credentials'))
                <div role="alert" class="alert alert-error mb-6">
                    @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                    <span>{{ $errors->first('credentials') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4 sm:space-y-5" data-login-form>
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="email" class="label py-0 font-medium">E-posta</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="input input-bordered w-full {{ $errors->has('email') ? 'input-error' : '' }}"
                        placeholder="admin@sera.com" autofocus autocomplete="email" />
                    <p class="text-error text-sm {{ $errors->has('email') ? '' : 'hidden' }}" data-error-for="email">{{ $errors->first('email') }}</p>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <label for="password" class="label py-0 font-medium">Şifre</label>
                        <button type="button" class="link link-primary link-hover text-sm" onclick="forgot_password_modal.showModal()">Şifremi unuttum</button>
                    </div>
                    <input type="password" id="password" name="password"
                        class="input input-bordered w-full {{ $errors->has('password') ? 'input-error' : '' }}"
                        placeholder="••••••••" autocomplete="current-password" />
                    <p class="text-error text-sm {{ $errors->has('password') ? '' : 'hidden' }}" data-error-for="password">{{ $errors->first('password') }}</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary rounded" {{ old('remember') ? 'checked' : '' }} />
                    <span class="text-base-content/80">Beni hatırla</span>
                </label>
                <button type="submit" class="btn btn-primary w-full">Giriş Yap</button>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-base-content/60 hover:text-base-content link-hover">
                ← Anasayfaya Dön
            </a>
        </div>
    </div>
</div>

{{-- Şifremi unuttum modal --}}
<dialog id="forgot_password_modal" class="modal">
    <div class="modal-box">
        <h3 class="font-semibold text-lg">Şifremi Unuttum</h3>
        <p class="py-2 text-base-content/70 text-sm">E-posta adresinizi girin, size şifre sıfırlama bağlantısı gönderelim.</p>
        <div class="flex flex-col gap-3 mt-4">
            <input type="email" placeholder="E-posta adresiniz" class="input input-bordered w-full" id="forgot_email" />
            <button type="button" class="btn btn-primary w-full" onclick="handleForgotPassword()">Gönder</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button type="submit">kapat</button>
    </form>
</dialog>
@endsection

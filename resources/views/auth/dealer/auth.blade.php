@extends('layouts.app')

@section('title', 'Bayi Girişi')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6">
    @php
        $showRegister = $errors->has('company_name');
    @endphp
    <div class="w-full max-w-sm sm:max-w-md md:max-w-lg" data-auth-flip>
        <div class="relative w-full auth-flip-container perspective-1000" data-flip-container>
            <div class="auth-flip-inner relative w-full transition-transform duration-500 ease-out preserve-3d {{ $showRegister ? 'flipped' : '' }}" data-flip-inner>
                {{-- Ön: Giriş --}}
                <div class="auth-flip-front auth-card backface-hidden bg-base-100 rounded-xl border border-base-300/40 shadow-sm">
                    <h2 class="text-xl font-semibold text-base-content mb-1">Giriş Yap</h2>
                    <p class="text-base-content/60 mb-6">Mevcut hesabınızla giriş yapın</p>

                    @if($errors->has('credentials'))
                        <div role="alert" class="alert alert-error mb-6">
                            @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                            <span>{{ $errors->first('credentials') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dealer.login.submit') }}" class="space-y-4 sm:space-y-5" data-login-form>
                        @csrf
                        <div class="flex flex-col gap-2">
                            <label for="login_email" class="label py-0 font-medium">E-posta</label>
                            <input type="email" id="login_email" name="email" value="{{ old('email') }}"
                                class="input input-bordered w-full {{ $errors->has('email') && !$showRegister ? 'input-error' : '' }}"
                                placeholder="ornek@firma.com" autocomplete="email" {{ !$showRegister ? 'autofocus' : '' }} />
                            <p class="text-error text-sm {{ $errors->has('email') && !$showRegister ? '' : 'hidden' }}" data-error-for="email">{{ $errors->first('email') }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label for="login_password" class="label py-0 font-medium">Şifre</label>
                                <button type="button" class="link link-primary link-hover text-sm" onclick="forgot_password_modal.showModal()">Şifremi unuttum</button>
                            </div>
                            <input type="password" id="login_password" name="password"
                                class="input input-bordered w-full {{ $errors->has('password') && !$showRegister ? 'input-error' : '' }}"
                                placeholder="••••••••" autocomplete="current-password" />
                            <p class="text-error text-sm {{ $errors->has('password') && !$showRegister ? '' : 'hidden' }}" data-error-for="password">{{ $errors->first('password') }}</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary rounded" {{ old('remember') ? 'checked' : '' }} />
                            <span class="text-base-content/80">Beni hatırla</span>
                        </label>
                        <button type="submit" class="btn btn-primary w-full">Giriş Yap</button>
                    </form>

                    <p class="mt-6 text-center text-base-content/70">
                        Hesabınız yok mu?
                        <button type="button" class="link link-primary font-medium" data-flip-trigger>Kayıt ol</button>
                    </p>
                </div>

                {{-- Arka: Kayıt --}}
                <div class="auth-flip-back auth-card backface-hidden bg-base-100 rounded-xl border border-base-300/40 shadow-sm rotate-y-180">
                    <h2 class="text-xl font-semibold text-base-content mb-1">Kayıt Ol</h2>
                    <p class="text-base-content/60 mb-6">Bayi hesabı oluşturun</p>

                    @if(session('success'))
                        <div role="alert" class="alert alert-success mb-6">
                            @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dealer.register.submit') }}" class="space-y-3" data-register-form>
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                            <div class="flex flex-col gap-2">
                                <label for="company_name" class="label py-0 font-medium">Şirket Adı</label>
                                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
                                    class="input input-bordered w-full {{ $errors->has('company_name') ? 'input-error' : '' }}"
                                    placeholder="Firma A.Ş." autocomplete="organization" />
                                <p class="text-error text-sm {{ $errors->has('company_name') ? '' : 'hidden' }}" data-error-for="company_name">{{ $errors->first('company_name') }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="contact_name" class="label py-0 font-medium">Yetkili Adı</label>
                                <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}"
                                    class="input input-bordered w-full {{ $errors->has('contact_name') ? 'input-error' : '' }}"
                                    placeholder="Ad Soyad" autocomplete="name" />
                                <p class="text-error text-sm {{ $errors->has('contact_name') ? '' : 'hidden' }}" data-error-for="contact_name">{{ $errors->first('contact_name') }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="register_email" class="label py-0 font-medium">E-posta</label>
                                <input type="email" id="register_email" name="email" value="{{ old('email') }}"
                                    class="input input-bordered w-full {{ $errors->has('email') ? 'input-error' : '' }}"
                                    placeholder="ornek@firma.com" autocomplete="email" />
                                <p class="text-error text-sm {{ $errors->has('email') ? '' : 'hidden' }}" data-error-for="email">{{ $errors->first('email') }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="phone" class="label py-0 font-medium">Telefon <span class="text-base-content/50 font-normal">(opsiyonel)</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                    class="input input-bordered w-full {{ $errors->has('phone') ? 'input-error' : '' }}"
                                    placeholder="05XX XXX XX XX" autocomplete="tel" />
                                <p class="text-error text-sm {{ $errors->has('phone') ? '' : 'hidden' }}" data-error-for="phone">{{ $errors->first('phone') }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="register_password" class="label py-0 font-medium">Şifre</label>
                                <input type="password" id="register_password" name="password"
                                    class="input input-bordered w-full {{ $errors->has('password') ? 'input-error' : '' }}"
                                    placeholder="••••••••" autocomplete="new-password" />
                                <p class="text-error text-sm {{ $errors->has('password') ? '' : 'hidden' }}" data-error-for="password">{{ $errors->first('password') }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="password_confirmation" class="label py-0 font-medium">Şifre Tekrar</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="input input-bordered w-full {{ $errors->has('password_confirmation') ? 'input-error' : '' }}"
                                    placeholder="••••••••" autocomplete="new-password" />
                                <p class="text-error text-sm {{ $errors->has('password_confirmation') ? '' : 'hidden' }}" data-error-for="password_confirmation">{{ $errors->first('password_confirmation') }}</p>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Kayıt Ol</button>
                    </form>

                    <p class="mt-6 text-center text-base-content/70">
                        Zaten hesabınız var mı?
                        <button type="button" class="link link-primary font-medium" data-flip-trigger>Giriş yap</button>
                    </p>
                </div>
            </div>
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

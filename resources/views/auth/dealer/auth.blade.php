@extends('layouts.app')

@section('title', 'Bayi Girişi')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6">
        @php
            $loginErrors = $errors->getBag('login');
            $registerErrors = $errors->getBag('register');
            $showRegister = $registerErrors->any();
        @endphp
        <div class="w-full max-w-sm sm:max-w-md md:max-w-lg" data-auth-flip>
            <div class="relative w-full auth-flip-container perspective-1000" data-flip-container>
                <div class="auth-flip-inner relative w-full transition-transform duration-500 ease-out preserve-3d {{ $showRegister ? 'flipped' : '' }}"
                    data-flip-inner>
                    {{-- Ön: Giriş --}}
                    <div
                        class="auth-flip-front auth-card backface-hidden bg-base-100 rounded-xl border border-base-300/40 shadow-sm">
                        <h2 class="text-xl font-semibold text-base-content mb-1">Giriş Yap</h2>
                        <p class="text-base-content/60 mb-6">Mevcut hesabınızla giriş yapın</p>

                        <div role="alert" class="alert alert-success mb-6 hidden" data-login-success>
                            @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                            <span data-login-success-text></span>
                        </div>

                        @if ($loginErrors->has('credentials'))
                            <div role="alert" class="alert alert-error mb-6">
                                @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                                <span>{{ $loginErrors->first('credentials') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('dealer.login.submit') }}" class="space-y-4 sm:space-y-5"
                            data-login-form>
                            @csrf
                            <div class="flex flex-col gap-2">
                                <label for="login_email" class="label py-0 font-medium">E-posta</label>
                                <input type="email" id="login_email" name="email" value="{{ old('email') }}"
                                    class="input input-bordered w-full {{ $loginErrors->has('email') ? 'input-error' : '' }}"
                                    placeholder="ornek@firma.com" autocomplete="email"
                                    {{ !$showRegister ? 'autofocus' : '' }} />
                                <p class="text-error text-sm {{ $loginErrors->has('email') ? '' : 'hidden' }}"
                                    data-error-for="email">{{ $loginErrors->first('email') }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <label for="login_password" class="label py-0 font-medium">Şifre</label>
                                    <button type="button" class="link link-primary link-hover text-sm"
                                        onclick="forgot_password_modal.showModal()">Şifremi unuttum</button>
                                </div>
                                <input type="password" id="login_password" name="password"
                                    class="input input-bordered w-full {{ $loginErrors->has('password') ? 'input-error' : '' }}"
                                    placeholder="••••••••" autocomplete="current-password" />
                                <p class="text-error text-sm {{ $loginErrors->has('password') ? '' : 'hidden' }}"
                                    data-error-for="password">{{ $loginErrors->first('password') }}</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary rounded"
                                    {{ old('remember') ? 'checked' : '' }} />
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
                    <div
                        class="auth-flip-back auth-card backface-hidden bg-base-100 rounded-xl border border-base-300/40 shadow-sm rotate-y-180">
                        <h2 class="text-xl font-semibold text-base-content mb-1">Kayıt Ol</h2>
                        <p class="text-base-content/60 mb-6">Bayi hesabı oluşturun</p>

                        <div role="alert" class="alert alert-success mb-4 hidden" data-register-success>
                            @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                            <span data-register-success-text></span>
                        </div>
                        <div role="alert" class="alert alert-error mb-4 hidden" data-register-error>
                            @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                            <span data-register-error-text>Bir hata oluştu. Lütfen tekrar deneyin.</span>
                        </div>

                        <form method="POST" action="{{ route('dealer.register.submit') }}" class="space-y-3"
                            data-register-form>
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                                <div class="flex flex-col gap-2">
                                    <label for="company_name" class="label py-0 font-medium">Şirket Adı</label>
                                    <input type="text" id="company_name" name="company_name"
                                        value="{{ old('company_name') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('company_name') ? 'input-error' : '' }}"
                                        placeholder="Firma A.Ş." autocomplete="organization" />
                                    <p class="text-error text-sm {{ $registerErrors->has('company_name') ? '' : 'hidden' }}"
                                        data-error-for="company_name">{{ $registerErrors->first('company_name') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="contact_name" class="label py-0 font-medium">Yetkili Adı</label>
                                    <input type="text" id="contact_name" name="contact_name"
                                        value="{{ old('contact_name') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('contact_name') ? 'input-error' : '' }}"
                                        placeholder="Ad Soyad" autocomplete="name" />
                                    <p class="text-error text-sm {{ $registerErrors->has('contact_name') ? '' : 'hidden' }}"
                                        data-error-for="contact_name">{{ $registerErrors->first('contact_name') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="register_email" class="label py-0 font-medium">E-posta</label>
                                    <input type="email" id="register_email" name="email" value="{{ old('email') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('email') ? 'input-error' : '' }}"
                                        placeholder="ornek@firma.com" autocomplete="email" inputmode="email" />
                                    <p class="text-error text-sm {{ $registerErrors->has('email') ? '' : 'hidden' }}"
                                        data-error-for="email">{{ $registerErrors->first('email') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="phone" class="label py-0 font-medium">Telefon</label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('phone') ? 'input-error' : '' }}"
                                        placeholder="0555 555 55 55" autocomplete="tel" inputmode="tel" />
                                    <p class="text-error text-sm {{ $registerErrors->has('phone') ? '' : 'hidden' }}"
                                        data-error-for="phone">{{ $registerErrors->first('phone') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="tax_office" class="label py-0 font-medium">Vergi Dairesi</label>
                                    <input type="text" id="tax_office" name="tax_office"
                                        value="{{ old('tax_office') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('tax_office') ? 'input-error' : '' }}"
                                        placeholder="Örn: Kadıköy" autocomplete="off" />
                                    <p class="text-error text-sm {{ $registerErrors->has('tax_office') ? '' : 'hidden' }}"
                                        data-error-for="tax_office">{{ $registerErrors->first('tax_office') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="tax_number" class="label py-0 font-medium">Vergi No / TCKN</label>
                                    <input type="text" id="tax_number" name="tax_number"
                                        value="{{ old('tax_number') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('tax_number') ? 'input-error' : '' }}"
                                        placeholder="10 veya 11 haneli" inputmode="numeric" autocomplete="off" />
                                    <p class="text-error text-sm {{ $registerErrors->has('tax_number') ? '' : 'hidden' }}"
                                        data-error-for="tax_number">{{ $registerErrors->first('tax_number') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="city" class="label py-0 font-medium">İl</label>
                                    <input type="text" id="city" name="city" value="{{ old('city') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('city') ? 'input-error' : '' }}"
                                        placeholder="Örn: İstanbul" autocomplete="address-level1" />
                                    <p class="text-error text-sm {{ $registerErrors->has('city') ? '' : 'hidden' }}"
                                        data-error-for="city">{{ $registerErrors->first('city') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="district" class="label py-0 font-medium">İlçe</label>
                                    <input type="text" id="district" name="district" value="{{ old('district') }}"
                                        class="input input-bordered w-full {{ $registerErrors->has('district') ? 'input-error' : '' }}"
                                        placeholder="Örn: Kadıköy" autocomplete="address-level2" />
                                    <p class="text-error text-sm {{ $registerErrors->has('district') ? '' : 'hidden' }}"
                                        data-error-for="district">{{ $registerErrors->first('district') }}</p>
                                </div>
                                <div class="flex flex-col gap-2 sm:col-span-2">
                                    <label for="address" class="label py-0 font-medium">Adres</label>
                                    <textarea id="address" name="address" rows="3"
                                        class="textarea textarea-bordered w-full {{ $registerErrors->has('address') ? 'textarea-error' : '' }}"
                                        placeholder="Açık adres" autocomplete="street-address">{{ old('address') }}</textarea>
                                    <p class="text-error text-sm {{ $registerErrors->has('address') ? '' : 'hidden' }}"
                                        data-error-for="address">{{ $registerErrors->first('address') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="register_password" class="label py-0 font-medium">Şifre</label>
                                    <div class="relative">
                                        <input type="password" id="register_password" name="password"
                                            class="input input-bordered w-full pr-12 {{ $registerErrors->has('password') ? 'input-error' : '' }}"
                                            placeholder="••••••••" autocomplete="new-password" />
                                        <button type="button"
                                            class="btn btn-ghost btn-sm btn-square absolute right-1 top-1/2 -translate-y-1/2"
                                            data-toggle-password="register_password" aria-label="Şifreyi göster/gizle">
                                            <span data-eye="show">@svg('heroicon-o-eye', 'h-5 w-5')</span>
                                            <span data-eye="hide" class="hidden">@svg('heroicon-o-eye-slash', 'h-5 w-5')</span>
                                        </button>
                                    </div>
                                    <p class="text-error text-sm {{ $registerErrors->has('password') ? '' : 'hidden' }}"
                                        data-error-for="password">{{ $registerErrors->first('password') }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="password_confirmation" class="label py-0 font-medium">Şifre Tekrar</label>
                                    <div class="relative">
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="input input-bordered w-full pr-12 {{ $registerErrors->has('password_confirmation') ? 'input-error' : '' }}"
                                            placeholder="••••••••" autocomplete="new-password" />
                                        <button type="button"
                                            class="btn btn-ghost btn-sm btn-square absolute right-1 top-1/2 -translate-y-1/2"
                                            data-toggle-password="password_confirmation"
                                            aria-label="Şifreyi göster/gizle">
                                            <span data-eye="show">@svg('heroicon-o-eye', 'h-5 w-5')</span>
                                            <span data-eye="hide" class="hidden">@svg('heroicon-o-eye-slash', 'h-5 w-5')</span>
                                        </button>
                                    </div>
                                    <p class="text-error text-sm {{ $registerErrors->has('password_confirmation') ? '' : 'hidden' }}"
                                        data-error-for="password_confirmation">
                                        {{ $registerErrors->first('password_confirmation') }}</p>
                                </div>
                            </div>

                            <div class="mt-1">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="kvkk_consent"
                                        class="checkbox checkbox-sm checkbox-primary rounded mt-1"
                                        {{ old('kvkk_consent') ? 'checked' : '' }} />
                                    <span class="text-sm text-base-content/80">
                                        KVKK aydınlatma metnini okudum ve onaylıyorum.
                                    </span>
                                </label>
                                <p class="text-error text-sm {{ $registerErrors->has('kvkk_consent') ? '' : 'hidden' }}"
                                    data-error-for="kvkk_consent">{{ $registerErrors->first('kvkk_consent') }}</p>
                            </div>

                            <button type="submit" class="btn btn-primary w-full">Kayıt Ol</button>
                        </form>

                        <p class="mt-6 text-center text-base-content/70">
                            Zaten hesabınız var mı?
                            <button type="button" class="link link-primary font-medium" data-flip-trigger>Giriş
                                yap</button>
                        </p>
                    </div>
                </div>
            </div>


        </div>
    </div>

    {{-- Şifremi unuttum modal --}}
    <dialog id="forgot_password_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-semibold text-lg">Şifremi Unuttum</h3>
            <p class="py-2 text-base-content/70 text-sm">E-posta adresinizi girin, size şifre sıfırlama bağlantısı
                gönderelim.</p>
            <div class="flex flex-col gap-3 mt-4">
                <input type="email" placeholder="E-posta adresiniz" class="input input-bordered w-full"
                    id="forgot_email" />
                <button type="button" class="btn btn-primary w-full" onclick="handleForgotPassword()">Gönder</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="submit">kapat</button>
        </form>
    </dialog>
@endsection

@extends('layouts.app')

@section('title', 'Bayi Girişi')

@section('content')
    @php
        $loginErrors = $errors->getBag('login');
    @endphp

    <div class="min-h-[70vh] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6">
        <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-base-300/40 bg-base-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2">
                {{-- Sol: görsel --}}
                <div class="relative hidden md:block">
                    <img src="{{ asset('images/auth-placeholder.png') }}" alt="Sera"
                        class="absolute inset-0 h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-linear-to-br from-base-100/10 via-base-100/40 to-base-100/10"></div>


                </div>

                {{-- Sağ: form --}}
                <div class="p-6 sm:p-10">
                    <h2 class="text-xl font-semibold text-base-content">Giriş Yap</h2>
                    <p class="text-base-content/60 mt-1 mb-6">Bayi hesabınızla giriş yapın</p>

                    <div role="alert" class="alert alert-success mb-6 hidden" data-login-success>
                        @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                        <span data-login-success-text></span>
                    </div>

                    @if (session('needs_email_verification'))
                        <div role="alert" class="alert alert-warning mb-6">
                            @svg('heroicon-s-exclamation-triangle', 'h-5 w-5 shrink-0')
                            <div class="flex-1">
                                <div class="font-semibold">E-posta doğrulaması gerekli</div>
                                <div class="text-sm opacity-80">
                                    {{ session('needs_email_verification_email') ?? 'E-posta adresinizi' }} doğrulamadan
                                    giriş yapamazsınız.
                                </div>
                            </div>
                            <div class="flex gap-2 flex-col">

                                <a href="{{ route('dealer.verify.show') }}" class="btn btn-sm btn-primary">Kodu gir</a>
                            </div>
                        </div>
                    @endif

                    @if (session('admin_pending'))
                        <div role="alert" class="alert alert-info mb-6">
                            @svg('heroicon-s-clock', 'h-5 w-5 shrink-0')
                            <div>
                                <div class="font-semibold">Yönetici onayı bekleniyor</div>
                                <div class="text-sm opacity-80">E-posta doğrulandı. Hesabınız yönetici onayından sonra aktif
                                    olacaktır.</div>
                            </div>
                        </div>
                    @endif

                    @if (session('dealer_passive'))
                        <div role="alert" class="alert alert-error mb-6">
                            @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                            <div>
                                <div class="font-semibold">Hesabınız pasif</div>
                                <div class="text-sm opacity-80">Yönetici ile iletişime geçin.</div>
                            </div>
                        </div>
                    @endif

                    @if (session('dealer_blocked'))
                        <div role="alert" class="alert alert-error mb-6">
                            @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                            <div>
                                <div class="font-semibold">Giriş yapılamıyor</div>
                                <div class="text-sm opacity-80">Hesabınızla giriş yapılamıyor. Yönetici ile iletişime geçin.
                                </div>
                            </div>
                        </div>
                    @endif

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
                                placeholder="ornek@firma.com" autocomplete="email" autofocus />
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

                    <div class="text-sm text-center mt-8 text-base-content/70">
                        Hesabınız yok mu?
                        <a href="{{ route('dealer.register') }}" class="link link-primary font-medium link-hover">Kayıt
                            olun</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Şifremi unuttum modal (tasarım amaçlı) --}}
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

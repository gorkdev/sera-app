@extends('layouts.app')

@section('title', 'E-posta Doğrulama')

@section('content')
    @php
        $verifyErrors = $errors->getBag('verify');
    @endphp

    <div class="min-h-[70vh] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6">
        <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-base-300/40 bg-base-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2">
                {{-- Sol: görsel --}}
                <div class="relative hidden md:block">
                    <img src="{{ asset('images/auth-placeholder.png') }}" alt="Sera"
                        class="absolute inset-0 h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-linear-to-br from-base-100/10 via-base-100/40 to-base-100/10"></div>

                    <div class="relative flex h-full flex-col justify-between p-10">
                        <div>
                            <a href="{{ route('home') }}"
                                class="inline-flex items-center gap-2 text-2xl font-semibold tracking-tight">
                                <span class="text-primary">Sera</span>
                                <span class="text-base-content/70 text-sm font-medium">B2B</span>
                            </a>
                            <p class="mt-3 text-base-content/70 leading-relaxed">
                                Güvenliğiniz için e-postanızı doğruluyoruz.
                            </p>
                        </div>

                        <div class="text-sm text-base-content/70">
                            Kodu almadınız mı? Sağ taraftan tekrar gönderebilirsiniz.
                        </div>
                    </div>
                </div>

                {{-- Sağ: form --}}
                <div class="p-6 sm:p-10">
                    <h2 class="text-xl font-semibold text-base-content">E-postanı doğrula</h2>
                    <p class="text-base-content/60 mt-1 mb-6">
                        <span class="font-medium">{{ $maskedEmail }}</span> adresine gönderdiğimiz 6 haneli kodu gir.
                    </p>

                    @if (session('success'))
                        <div role="alert" class="alert alert-success mb-4">
                            @svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($verifyErrors->has('code') || $verifyErrors->has('resend'))
                        <div role="alert" class="alert alert-error mb-4">
                            @svg('heroicon-s-x-circle', 'h-5 w-5 shrink-0')
                            <span>
                                {{ $verifyErrors->first('code') ?: $verifyErrors->first('resend') }}
                            </span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dealer.verify.submit') }}" data-verify-form class="space-y-5">
                        @csrf

                        <input type="hidden" name="code" value="" data-verify-code />

                        <div class="flex items-center justify-between gap-2 sm:gap-3">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                    class="input input-bordered w-12 sm:w-14 text-center text-lg font-semibold"
                                    data-code-input aria-label="Kod {{ $i + 1 }}" {{ $i === 0 ? 'autofocus' : '' }}
                                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" />
                            @endfor
                        </div>

                        <button type="submit" class="btn btn-primary w-full">Doğrula</button>
                    </form>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <form method="POST" action="{{ route('dealer.verify.resend') }}" data-resend-form class="flex-1">
                            @csrf
                            <button type="submit" class="btn btn-outline w-full" data-resend-btn
                                data-cooldown="{{ (int) ($cooldownSeconds ?? 0) }}">
                                Kodu tekrar gönder
                            </button>
                        </form>

                        <a href="{{ route('dealer.register') }}" class="btn btn-ghost">Geri Dön</a>
                    </div>

                    <div class="mt-8 text-center md:hidden">
                        <a href="{{ route('home') }}" class="text-base-content/60 hover:text-base-content link-hover">
                            ← Anasayfaya Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

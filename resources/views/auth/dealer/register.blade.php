@extends('layouts.app')

@section('title', 'Bayi Kayıt')

@section('content')
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


                        </div>


                    </div>
                </div>

                {{-- Sağ: form --}}
                <div class="p-6 sm:p-10">
                    <h2 class="text-xl font-semibold text-base-content">Kayıt Ol</h2>
                    <p class="text-base-content/60 mt-1 mb-6">Bayi hesabı oluşturun</p>

                    <livewire:dealer.register-form />

                    <div class="mt-6 md:hidden text-center text-base-content/70">
                        Zaten hesabınız var mı?
                        <a href="{{ route('dealer.login') }}" class="link link-primary font-medium link-hover">Giriş
                            yapın</a>
                    </div>

                    <div class="mt-8 text-center text-sm text-base-content/70">
                        Zaten hesabınız var mı?
                        <a href="{{ route('dealer.login') }}" class="link link-primary font-medium link-hover">Giriş
                            yapın</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

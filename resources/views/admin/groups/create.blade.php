@extends('layouts.admin')

@section('title', 'Yeni Bayi Grubu')

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.groups.index') }}" class="hover:text-base-content">Bayi Grupları</a>
            <span>/</span>
            <span class="text-base-content">Yeni</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Yeni Bayi Grubu</h1>
                <p class="mt-1 text-sm text-base-content/60">Yeni bir bayi grubu oluşturun</p>
            </div>
            <a href="{{ route('admin.groups.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.groups.store') }}" class="admin-form space-y-6 max-w-3xl">
        @csrf

        {{-- Temel Bilgiler --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-document-text', 'h-4 w-4')
                Temel Bilgiler
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Ne doldurmalıyım?</p>
                    <p class="text-sm opacity-90">Grup Adı: Bayi listesinde görünecek isim. Örn: "VIP Bayiler". Kod: Teknik kod (büyük harf). Örn: "VIP". Gecikme Süresi: Stok erişim gecikmesi (dakika). VIP: 0, Standart: 15, Yeni: 30.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="name" class="label">
                            <span class="label-text font-medium">Grup Adı <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="input input-bordered input-md w-full @error('name') input-error @enderror"
                            placeholder="Örn: VIP Bayiler" required />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="code" class="label">
                            <span class="label-text font-medium">Grup Kodu <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}"
                            class="input input-bordered input-md w-full @error('code') input-error @enderror"
                            placeholder="Örn: VIP" required />
                        @error('code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label for="delay_minutes" class="label">
                        <span class="label-text font-medium">Gecikme Süresi (dakika) <span class="text-error">*</span></span>
                    </label>
                    <input type="number" id="delay_minutes" name="delay_minutes" value="{{ old('delay_minutes', 0) }}"
                        class="input input-bordered input-md w-full @error('delay_minutes') input-error @enderror"
                        min="0" step="1" required />
                    <label class="label">
                        <span class="label-text-alt">VIP: 0, Standart: 15, Yeni: 30 dakika</span>
                    </label>
                    @error('delay_minutes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Durum --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 mb-4 flex items-center gap-2">
                @svg('heroicon-o-adjustments-horizontal', 'h-4 w-4')
                Durum
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Varsayılan Grup</p>
                    <p class="text-sm opacity-90">Varsayılan grup, yeni kayıt olan bayilerin otomatik atandığı gruptur. Sadece bir grup varsayılan olabilir.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="form-control">
                    <label for="sort_order" class="label">
                        <span class="label-text font-medium">Sıralama</span>
                    </label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                        class="input input-bordered input-md w-full @error('sort_order') input-error @enderror"
                        min="0" step="1" />
                    @error('sort_order')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_default" value="1" class="checkbox checkbox-primary"
                            {{ old('is_default') ? 'checked' : '' }} />
                        <span class="label-text font-medium">Varsayılan Grup</span>
                    </label>
                </div>
            </div>
        </section>

        {{-- İşlemler --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Grup Oluştur
            </button>
            <a href="{{ route('admin.groups.index') }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>
@endsection

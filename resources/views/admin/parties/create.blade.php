@extends('layouts.admin')

@section('title', 'Yeni Parti')

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.parties.index') }}" class="hover:text-base-content">Partiler</a>
            <span>/</span>
            <span class="text-base-content">Yeni</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Yeni Parti</h1>
                <p class="mt-1 text-sm text-base-content/60">Yeni bir satış partisi oluşturun</p>
            </div>
            <a href="{{ route('admin.parties.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.parties.store') }}" class="admin-form space-y-6 max-w-3xl">
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
                    <p class="text-sm opacity-90">Parti Adı: Bayilerin göreceği parti ismi. Örn: "Şubat 2026 Partisi". Açıklama: Parti hakkında bilgi (opsiyonel). Parti oluşturulduğunda "Taslak" durumunda olur. Stokları yükledikten sonra "Aktif Et" ile bayilere açabilirsiniz.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="party_code" class="label">
                            <span class="label-text font-medium">Parti Kodu</span>
                        </label>
                        <input type="text" id="party_code" name="party_code" value="{{ old('party_code') }}"
                            class="input input-bordered input-md w-full @error('party_code') input-error @enderror"
                            placeholder="NL-TR-YYYY-MM-XXXX" />
                        <label class="label">
                            <span class="label-text-alt">Boş bırakılırsa otomatik oluşturulur</span>
                        </label>
                        @error('party_code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="name" class="label">
                            <span class="label-text font-medium">Parti Adı <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="input input-bordered input-md w-full @error('name') input-error @enderror"
                            placeholder="Örn: Şubat 2026 Partisi" required />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label for="description" class="label">
                        <span class="label-text font-medium">Açıklama</span>
                    </label>
                    <div class="textarea textarea-md w-full min-h-28 @error('description') textarea-error @enderror">
                        <textarea id="description" name="description" rows="5" class="resize-y"
                            placeholder="Bu parti hakkında açıklama...">{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Lojistik Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-truck', 'h-4 w-4')
                Lojistik Bilgileri
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Lojistik Detayları</p>
                    <p class="text-sm opacity-90">Tedarikçi ve tır plakası bilgileri zorunludur. Yolculuk süresi opsiyoneldir. Bu veriler çiçeğin tazelik skorunu ve maliyet hesaplamalarını etkiler.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="supplier_name" class="label">
                            <span class="label-text font-medium">Tedarikçi <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}"
                            class="input input-bordered input-md w-full @error('supplier_name') input-error @enderror"
                            placeholder="Örn: Hollanda Green Bloom Ltd" required />
                        @error('supplier_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="truck_plate" class="label">
                            <span class="label-text font-medium">Tır Plakası <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="truck_plate" name="truck_plate" value="{{ old('truck_plate') }}"
                            class="input input-bordered input-md w-full @error('truck_plate') input-error @enderror"
                            placeholder="Örn: 34 ABC 123" required />
                        @error('truck_plate')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label for="journey_days" class="label">
                        <span class="label-text font-medium">Yolculuk Süresi (Gün)</span>
                    </label>
                    <input type="number" id="journey_days" name="journey_days" value="{{ old('journey_days') }}"
                        class="input input-bordered input-md w-full @error('journey_days') input-error @enderror"
                        min="0" step="1" placeholder="Örn: 3" />
                    <label class="label">
                        <span class="label-text-alt">Hollanda'dan Türkiye'ye kaç gün sürdü?</span>
                    </label>
                    @error('journey_days')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Maliyet Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-currency-dollar', 'h-4 w-4')
                Maliyet Bilgileri (İstatistik İçin)
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Maliyet Hesaplamaları</p>
                    <p class="text-sm opacity-90">Birim alış fiyatı, lojistik maliyeti ve gümrük/vergi masrafları zorunludur. Bu bilgiler istatistik ve kar analizi için kullanılır.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="currency" class="label">
                            <span class="label-text font-medium">Döviz</span>
                        </label>
                        <select name="currency" id="currency"
                            class="select select-bordered select-md w-full @error('currency') select-error @enderror">
                            <option value="EUR" {{ old('currency', 'EUR') == 'EUR' ? 'selected' : '' }}>EUR (€)
                            </option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="TRY" {{ old('currency') == 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
                        </select>
                        @error('currency')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="purchase_price_per_unit" class="label">
                            <span class="label-text font-medium">Birim Alış Fiyatı <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="purchase_price_per_unit" name="purchase_price_per_unit"
                            value="{{ old('purchase_price_per_unit') }}"
                            class="input input-bordered input-md w-full @error('purchase_price_per_unit') input-error @enderror"
                            step="0.01" min="0" placeholder="0.00" required />
                        @error('purchase_price_per_unit')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="logistics_cost" class="label">
                            <span class="label-text font-medium">Lojistik Maliyeti <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="logistics_cost" name="logistics_cost"
                            value="{{ old('logistics_cost') }}"
                            class="input input-bordered input-md w-full @error('logistics_cost') input-error @enderror"
                            step="0.01" min="0" placeholder="0.00" required />
                        @error('logistics_cost')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="customs_cost" class="label">
                            <span class="label-text font-medium">Gümrük/Vergi Masrafları <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="customs_cost" name="customs_cost" value="{{ old('customs_cost') }}"
                            class="input input-bordered input-md w-full @error('customs_cost') input-error @enderror"
                            step="0.01" min="0" placeholder="0.00" required />
                        @error('customs_cost')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- İşlemler --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Parti Oluştur
            </button>
            <a href="{{ route('admin.parties.index') }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>
@endsection

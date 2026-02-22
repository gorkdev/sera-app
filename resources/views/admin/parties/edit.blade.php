@extends('layouts.admin')

@section('title', 'Parti Düzenle — ' . $party->name)

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.parties.index') }}" class="hover:text-base-content">Partiler</a>
            <span>/</span>
            <span class="text-base-content">{{ $party->name }}</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Parti Düzenle</h1>
                <p class="mt-1 text-sm text-base-content/60">
                    Durum: 
                    @php
                        $statusLabel = [
                            'draft' => 'Taslak',
                            'active' => 'Aktif',
                            'closed' => 'Kapalı',
                        ][$party->status] ?? $party->status;
                        $statusClass = match ($party->status) {
                            'active' => 'badge-success',
                            'draft' => 'badge-warning',
                            'closed' => 'badge-error',
                            default => 'badge-ghost',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} badge-sm">{{ $statusLabel }}</span>
                    @if($party->isActive() || $party->isClosed())
                        <span class="text-warning ml-2">⚠ Aktif veya kapalı partiler düzenlenemez.</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.parties.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    @if($party->isActive())
        <div class="alert alert-info mb-6">
            @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
            <div>
                <p class="font-medium">Aktif parti</p>
                <p class="text-sm opacity-90">Aktif partide sadece <strong>Varış Tarihi</strong> ve <strong>Yolculuk Süresi</strong> güncellenebilir. Tır geldiğinde varış tarihini işaretleyin.</p>
            </div>
        </div>
    @endif
    @if($party->isClosed())
        <div class="alert alert-warning mb-6">
            @svg('heroicon-o-exclamation-triangle', 'h-5 w-5 shrink-0')
            <div>
                <p class="font-medium">Kapalı parti</p>
                <p class="text-sm opacity-90">Kapalı partiler düzenlenemez.</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.parties.update', $party) }}" class="admin-form space-y-6 max-w-3xl">
        @csrf
        @method('PUT')

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
                    <p class="text-sm opacity-90">Parti Adı: Bayilerin göreceği parti ismi. Açıklama: Parti hakkında bilgi (opsiyonel).</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="party_code" class="label">
                            <span class="label-text font-medium">Parti Kodu</span>
                        </label>
                        <input type="text" id="party_code" name="party_code" value="{{ old('party_code', $party->party_code) }}"
                            class="input input-bordered input-md w-full @error('party_code') input-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())
                            placeholder="NL-TR-YYYY-MM-XXXX" />
                        @error('party_code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="name" class="label">
                            <span class="label-text font-medium">Parti Adı <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $party->name) }}"
                            class="input input-bordered input-md w-full @error('name') input-error @enderror"
                            placeholder="Örn: Şubat 2026 Partisi" 
                            @disabled($party->isActive() || $party->isClosed())
                            required />
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
                            @disabled($party->isActive() || $party->isClosed())
                            placeholder="Bu parti hakkında açıklama...">{{ old('description', $party->description) }}</textarea>
                    </div>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        @if($party->isDraft())
        {{-- Sipariş Penceresi (sadece taslak) --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-calendar', 'h-4 w-4')
                Sipariş Penceresi
            </h2>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="starts_at" class="label">
                            <span class="label-text font-medium">Sipariş Başlangıç Tarihi <span class="text-error">*</span></span>
                        </label>
                        <input type="datetime-local" id="starts_at" name="starts_at"
                            value="{{ old('starts_at', $party->starts_at?->format('Y-m-d\TH:i')) }}"
                            class="input input-bordered input-md w-full @error('starts_at') input-error @enderror" required />
                        @error('starts_at')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control" id="ends_at_wrapper">
                        <label for="ends_at" class="label">
                            <span class="label-text font-medium">Sipariş Bitiş Tarihi</span>
                        </label>
                        <input type="datetime-local" id="ends_at" name="ends_at"
                            value="{{ old('ends_at', $party->ends_at?->format('Y-m-d\TH:i')) }}"
                            class="input input-bordered input-md w-full @error('ends_at') input-error @enderror" />
                        @error('ends_at')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="close_when_stock_runs_out" value="1"
                            {{ old('close_when_stock_runs_out', $party->close_when_stock_runs_out) ? 'checked' : '' }}
                            class="checkbox checkbox-primary checkbox-sm"
                            id="close_when_stock_runs_out" />
                        <span class="label-text font-medium">Stok bitene kadar açık tut</span>
                    </label>
                </div>
            </div>
        </section>
        @endif

        {{-- Lojistik Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-truck', 'h-4 w-4')
                Lojistik Bilgileri
            </h2>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="supplier_name" class="label">
                            <span class="label-text font-medium">Tedarikçi</span>
                        </label>
                        <input type="text" id="supplier_name" name="supplier_name" value="{{ old('supplier_name', $party->supplier_name) }}"
                            class="input input-bordered input-md w-full @error('supplier_name') input-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())
                            placeholder="Örn: Hollanda Green Bloom Ltd" />
                        @error('supplier_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="truck_plate" class="label">
                            <span class="label-text font-medium">Tır Plakası</span>
                        </label>
                        <input type="text" id="truck_plate" name="truck_plate" value="{{ old('truck_plate', $party->truck_plate) }}"
                            class="input input-bordered input-md w-full @error('truck_plate') input-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())
                            placeholder="Örn: 34 ABC 123" />
                        @error('truck_plate')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="journey_days" class="label">
                            <span class="label-text font-medium">Yolculuk Süresi (Gün)</span>
                        </label>
                        <input type="number" id="journey_days" name="journey_days" value="{{ old('journey_days', $party->journey_days) }}"
                            class="input input-bordered input-md w-full @error('journey_days') input-error @enderror"
                            @disabled($party->isClosed())
                            min="0" step="1" placeholder="Örn: 3" />
                        @error('journey_days')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="arrived_at" class="label">
                            <span class="label-text font-medium">Varış Tarihi</span>
                        </label>
                        <input type="datetime-local" id="arrived_at" name="arrived_at" 
                            value="{{ old('arrived_at', $party->arrived_at ? $party->arrived_at->format('Y-m-d\TH:i') : '') }}"
                            class="input input-bordered input-md w-full @error('arrived_at') input-error @enderror"
                            @disabled($party->isClosed()) />
                        <label class="label">
                            <span class="label-text-alt">Tır geldiğinde bu alanı doldurun</span>
                        </label>
                        @error('arrived_at')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- Maliyet Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-currency-dollar', 'h-4 w-4')
                Maliyet Bilgileri
            </h2>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="currency" class="label">
                            <span class="label-text font-medium">Döviz</span>
                        </label>
                        <select name="currency" id="currency"
                            class="select select-bordered select-md w-full @error('currency') select-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())>
                            <option value="EUR" {{ old('currency', $party->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="USD" {{ old('currency', $party->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="TRY" {{ old('currency', $party->currency) == 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
                        </select>
                        @error('currency')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="purchase_price_per_unit" class="label">
                            <span class="label-text font-medium">Birim Alış Fiyatı</span>
                        </label>
                        <input type="number" id="purchase_price_per_unit" name="purchase_price_per_unit" 
                            value="{{ old('purchase_price_per_unit', $party->purchase_price_per_unit) }}"
                            class="input input-bordered input-md w-full @error('purchase_price_per_unit') input-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())
                            step="0.01" min="0" placeholder="0.00" />
                        @error('purchase_price_per_unit')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="logistics_cost" class="label">
                            <span class="label-text font-medium">Lojistik Maliyeti</span>
                        </label>
                        <input type="number" id="logistics_cost" name="logistics_cost" 
                            value="{{ old('logistics_cost', $party->logistics_cost) }}"
                            class="input input-bordered input-md w-full @error('logistics_cost') input-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())
                            step="0.01" min="0" placeholder="0.00" />
                        @error('logistics_cost')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label for="customs_cost" class="label">
                            <span class="label-text font-medium">Gümrük/Vergi Masrafları</span>
                        </label>
                        <input type="number" id="customs_cost" name="customs_cost" 
                            value="{{ old('customs_cost', $party->customs_cost) }}"
                            class="input input-bordered input-md w-full @error('customs_cost') input-error @enderror"
                            @disabled($party->isActive() || $party->isClosed())
                            step="0.01" min="0" placeholder="0.00" />
                        @error('customs_cost')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if($party->net_cost_per_unit)
                    <div class="alert alert-success">
                        @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                        <div>
                            <p class="font-medium">Net Birim Maliyet</p>
                            <p class="text-sm opacity-90">
                                {{ number_format($party->net_cost_per_unit, 2, ',', '.') }} {{ $party->currency }}
                                (Alış + Lojistik + Gümrük / Toplam Stok)
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Bilgiler --}}
        @if($party->createdByAdmin || $party->activated_at || $party->closed_at)
            <section class="admin-form-section">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                    @svg('heroicon-o-information-circle', 'h-4 w-4')
                    Parti Bilgileri
                </h2>
                <div class="space-y-3 text-sm">
                    @if($party->createdByAdmin)
                        <div class="flex items-center gap-2">
                            <span class="text-base-content/60 w-32">Oluşturan:</span>
                            <span>{{ $party->createdByAdmin->name }}</span>
                        </div>
                    @endif
                    @if($party->activated_at)
                        <div class="flex items-center gap-2">
                            <span class="text-base-content/60 w-32">Aktif Edilme:</span>
                            <span>{{ formatliTarih($party->activated_at) }}</span>
                        </div>
                    @endif
                    @if($party->closed_at)
                        <div class="flex items-center gap-2">
                            <span class="text-base-content/60 w-32">Kapanma:</span>
                            <span>{{ formatliTarih($party->closed_at) }}</span>
                            @if($party->closedByAdmin)
                                <span class="text-base-content/50">({{ $party->closedByAdmin->name }})</span>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- İşlemler --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            @if($party->isDraft() || $party->isActive())
                <button type="submit" class="btn btn-primary gap-2">
                    @svg('heroicon-o-check', 'h-4 w-4')
                    {{ $party->isActive() ? 'Varış Bilgisini Kaydet' : 'Değişiklikleri Kaydet' }}
                </button>
            @endif
            <a href="{{ route('admin.parties.index') }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>

    @if($party->isDraft())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('close_when_stock_runs_out');
            const endsAtWrapper = document.getElementById('ends_at_wrapper');
            const endsAtInput = document.getElementById('ends_at');
            if (!checkbox || !endsAtWrapper) return;

            function toggleEndsAt() {
                if (checkbox.checked) {
                    endsAtWrapper.style.opacity = '0.5';
                    endsAtInput.disabled = true;
                    endsAtInput.removeAttribute('required');
                } else {
                    endsAtWrapper.style.opacity = '1';
                    endsAtInput.disabled = false;
                    endsAtInput.setAttribute('required', 'required');
                }
            }
            checkbox.addEventListener('change', toggleEndsAt);
            toggleEndsAt();
        });
    </script>
    @endif
@endsection

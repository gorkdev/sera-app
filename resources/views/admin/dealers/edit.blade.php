@extends('layouts.admin')

@section('title', 'Bayi Düzenle — ' . $dealer->company_name)

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.dealers.index') }}" class="hover:text-base-content">Bayiler</a>
            <span>/</span>
            <span class="text-base-content">{{ $dealer->company_name }}</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Bayi Düzenle</h1>
                <p class="mt-1 text-sm text-base-content/60">E-posta: <code
                        class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $dealer->email }}</code></p>
            </div>
            <a href="{{ route('admin.dealers.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sol: Form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.dealers.update', $dealer) }}" class="admin-form space-y-6"
                data-register-form data-register-mode="edit">
                @csrf
                @method('PUT')

                {{-- Temel Bilgiler --}}
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                        @svg('heroicon-o-document-text', 'h-4 w-4')
                        Temel Bilgiler
                    </h2>
                    <div class="alert alert-info mb-4">
                        @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                        <div>
                            <p class="font-medium">Ne doldurmalıyım?</p>
                            <p class="text-sm opacity-90">Şirket adı, yetkili adı, e-posta ve iletişim bilgileri. Vergi
                                bilgileri ve adres alanları.</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="form-control">
                            <label for="dealer_group_id" class="label">
                                <span class="label-text font-medium">Bayi Grubu</span>
                            </label>
                            <select name="dealer_group_id" id="dealer_group_id"
                                class="select select-md w-full @error('dealer_group_id') select-error @enderror">
                                <option value=""
                                    {{ !old('dealer_group_id', $dealer->dealer_group_id) ? 'selected' : '' }}>— Grup seçin —
                                </option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}"
                                        {{ old('dealer_group_id', $dealer->dealer_group_id) == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('dealer_group_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-control">
                                <label for="company_name" class="label">
                                    <span class="label-text font-medium">Şirket Adı <span class="text-error">*</span></span>
                                </label>
                                <input type="text" id="company_name" name="company_name"
                                    value="{{ old('company_name', $dealer->company_name) }}"
                                    class="input input-bordered input-md w-full @error('company_name') input-error @enderror"
                                    placeholder="Örn: Firma A.Ş." required />
                                @error('company_name')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control">
                                <label for="contact_name" class="label">
                                    <span class="label-text font-medium">Yetkili Adı <span
                                            class="text-error">*</span></span>
                                </label>
                                <input type="text" id="contact_name" name="contact_name"
                                    value="{{ old('contact_name', $dealer->contact_name) }}"
                                    class="input input-bordered input-md w-full @error('contact_name') input-error @enderror"
                                    placeholder="Örn: Ad Soyad" required />
                                @error('contact_name')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-control">
                                <label for="email" class="label">
                                    <span class="label-text font-medium">E-posta <span class="text-error">*</span></span>
                                </label>
                                <input type="email" id="email" name="email"
                                    value="{{ old('email', $dealer->email) }}"
                                    class="input input-bordered input-md w-full @error('email') input-error @enderror"
                                    placeholder="ornek@firma.com" required />
                                @error('email')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control">
                                <label for="phone" class="label">
                                    <span class="label-text font-medium">Telefon <span class="text-error">*</span></span>
                                </label>
                                <input type="tel" id="phone" name="phone"
                                    value="{{ old('phone', $dealer->phone) }}"
                                    class="input input-bordered input-md w-full @error('phone') input-error @enderror"
                                    placeholder="0555 555 55 55" required />
                                @error('phone')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-control">
                                <label for="tax_office" class="label">
                                    <span class="label-text font-medium">Vergi Dairesi <span
                                            class="text-error">*</span></span>
                                </label>
                                <input type="text" id="tax_office" name="tax_office"
                                    value="{{ old('tax_office', $dealer->tax_office) }}"
                                    class="input input-bordered input-md w-full @error('tax_office') input-error @enderror"
                                    placeholder="Örn: Kadıköy" required />
                                @error('tax_office')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control">
                                <label for="tax_number" class="label">
                                    <span class="label-text font-medium">Vergi No / TCKN <span
                                            class="text-error">*</span></span>
                                </label>
                                <input type="text" id="tax_number" name="tax_number"
                                    value="{{ old('tax_number', $dealer->tax_number) }}"
                                    class="input input-bordered input-md w-full @error('tax_number') input-error @enderror"
                                    placeholder="11111111111" inputmode="numeric" required />
                                @error('tax_number')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-control">
                                <label for="city" class="label">
                                    <span class="label-text font-medium">İl <span class="text-error">*</span></span>
                                </label>
                                <input type="text" id="city" name="city"
                                    value="{{ old('city', $dealer->city) }}"
                                    class="input input-bordered input-md w-full @error('city') input-error @enderror"
                                    placeholder="Örn: İstanbul" list="tr_city_list" required />
                                @error('city')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control">
                                <label for="district" class="label">
                                    <span class="label-text font-medium">İlçe <span class="text-error">*</span></span>
                                </label>
                                <input type="text" id="district" name="district"
                                    value="{{ old('district', $dealer->district) }}"
                                    class="input input-bordered input-md w-full @error('district') input-error @enderror"
                                    placeholder="Örn: Kadıköy" list="tr_district_list" required />
                                @error('district')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="form-control">
                            <label for="address" class="label">
                                <span class="label-text font-medium">Adres <span class="text-error">*</span></span>
                            </label>
                            <div class="textarea textarea-md w-full min-h-28 @error('address') textarea-error @enderror">
                                <textarea id="address" name="address" rows="5" class="resize-y" placeholder="Açık adres..." required>{{ old('address', $dealer->address) }}</textarea>
                            </div>
                            @error('address')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- Durum --}}
                <section class="admin-form-section">
                    <h2
                        class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                        @svg('heroicon-o-adjustments-horizontal', 'h-4 w-4')
                        Durum
                    </h2>
                    <div class="alert alert-info mb-4">
                        @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                        <div>
                            <p class="font-medium">Onay Bekleyen / Aktif / Pasif</p>
                            <p class="text-sm opacity-90">Aktif bayiler panele giriş yapabilir. Pasif bayiler erişemez.
                                Onay bekleyen başvurular henüz değerlendirilmemiştir.</p>
                        </div>
                    </div>
                    <div class="form-control">
                        <label for="status" class="label">
                            <span class="label-text font-medium">Durum</span>
                        </label>
                        <select name="status" id="status"
                            class="select select-md w-full @error('status') select-error @enderror">
                            <option value="pending" {{ old('status', $dealer->status) === 'pending' ? 'selected' : '' }}>
                                Onay Bekleyen</option>
                            <option value="active" {{ old('status', $dealer->status) === 'active' ? 'selected' : '' }}>
                                Aktif</option>
                            <option value="passive" {{ old('status', $dealer->status) === 'passive' ? 'selected' : '' }}>
                                Pasif</option>
                        </select>
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- İşlemler --}}
                <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
                    <button type="submit" class="btn btn-primary gap-2">
                        @svg('heroicon-o-check', 'h-4 w-4')
                        Değişiklikleri Kaydet
                    </button>
                    <a href="{{ route('admin.dealers.index') }}" class="btn btn-ghost">İptal</a>
                </div>
            </form>
        </div>

        {{-- Sağ: Bayi Bilgileri --}}
        <div class="lg:col-span-1">
            <div class="admin-form-section sticky top-20">
                <h2
                    class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                    @svg('heroicon-o-information-circle', 'h-4 w-4')
                    Bayi Bilgileri
                </h2>
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Kayıt Tarihi</div>
                        <div class="text-sm font-medium">
                            {{ $dealer->created_at ? $dealer->created_at->format('d.m.Y H:i') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Son Güncelleme</div>
                        <div class="text-sm font-medium">
                            {{ $dealer->updated_at ? $dealer->updated_at->format('d.m.Y H:i') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">E-posta Doğrulama</div>
                        <div class="text-sm font-medium">
                            @if ($dealer->email_verified_at)
                                <span class="badge badge-success badge-sm text-success-content">Doğrulandı</span>
                                <div class="text-xs text-base-content/60 mt-1">
                                    {{ $dealer->email_verified_at->format('d.m.Y H:i') }}
                                </div>
                            @else
                                <span class="badge badge-warning badge-sm text-warning-content">Doğrulanmadı</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Durum</div>
                        <div class="text-sm font-medium">
                            @php
                                $statusLabel =
                                    ['pending' => 'Onay Bekliyor', 'active' => 'Aktif', 'passive' => 'Pasif'][
                                        $dealer->status
                                    ] ?? $dealer->status;
                                $statusClass = match ($dealer->status) {
                                    'active' => 'badge-success text-success-content',
                                    'pending' => 'badge-warning text-warning-content',
                                    'passive' => 'badge-error text-error-content',
                                    default => 'badge-ghost',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} badge-sm">{{ $statusLabel }}</span>
                        </div>
                    </div>
                    @if ($dealer->group)
                        <div>
                            <div class="text-xs text-base-content/60 mb-1">Bayi Grubu</div>
                            <div class="text-sm font-medium">{{ $dealer->group->name }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Telefon</div>
                        <div class="text-sm font-medium">
                            @php
                                $formattedPhone = '—';
                                if ($dealer->phone) {
                                    $digits = preg_replace('/\D+/', '', $dealer->phone);
                                    if (str_starts_with($digits, '5')) {
                                        $digits = '0' . $digits;
                                    }
                                    $digits = substr($digits, 0, 11);
                                    if (strlen($digits) === 11) {
                                        $p1 = substr($digits, 0, 4);
                                        $p2 = substr($digits, 4, 3);
                                        $p3 = substr($digits, 7, 2);
                                        $p4 = substr($digits, 9, 2);
                                        $formattedPhone = trim("$p1 $p2 $p3 $p4");
                                    } else {
                                        $formattedPhone = $dealer->phone;
                                    }
                                }
                            @endphp
                            {{ $formattedPhone }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Vergi No / TCKN</div>
                        <div class="text-sm font-medium">{{ $dealer->tax_number ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Konum</div>
                        <div class="text-sm font-medium">
                            @if ($dealer->city || $dealer->district)
                                {{ $dealer->city }}{{ $dealer->district ? ' / ' . $dealer->district : '' }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- İl/İlçe datasource (register-form.js için) --}}
    <datalist id="tr_city_list"></datalist>
    <datalist id="tr_district_list"></datalist>
@endsection

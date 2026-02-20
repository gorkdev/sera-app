@extends('layouts.admin')

@section('title', 'Stok Düzenle — ' . $stock->product->name)

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.stocks.index', ['partyId' => $stock->party_id]) }}"
                class="hover:text-base-content">Stoklar</a>
            <span>/</span>
            <span class="text-base-content">{{ $stock->product->name }}</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Stok Düzenle</h1>
                <p class="mt-1 text-sm text-base-content/60">
                    Parti: <span class="font-medium">{{ $stock->party->name }}</span>
                    · Ürün: <span class="font-medium">{{ $stock->product->name }}</span>
                </p>
            </div>
            <a href="{{ route('admin.stocks.index', ['partyId' => $stock->party_id]) }}"
                class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.stocks.update', $stock) }}" class="admin-form space-y-6 max-w-3xl">
        @csrf
        @method('PUT')

        {{-- Stok Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-document-text', 'h-4 w-4')
                Stok Miktarları ve Lokasyon
            </h2>
            <div class="alert alert-info mb-4">
                @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
                <div>
                    <p class="font-medium">Stok Yönetimi</p>
                    <p class="text-sm opacity-90">Toplam stok miktarını güncelleyebilirsiniz. Rezerve ve satılan miktarlar
                        sistem tarafından otomatik yönetilir ve değiştirilemez.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="form-control">
                    <label for="location" class="label">
                        <span class="label-text font-medium">Lokasyon</span>
                    </label>
                    <input type="text" id="location" name="location" value="{{ old('location', $stock->location) }}"
                        class="input input-bordered input-md w-full @error('location') input-error @enderror"
                        placeholder="Örn: Sera A Bölgesi, 4. Raf" />
                    @error('location')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Rezerve Edilmiş</span>
                        </label>
                        <div class="input input-bordered input-md w-full bg-base-200" readonly>
                            {{ number_format($stock->reserved_quantity, 0, ',', '.') }}
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-warning">Sepette rezerve edilmiş miktar</span>
                        </label>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Satılan</span>
                        </label>
                        <div class="input input-bordered input-md w-full bg-base-200" readonly>
                            {{ number_format($stock->sold_quantity, 0, ',', '.') }}
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-success">Siparişlerde satılan miktar</span>
                        </label>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Çöp</span>
                        </label>
                        <div class="input input-bordered input-md w-full bg-base-200" readonly>
                            {{ number_format($stock->waste_quantity, 0, ',', '.') }}
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-error">Çöpe atılan/bozulan miktar</span>
                        </label>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="total_quantity" class="label">
                            <span class="label-text font-medium">Toplam Stok Miktarı <span
                                    class="text-error">*</span></span>
                        </label>
                        <input type="number" id="total_quantity" name="total_quantity"
                            value="{{ old('total_quantity', $stock->total_quantity) }}"
                            class="input input-bordered input-md w-full @error('total_quantity') input-error @enderror"
                            min="{{ $stock->reserved_quantity + $stock->sold_quantity + $stock->waste_quantity }}"
                            step="1" required />
                        <label class="label">
                            <span class="label-text-alt">
                                Minimum:
                                {{ number_format($stock->reserved_quantity + $stock->sold_quantity + $stock->waste_quantity, 0, ',', '.') }}
                                (Rezerve + Satılan + Çöp)
                            </span>
                        </label>
                        @error('total_quantity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="waste_quantity" class="label">
                            <span class="label-text font-medium">Çöp Miktarı <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="waste_quantity" name="waste_quantity"
                            value="{{ old('waste_quantity', $stock->waste_quantity) }}"
                            class="input input-bordered input-md w-full @error('waste_quantity') input-error @enderror"
                            min="0" step="1" required />
                        <label class="label">
                            <span class="label-text-alt text-error">Çöpe atılan/bozulan çiçek miktarı</span>
                        </label>
                        @error('waste_quantity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Mevcut Stok</span>
                    </label>
                    <div class="input input-bordered input-md w-full bg-base-200" readonly>
                        <span class="{{ $stock->available_quantity > 0 ? 'text-success font-medium' : 'text-error' }}">
                            {{ number_format($stock->available_quantity, 0, ',', '.') }}
                        </span>
                    </div>
                    <label class="label">
                        <span class="label-text-alt">Mevcut = Toplam - Rezerve - Satılan - Çöp</span>
                    </label>
                </div>
            </div>
        </section>

        {{-- Parti ve Tazelik Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-information-circle', 'h-4 w-4')
                Parti ve Tazelik Bilgileri
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-base-content/60 w-32">Parti:</span>
                    <span class="font-medium">{{ $stock->party->name }}</span>
                    @if ($stock->party->party_code)
                        <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $stock->party->party_code }}</code>
                    @endif
                </div>
                @if ($stock->party->arrived_at)
                    <div class="flex items-center gap-2">
                        <span class="text-base-content/60 w-32">Varış Tarihi:</span>
                        <span>{{ formatliTarih($stock->party->arrived_at) }}</span>
                        @if ($stock->party->journey_days)
                            <span class="text-base-content/50">({{ $stock->party->journey_days }} gün yolculuk)</span>
                        @endif
                    </div>
                @endif
                @php
                    $freshness = $stock->freshness_score ?? $stock->calculateFreshnessScore();
                @endphp
                @if ($freshness !== null)
                    <div class="flex items-center gap-2">
                        <span class="text-base-content/60 w-32">Tazelik Skoru:</span>
                        <span
                            class="font-medium {{ $freshness >= 80 ? 'text-success' : ($freshness >= 50 ? 'text-warning' : 'text-error') }}">
                            {{ number_format($freshness, 1) }}%
                        </span>

                    </div>
                @endif
            </div>
        </section>

        {{-- Ürün Bilgileri --}}
        <section class="admin-form-section">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 my-4 flex items-center gap-2">
                @svg('heroicon-o-cube', 'h-4 w-4')
                Ürün Bilgileri
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-base-content/60 w-32">Ürün:</span>
                    <span class="font-medium">{{ $stock->product->name }}</span>
                </div>
                @if ($stock->product->sku)
                    <div class="flex items-center gap-2">
                        <span class="text-base-content/60 w-32">SKU:</span>
                        <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $stock->product->sku }}</code>
                    </div>
                @endif
                @if ($stock->product->category)
                    <div class="flex items-center gap-2">
                        <span class="text-base-content/60 w-32">Kategori:</span>
                        <span>{{ $stock->product->category->name }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <span class="text-base-content/60 w-32">Fiyat:</span>
                    <span>{{ $stock->product->formatted_price }}</span>
                </div>
            </div>
        </section>

        {{-- Zayiat Logları --}}
        <section class="admin-form-section">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-base-content/70 flex items-center gap-2">
                    @svg('heroicon-o-trash', 'h-4 w-4')
                    Zayiat Kayıtları
                </h2>
                <button type="button" onclick="document.getElementById('add-waste-modal').showModal()"
                    class="btn btn-sm btn-error gap-2">
                    @svg('heroicon-o-plus', 'h-4 w-4')
                    Zayiat Ekle
                </button>
            </div>

            @if ($stock->wasteLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Tip</th>
                                <th class="text-right">Miktar</th>
                                <th class="hidden md:table-cell">Parti Gelişinden</th>
                                <th class="hidden lg:table-cell">Kaydeden</th>
                                <th class="w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stock->wasteLogs->sortByDesc('waste_date') as $log)
                                <tr>
                                    <td>
                                        {{ formatliTarih($log->waste_date) }}
                                        @if ($log->waste_date instanceof \Carbon\Carbon && $log->waste_date->format('H:i') !== '00:00')
                                            <span class="text-xs text-base-content/50 ml-1">
                                                {{ $log->waste_date->format('H:i') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-error badge-sm">
                                            {{ $log->waste_type_label }}
                                        </span>
                                    </td>
                                    <td class="text-right font-medium">{{ number_format($log->quantity, 0, ',', '.') }}
                                    </td>
                                    <td class="hidden md:table-cell text-sm text-base-content/70">
                                        @if ($log->days_since_party_arrival !== null)
                                            {{ $log->days_since_party_arrival }} gün sonra
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="hidden lg:table-cell text-sm text-base-content/70">
                                        {{ $log->recordedByAdmin->name ?? '—' }}
                                    </td>
                                    <td class="text-right">
                                        <button type="button"
                                            onclick="confirmDeleteWaste({{ $log->id }}, {{ $log->quantity }}, '{{ $log->waste_type_label }}')"
                                            class="btn btn-xs btn-error btn-ghost gap-1" title="Zayiat Kaydını Sil">
                                            @svg('heroicon-o-trash', 'h-4 w-4')
                                        </button>
                                    </td>
                                </tr>
                                @if ($log->notes)
                                    <tr>
                                        <td colspan="6" class="text-xs text-base-content/50 pl-8">
                                            <em>{{ $log->notes }}</em>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-base-content/50">
                    <p>Henüz zayiat kaydı yok.</p>
                </div>
            @endif
        </section>

        {{-- İşlemler --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-base-300">
            <button type="submit" class="btn btn-primary gap-2">
                @svg('heroicon-o-check', 'h-4 w-4')
                Değişiklikleri Kaydet
            </button>
            <a href="{{ route('admin.stocks.index', ['partyId' => $stock->party_id]) }}" class="btn btn-ghost">İptal</a>
        </div>
    </form>

    {{-- Zayiat Ekleme Modal --}}
    <dialog id="add-waste-modal" class="modal">
        <div class="modal-box max-w-2xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-6">Yeni Zayiat Kaydı</h3>
            <form method="POST" action="{{ route('admin.stocks.add-waste', $stock) }}" class="space-y-4">
                @csrf
                <div class="form-control">
                    <label for="waste_type" class="label">
                        <span class="label-text font-medium">Zayiat Tipi <span class="text-error">*</span></span>
                    </label>
                    <select name="waste_type" id="waste_type"
                        class="select select-bordered select-md w-full @error('waste_type') select-error @enderror"
                        required>
                        <option value="pest">Böceklenme</option>
                        <option value="fungus">Mantar</option>
                        <option value="dehydration">Susuzluk</option>
                        <option value="breakage">Kırılma</option>
                        <option value="expired" selected>Raf Ömrü Sonu</option>
                    </select>
                    @error('waste_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-control">
                        <label for="waste_quantity" class="label">
                            <span class="label-text font-medium">Miktar <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="waste_quantity" name="quantity" value="{{ old('quantity') }}"
                            class="input input-bordered input-md w-full @error('quantity') input-error @enderror"
                            min="1" step="1" required />
                        @error('quantity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="waste_date" class="label">
                            <span class="label-text font-medium">Zayiat Tarihi <span class="text-error">*</span></span>
                        </label>
                        <input type="date" id="waste_date" name="waste_date"
                            value="{{ old('waste_date', now()->format('Y-m-d')) }}"
                            class="input input-bordered input-md w-full @error('waste_date') input-error @enderror"
                            required />
                        @error('waste_date')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label for="waste_time" class="label">
                        <span class="label-text font-medium">Zayiat Saati</span>
                    </label>
                    <input type="time" id="waste_time" name="waste_time"
                        value="{{ old('waste_time', now()->format('H:i')) }}"
                        class="input input-bordered input-md w-full @error('waste_time') input-error @enderror" />
                    <label class="label">
                        <span class="label-text-alt">Boş bırakılırsa şu anki saat kullanılır</span>
                    </label>
                    @error('waste_time')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="waste_notes" class="label">
                        <span class="label-text font-medium">Notlar</span>
                    </label>
                    <textarea name="notes" id="waste_notes" rows="3"
                        class="textarea textarea-bordered textarea-md w-full @error('notes') textarea-error @enderror"
                        placeholder="Ek bilgiler...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="modal-action mt-6">
                    <button type="button" onclick="document.getElementById('add-waste-modal').close()"
                        class="btn btn-ghost">İptal</button>
                    <button type="submit" class="btn btn-error gap-2">
                        @svg('heroicon-o-check', 'h-4 w-4')
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    {{-- Zayiat Silme Onay Modal --}}
    <dialog id="confirm_delete_waste_modal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                @svg('heroicon-o-exclamation-triangle', 'h-5 w-5 text-warning')
                <span id="confirm_delete_waste_title">Zayiat Kaydı Silme Onayı</span>
            </h3>
            <p class="py-4 text-base-content/70" id="confirm_delete_waste_message">
                Bu zayiat kaydını silmek istediğinize emin misiniz?
            </p>
            <div class="modal-action">
                <form method="dialog" class="flex items-center gap-2">
                    <button class="btn btn-ghost">Vazgeç</button>
                    <button type="button" class="btn btn-error" id="confirm_delete_waste_yes">
                        Evet, Sil
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    {{-- Zayiat Silme Formu (Hidden) --}}
    <form id="delete-waste-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function confirmDeleteWaste(wasteLogId, quantity, wasteType) {
            const modal = document.getElementById('confirm_delete_waste_modal');
            const title = document.getElementById('confirm_delete_waste_title');
            const message = document.getElementById('confirm_delete_waste_message');
            const yesBtn = document.getElementById('confirm_delete_waste_yes');

            title.textContent = 'Zayiat Kaydı Silme Onayı';
            message.innerHTML = `
                <div class="space-y-2">
                    <p><strong>Zayiat Tipi:</strong> ${wasteType}</p>
                    <p><strong>Miktar:</strong> ${quantity} adet</p>
                    <p class="mt-3">Bu zayiat kaydını silmek istediğinize emin misiniz? Stok miktarı güncellenecektir.</p>
                </div>
            `;

            yesBtn.onclick = function() {
                const form = document.getElementById('delete-waste-form');
                form.action = '{{ route('admin.stocks.delete-waste', ':id') }}'.replace(':id', wasteLogId);
                form.submit();
            };

            modal.showModal();
        }
    </script>
@endsection

<div>
    <div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Stoklar</h1>
            <p class="mt-1 text-sm text-base-content/60">Parti bazlı stok yönetimi. Ürünlerin parti bazında stok miktarlarını yönetin.</p>
        </div>
        <a href="{{ route('admin.stocks.create', ['party_id' => $partyId]) }}" class="btn btn-primary gap-2 shrink-0">
            @svg('heroicon-o-plus', 'h-4 w-4')
            Stok Ekle
        </a>
    </div>

    {{-- Filtreler --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
        <select wire:model.live="partyId" class="select select-bordered select-sm w-64">
            <option value="">Tüm partiler</option>
            @foreach($parties as $party)
                <option value="{{ $party->id }}">
                    {{ $party->name }}
                    @if($party->isActive())
                        <span class="text-success">(Aktif)</span>
                    @elseif($party->isDraft())
                        <span class="text-warning">(Taslak)</span>
                    @else
                        <span class="text-error">(Kapalı)</span>
                    @endif
                </option>
            @endforeach
        </select>

        <input type="text" wire:model.live.debounce.400ms="q" class="input input-bordered input-sm w-48"
            placeholder="Ürün adı veya SKU..." />

        <button type="button" wire:click="resetFilters" class="btn btn-warning btn-sm gap-2"
            @disabled($partyId === null && trim($q) === '')>
            @svg('heroicon-o-arrow-path', 'h-4 w-4')
            Sıfırla
        </button>

        <span class="text-xs text-base-content/60 ml-auto" wire:loading>
            Yükleniyor...
        </span>
    </div>

    @if($selectedParty)
        <div class="alert alert-info mb-4">
            @svg('heroicon-o-information-circle', 'h-5 w-5 shrink-0')
            <div>
                <p class="font-medium">{{ $selectedParty->name }} partisi için stoklar</p>
                <p class="text-sm opacity-90">Durum: 
                    @if($selectedParty->isActive())
                        <span class="badge badge-success badge-sm">Aktif</span>
                    @elseif($selectedParty->isDraft())
                        <span class="badge badge-warning badge-sm">Taslak</span>
                    @else
                        <span class="badge badge-error badge-sm">Kapalı</span>
                    @endif
                </p>
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-base-300 bg-base-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Parti</th>
                        <th>Ürün</th>
                        <th class="hidden md:table-cell">Lokasyon</th>
                        <th class="text-right">Toplam</th>
                        <th class="text-right">Rezerve</th>
                        <th class="text-right">Satılan</th>
                        <th class="text-right">Çöp</th>
                        <th class="text-right">Mevcut</th>
                        <th class="hidden lg:table-cell text-right">Tazelik</th>
                        <th class="w-24 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr class="hover" wire:key="s-{{ $stock->id }}">
                            <td>
                                <div class="font-medium">
                                    <span class="tooltip tooltip-right" data-tip="{{ $stock->party->name }}{{ $stock->party->description ? ' - ' . \Illuminate\Support\Str::limit($stock->party->description, 50) : '' }}{{ $stock->party->supplier_name ? ' | Tedarikçi: ' . $stock->party->supplier_name : '' }}{{ $stock->party->arrived_at ? ' | Varış: ' . formatliTarih($stock->party->arrived_at) : '' }}{{ $stock->party->journey_days ? ' | Yolculuk: ' . $stock->party->journey_days . ' gün' : '' }}">
                                        {{ \Illuminate\Support\Str::limit($stock->party->name, 20) }}
                                    </span>
                                </div>
                                @if($stock->party->party_code)
                                    <div class="text-xs text-base-content/50 mt-0.5">
                                        <code class="bg-base-200 px-1 rounded">{{ $stock->party->party_code }}</code>
                                    </div>
                                @endif
                                @if($stock->party->arrived_at)
                                    <div class="text-xs text-base-content/50 mt-0.5">
                                        {{ formatliTarih($stock->party->arrived_at) }}
                                        @if($stock->party->journey_days)
                                            <span class="text-base-content/40">({{ $stock->party->journey_days }}g)</span>
                                        @endif
                                    </div>
                                @endif
                                @php
                                    $partyStatusClass = match ($stock->party->status) {
                                        'active' => 'badge-success badge-sm',
                                        'draft' => 'badge-warning badge-sm',
                                        'closed' => 'badge-error badge-sm',
                                        default => 'badge-ghost badge-sm',
                                    };
                                @endphp
                                <span class="badge {{ $partyStatusClass }} mt-1">
                                    {{ ['active' => 'Aktif', 'draft' => 'Taslak', 'closed' => 'Kapalı'][$stock->party->status] ?? $stock->party->status }}
                                </span>
                            </td>
                            <td>
                                <div class="font-medium">{{ $stock->product->name }}</div>
                                @if($stock->product->sku)
                                    <div class="text-xs text-base-content/50 mt-0.5">
                                        SKU: <code class="bg-base-200 px-1 rounded">{{ $stock->product->sku }}</code>
                                    </div>
                                @endif
                                @if($stock->product->category)
                                    <div class="text-xs text-base-content/40 mt-0.5">
                                        {{ $stock->product->category->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="hidden md:table-cell text-sm text-base-content/70">
                                {{ $stock->location ?? '—' }}
                            </td>
                            <td class="text-right">
                                <span class="font-medium">{{ number_format($stock->total_quantity, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right">
                                <span class="text-warning">{{ number_format($stock->reserved_quantity, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right">
                                <span class="text-success">{{ number_format($stock->sold_quantity, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right">
                                <span class="text-error">{{ number_format($stock->waste_quantity, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right">
                                @php
                                    $available = $stock->available_quantity;
                                    $availableClass = $available > 0 ? 'text-success font-medium' : 'text-error';
                                @endphp
                                <span class="{{ $availableClass }}">
                                    {{ number_format($available, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-right">
                                @php
                                    $freshness = $stock->freshness_score ?? $stock->calculateFreshnessScore();
                                    $freshnessClass = $freshness >= 80 ? 'text-success' : ($freshness >= 50 ? 'text-warning' : 'text-error');
                                @endphp
                                @if($freshness !== null)
                                    <span class="{{ $freshnessClass }} font-medium">{{ number_format($freshness, 0) }}%</span>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.stocks.edit', $stock) }}"
                                        class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                        @svg('heroicon-o-pencil-square', 'h-4 w-4')
                                    </a>
                                    @if($stock->reserved_quantity === 0 && $stock->sold_quantity === 0)
                                        <button type="button"
                                            onclick="confirmDelete('{{ route('admin.stocks.destroy', $stock) }}', '{{ $stock->product->name }}')"
                                            class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10"
                                            title="Sil">
                                            @svg('heroicon-o-trash', 'h-4 w-4')
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-base-content/60">
                                    @svg('heroicon-o-square-3-stack-3d', 'h-12 w-12 opacity-40')
                                    <p class="font-medium">
                                        @if($selectedParty)
                                            Bu parti için henüz stok tanımlanmamış
                                        @else
                                            Henüz stok kaydı yok
                                        @endif
                                    </p>
                                    @if($selectedParty)
                                        <a href="{{ route('admin.stocks.create', ['party_id' => $selectedParty->id]) }}" class="btn btn-primary btn-sm gap-2">
                                            @svg('heroicon-o-plus', 'h-4 w-4')
                                            Stok Ekle
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stocks->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function confirmDelete(url, name) {
    const modal = document.getElementById('confirm_delete_modal');
    const title = document.getElementById('confirm_delete_title');
    const message = document.getElementById('confirm_delete_message');
    const yesBtn = document.getElementById('confirm_delete_yes');
    
    title.textContent = 'Stok Silme Onayı';
    message.textContent = `"${name}" ürününün stok kaydını silmek istediğinize emin misiniz?`;
    
    yesBtn.onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    };
    
    modal.showModal();
}
</script>

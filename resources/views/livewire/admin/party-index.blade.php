<div>
    <div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Partiler</h1>
            <p class="mt-1 text-sm text-base-content/60">Parti bazlı satış sezonlarını yönetin. Başlangıç tarihinde otomatik sipariş almaya başlar, bitiş tarihinde veya stok bitince otomatik kapanır.</p>
        </div>
        <a href="{{ route('admin.parties.create') }}" class="btn btn-primary gap-2 shrink-0">
            @svg('heroicon-o-plus', 'h-4 w-4')
            Yeni Parti
        </a>
    </div>

    {{-- Filtreler --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
        <select wire:model.live="status" class="select select-bordered select-sm w-48">
            <option value="">Tüm durumlar</option>
            <option value="draft">Taslak</option>
            <option value="active">Aktif</option>
            <option value="closed">Kapalı</option>
        </select>

        <input type="text" wire:model.live.debounce.400ms="q" class="input input-bordered input-sm w-48"
            placeholder="Parti adı veya açıklama..." />

        <button type="button" wire:click="resetFilters" class="btn btn-warning btn-sm gap-2"
            @disabled($status === '' && trim($q) === '')>
            @svg('heroicon-o-arrow-path', 'h-4 w-4')
            Sıfırla
        </button>

        <span class="text-xs text-base-content/60 ml-auto" wire:loading>
            Yükleniyor...
        </span>
    </div>

    <div class="rounded-xl border border-base-300 bg-base-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Parti</th>
                        <th class="hidden md:table-cell">Açıklama</th>
                        <th class="w-24">Durum</th>
                        <th class="hidden lg:table-cell w-32">Başlangıç</th>
                        <th class="hidden xl:table-cell w-32">Bitiş</th>
                        <th class="hidden xl:table-cell w-28">Tedarikçi</th>
                        <th class="w-32 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parties as $party)
                        <tr class="hover" wire:key="p-{{ $party->id }}">
                            <td>
                                <div class="font-medium">{{ $party->name }}</div>
                                @if($party->party_code)
                                    <div class="text-xs text-base-content/50 mt-0.5">
                                        <code class="bg-base-200 px-1 rounded">{{ $party->party_code }}</code>
                                    </div>
                                @endif
                                @if($party->description)
                                    <div class="text-sm text-base-content/50 mt-0.5 line-clamp-1 max-w-xs hidden md:block">
                                        {{ Str::limit($party->description, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td class="hidden md:table-cell">
                                @if($party->description)
                                    <span class="text-sm text-base-content/70">{{ Str::limit($party->description, 80) }}</span>
                                @else
                                    <span class="text-sm text-base-content/40">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusLabel = [
                                        'draft' => 'Taslak',
                                        'active' => 'Aktif',
                                        'closed' => 'Kapalı',
                                    ][$party->status] ?? $party->status;
                                    $statusClass = match ($party->status) {
                                        'active' => 'badge-success text-success-content',
                                        'draft' => 'badge-warning text-warning-content',
                                        'closed' => 'badge-error text-error-content',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} badge-sm">{{ $statusLabel }}</span>
                            </td>
                            <td class="hidden lg:table-cell text-sm text-base-content/70">
                                @if($party->starts_at)
                                    {{ formatliTarih($party->starts_at) }}
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="hidden xl:table-cell text-sm text-base-content/70">
                                @if($party->ends_at)
                                    {{ formatliTarih($party->ends_at) }}
                                @elseif($party->close_when_stock_runs_out)
                                    <span class="text-xs text-base-content/50">Stok bitene kadar</span>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="hidden xl:table-cell text-sm text-base-content/70">
                                {{ $party->supplier_name ?? '—' }}
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    @if($party->isDraft())
                                        <a href="{{ route('admin.parties.edit', $party) }}"
                                            class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                            @svg('heroicon-o-pencil-square', 'h-4 w-4')
                                        </a>
                                        @if($hasActiveParty)
                                            <span class="tooltip tooltip-left" data-tip="Önce mevcut partiyi kapatın. Aynı anda tek parti açık olabilir.">
                                                <button type="button" class="btn btn-ghost btn-sm btn-square text-success/50 cursor-not-allowed" disabled
                                                    title="Önce mevcut partiyi kapatın">
                                                    @svg('heroicon-o-check-circle', 'h-4 w-4')
                                                </button>
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.parties.activate', $party) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-sm btn-square text-success hover:bg-success/10"
                                                    title="Aktif Et">
                                                    @svg('heroicon-o-check-circle', 'h-4 w-4')
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    @if($party->isActive())
                                        <form method="POST" action="{{ route('admin.parties.close', $party) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10"
                                                title="Kapat">
                                                @svg('heroicon-o-x-circle', 'h-4 w-4')
                                            </button>
                                        </form>
                                    @endif
                                    @if($party->isDraft())
                                        <button type="button"
                                            onclick="confirmDelete('{{ route('admin.parties.destroy', $party) }}', '{{ $party->name }}')"
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
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-base-content/60">
                                    @svg('heroicon-o-calendar-days', 'h-12 w-12 opacity-40')
                                    <p class="font-medium">Henüz parti yok</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($parties->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $parties->links() }}
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
    
    title.textContent = 'Parti Silme Onayı';
    message.textContent = `"${name}" partisini silmek istediğinize emin misiniz?`;
    
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

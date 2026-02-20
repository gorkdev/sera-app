<div>
    <div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Bayi Grupları</h1>
            <p class="mt-1 text-sm text-base-content/60">Bayi gruplarını yönetin. Gruplar stok erişim zamanlamasını kontrol eder.</p>
        </div>
        <a href="{{ route('admin.groups.create') }}" class="btn btn-primary gap-2 shrink-0">
            @svg('heroicon-o-plus', 'h-4 w-4')
            Yeni Grup
        </a>
    </div>

    {{-- Arama --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
        <input type="text" wire:model.live.debounce.400ms="q" class="input input-bordered input-sm w-48"
            placeholder="Grup adı veya kodu..." />

        <button type="button" wire:click="resetFilters" class="btn btn-warning btn-sm gap-2"
            @disabled(trim($q) === '')>
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
                        <th>Sıra</th>
                        <th>Grup Adı</th>
                        <th>Kod</th>
                        <th>Gecikme Süresi</th>
                        <th>Bayi Sayısı</th>
                        <th>Durum</th>
                        <th class="w-24 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr class="hover" wire:key="g-{{ $group->id }}">
                            <td>
                                <span class="text-sm text-base-content/70">{{ $group->sort_order }}</span>
                            </td>
                            <td>
                                <div class="font-medium">{{ $group->name }}</div>
                            </td>
                            <td>
                                <code class="text-xs bg-base-200 px-1.5 py-0.5 rounded">{{ $group->code }}</code>
                            </td>
                            <td>
                                <span class="text-sm">
                                    @if($group->delay_minutes === 0)
                                        <span class="badge badge-success badge-sm">Anında</span>
                                    @else
                                        {{ $group->delay_minutes }} dakika
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="text-sm">{{ $group->dealers_count }}</span>
                            </td>
                            <td>
                                @if($group->is_default)
                                    <span class="badge badge-primary badge-sm">Varsayılan</span>
                                @else
                                    <span class="badge badge-ghost badge-sm">Normal</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.groups.edit', $group) }}"
                                        class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                        @svg('heroicon-o-pencil-square', 'h-4 w-4')
                                    </a>
                                    @if(!$group->is_default && $group->dealers_count === 0)
                                        <button type="button" 
                                            onclick="confirmDelete('{{ route('admin.groups.destroy', $group) }}', '{{ $group->name }}')"
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
                                    @svg('heroicon-o-user-group', 'h-12 w-12 opacity-40')
                                    <p class="font-medium">Henüz grup yok</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($groups->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $groups->links() }}
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
    
    title.textContent = 'Grup Silme Onayı';
    message.textContent = `"${name}" grubunu silmek istediğinize emin misiniz?`;
    
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

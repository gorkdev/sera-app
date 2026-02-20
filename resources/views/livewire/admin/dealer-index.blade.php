<div>
    <div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Bayiler</h1>
            <p class="mt-1 text-sm text-base-content/60">Bayi üyeliklerini, bilgilerini ve onay durumlarını yönetin</p>
        </div>
    </div>

    {{-- Filtreler (Livewire) --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
        <select wire:model.live="status" class="select select-bordered select-sm w-48">
            <option value="">Tüm durumlar</option>
            <option value="pending">Onay Bekleyen</option>
            <option value="active">Aktif</option>
            <option value="passive">Pasif</option>
        </select>

        <input type="text" wire:model.live.debounce.400ms="q" class="input input-bordered input-sm w-48"
            placeholder="Şirket, yetkili, e-posta..." />

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
                        <th>Şirket</th>
                        <th class="hidden sm:table-cell">Yetkili</th>
                        <th class="hidden md:table-cell">E-posta</th>
                        <th class="hidden lg:table-cell">Telefon</th>
                        <th class="w-20">Durum</th>
                        <th class="w-24">E-Posta</th>
                        <th class="hidden lg:table-cell w-24">Grup</th>
                        <th class="hidden xl:table-cell w-28">Kayıt</th>
                        <th class="w-24 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dealers as $dealer)
                        <tr class="hover" wire:key="d-{{ $dealer->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded border border-base-300 bg-base-200 flex items-center justify-center shrink-0">
                                        @svg('heroicon-o-building-office-2', 'h-5 w-5 text-base-content/50')
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $dealer->company_name }}</div>
                                        @if ($dealer->city)
                                            <div class="text-sm text-base-content/50 mt-0.5 line-clamp-1 max-w-xs">
                                                {{ $dealer->city }}{{ $dealer->district ? ' / ' . $dealer->district : '' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="text-sm">{{ $dealer->contact_name }}</span>
                            </td>
                            <td class="hidden md:table-cell">
                                @if ($dealer->email)
                                    <span class="text-sm cursor-pointer hover:text-primary transition-colors"
                                        data-copy-text="{{ $dealer->email }}"
                                        title="Kopyalamak için tıklayın">{{ $dealer->email }}</span>
                                @else
                                    <span class="text-sm">—</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell">
                                @php
                                    $formattedPhone = '—';
                                    $phoneValue = '';
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
                                            $phoneValue = $formattedPhone;
                                        } else {
                                            $formattedPhone = $dealer->phone;
                                            $phoneValue = $dealer->phone;
                                        }
                                    }
                                @endphp
                                @if ($phoneValue)
                                    <code
                                        class="text-xs bg-base-200 px-1.5 py-0.5 rounded cursor-pointer hover:bg-base-300 transition-colors"
                                        data-copy-text="{{ $phoneValue }}"
                                        title="Kopyalamak için tıklayın">{{ $formattedPhone }}</code>
                                @else
                                    <code class="text-xs bg-base-200 px-1.5 py-0.5 rounded">—</code>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusLabel =
                                        ['pending' => 'Bekliyor', 'active' => 'Aktif', 'passive' => 'Pasif'][
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
                            </td>
                            <td>
                                @if ($dealer->email_verified_at)
                                    <span class="badge badge-success badge-sm text-success-content">Doğrulandı</span>
                                @else
                                    <span class="badge badge-warning badge-sm text-warning-content">Bekliyor</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell text-sm text-base-content/70">
                                {{ optional($dealer->group)->name ?? '—' }}
                            </td>
                            <td class="hidden xl:table-cell text-sm text-base-content/70">
                                {{ formatliTarih($dealer->created_at) ?? '—' }}
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.dealers.edit', $dealer) }}"
                                        class="btn btn-ghost btn-sm btn-square" title="Düzenle">
                                        @svg('heroicon-o-pencil-square', 'h-4 w-4')
                                    </a>
                                    @if ($dealer->status === 'active')
                                        {{-- Aktif ise: Reddet/Pasife Al butonu --}}
                                        <button type="button" data-confirm="dealer-status" data-confirm-action="reject"
                                            data-confirm-id="{{ $dealer->id }}" data-confirm-title="Onayı Kaldır"
                                            data-confirm-item="{{ $dealer->company_name }}"
                                            data-confirm-message="bayisinin onayını kaldırıp pasife almak istediğinize emin misiniz?"
                                            data-confirm-method="reject" data-confirm-params="{{ $dealer->id }}"
                                            class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10"
                                            title="Onayı Kaldır / Pasife Al">
                                            @svg('heroicon-o-x-mark', 'h-4 w-4')
                                        </button>
                                    @else
                                        {{-- Pasif/Pending ise: Onayla/Aktif Yap butonu --}}
                                        @if (!$dealer->email_verified_at)
                                            <div class="tooltip tooltip-left" data-tip="E-posta doğrulanmamış">
                                                <button type="button" disabled
                                                    class="btn btn-ghost btn-sm btn-square text-success hover:bg-success/10 opacity-50"
                                                    title="E-posta doğrulanmamış">
                                                    @svg('heroicon-o-check', 'h-4 w-4')
                                                </button>
                                            </div>
                                        @else
                                            <button type="button" data-confirm="dealer-status"
                                                data-confirm-action="approve" data-confirm-id="{{ $dealer->id }}"
                                                data-confirm-title="Bayi Onayı"
                                                data-confirm-item="{{ $dealer->company_name }}"
                                                data-confirm-message="bayiyi aktif hâle getirmek istediğinize emin misiniz?"
                                                data-confirm-method="approve" data-confirm-params="{{ $dealer->id }}"
                                                class="btn btn-ghost btn-sm btn-square text-success hover:bg-success/10"
                                                title="Onayla / Aktif Yap">
                                                @svg('heroicon-o-check', 'h-4 w-4')
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-base-content/60">
                                    @svg('heroicon-o-users', 'h-12 w-12 opacity-40')
                                    <p class="font-medium">Henüz bayi yok</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($dealers->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $dealers->links() }}
            </div>
        @endif
    </div>
</div>

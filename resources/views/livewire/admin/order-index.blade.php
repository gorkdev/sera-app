<div>
    <div class="admin-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Siparişler</h1>
            <p class="mt-1 text-sm text-base-content/60">Tüm siparişleri listeleyin, filtreleyin ve detayları
                görüntüleyin.</p>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl bg-base-100 border border-base-300">
        <select wire:model.live="statusSlug" class="select select-bordered select-sm w-44">
            <option value="">Tüm durumlar</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->slug }}">{{ $s->name }}</option>
            @endforeach
        </select>

        <input type="text" wire:model.live.debounce.400ms="orderNumber" class="input input-bordered input-sm w-44"
            placeholder="Sipariş no..." />

        <input type="text" wire:model.live.debounce.400ms="dealerSearch" class="input input-bordered input-sm w-52"
            placeholder="Bayi adı, yetkili veya e-posta..." />

        <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm w-40"
            placeholder="Başlangıç" title="Başlangıç tarihi" />
        <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm w-40" placeholder="Bitiş"
            title="Bitiş tarihi" />

        <button type="button" wire:click="resetFilters" class="btn btn-warning btn-sm gap-2"
            @disabled($statusSlug === '' && trim($orderNumber) === '' && trim($dealerSearch) === '' && $dateFrom === '' && $dateTo === '')>
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
                        <th>Sipariş No</th>
                        <th class="hidden md:table-cell">Tarih</th>
                        <th>Bayi</th>
                        <th class="hidden lg:table-cell">Parti</th>
                        <th class="w-28">Durum</th>
                        <th class="text-right ">Toplam</th>
                        <th class="w-24 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="hover" wire:key="o-{{ $order->id }}">
                            <td>
                                <code
                                    class="bg-base-200 px-1.5 py-0.5 rounded text-sm font-medium">{{ $order->order_number ?? '-' }}</code>
                            </td>
                            <td class="hidden md:table-cell text-sm text-base-content/70">
                                {{ formatliTarih($order->created_at) ?? '-' }}
                            </td>
                            <td>
                                <div class="font-medium">{{ $order->dealer?->company_name ?? '-' }}</div>
                                @if ($order->dealer)
                                    <div class="text-xs text-base-content/50">
                                        {{ $order->dealer->contact_name ?? $order->dealer->email }}</div>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell text-sm text-base-content/70">
                                {{ $order->party?->name ?? ($order->party?->party_code ?? '—') }}
                            </td>
                            <td>
                                @if ($order->orderStatus)
                                    <span class="badge badge-{{ $order->orderStatus->color ?? 'neutral' }} badge-sm">
                                        {{ $order->orderStatus->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right font-semibold">
                                {{ number_format((float) ($order->total_amount ?? ($order->total ?? 0)), 2, ',', '.') }}
                                ₺
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="btn btn-ghost btn-sm btn-square" title="Detay">
                                    @svg('heroicon-o-eye', 'h-4 w-4')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-base-content/60">
                                    @svg('heroicon-o-document-text', 'h-12 w-12 opacity-40')
                                    <p class="font-medium">Sipariş bulunamadı</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

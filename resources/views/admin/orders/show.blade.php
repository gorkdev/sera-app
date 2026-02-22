@extends('layouts.admin')

@section('title', 'Sipariş #' . ($order->order_number ?? $order->id))

@section('content')
    <div class="admin-page-header mb-6">
        <nav class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('admin.orders.index') }}" class="hover:text-base-content">Siparişler</a>
            <span>/</span>
            <span class="text-base-content">{{ $order->order_number ?? '#' . $order->id }}</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold flex items-center gap-2">
                    <code class="bg-base-200 px-2 py-1 rounded text-lg">{{ $order->order_number ?? '-' }}</code>
                    @if ($order->orderStatus)
                        <span class="badge badge-{{ $order->orderStatus->color ?? 'neutral' }} badge-lg">
                            {{ $order->orderStatus->name }}
                        </span>
                    @endif
                </h1>
                <p class="mt-1 text-sm text-base-content/60">
                    {{ $order->created_at?->format('d.m.Y H:i') ?? '-' }}
                    @if ($order->delivery_type)
                        · {{ $order->delivery_type === 'pickup' ? 'Teslim al' : ($order->delivery_type === 'delivery' ? 'Kargo' : $order->delivery_type) }}
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm gap-2 shrink-0">
                @svg('heroicon-o-chevron-left', 'h-4 w-4')
                Listeye dön
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sol: Bayi + Parti bilgisi --}}
        <div class="space-y-6">
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-base gap-2">
                        @svg('heroicon-o-user', 'h-5 w-5')
                        Bayi
                    </h2>
                    @if ($order->dealer)
                        <div class="space-y-1 text-sm">
                            <p class="font-semibold">{{ $order->dealer->company_name }}</p>
                            <p class="text-base-content/70">{{ $order->dealer->contact_name ?? '-' }}</p>
                            <p><a href="mailto:{{ $order->dealer->email }}" class="link link-hover">{{ $order->dealer->email }}</a></p>
                            @if ($order->dealer->phone)
                                <p>{{ $order->dealer->phone }}</p>
                            @endif
                            @if ($order->dealer->address || $order->dealer->city)
                                <p class="text-base-content/70">{{ trim(implode(', ', array_filter([$order->dealer->city, $order->dealer->district, $order->dealer->address]))) ?: '-' }}</p>
                            @endif
                        </div>
                        <a href="{{ route('admin.dealers.edit', $order->dealer) }}" class="btn btn-ghost btn-sm mt-2 gap-1">
                            @svg('heroicon-o-arrow-top-right-on-square', 'h-4 w-4')
                            Bayi sayfası
                        </a>
                    @else
                        <p class="text-base-content/50">Bayi bilgisi yok.</p>
                    @endif
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-base gap-2">
                        @svg('heroicon-o-calendar-days', 'h-5 w-5')
                        Parti
                    </h2>
                    @if ($order->party)
                        <div class="space-y-1 text-sm">
                            <p class="font-semibold">{{ $order->party->name }}</p>
                            @if ($order->party->party_code)
                                <p class="text-base-content/70"><code class="bg-base-200 px-1 rounded">{{ $order->party->party_code }}</code></p>
                            @endif
                        </div>
                        <a href="{{ route('admin.parties.edit', $order->party) }}" class="btn btn-ghost btn-sm mt-2 gap-1">
                            @svg('heroicon-o-arrow-top-right-on-square', 'h-4 w-4')
                            Parti sayfası
                        </a>
                    @else
                        <p class="text-base-content/50">Parti bilgisi yok.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sağ: Kalemler + Özet --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-base gap-2">
                        @svg('heroicon-o-shopping-bag', 'h-5 w-5')
                        Sipariş kalemleri
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th class="text-center w-20">Adet</th>
                                    <th class="text-right w-28">Birim fiyat</th>
                                    <th class="text-right w-28">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <span class="font-medium">{{ $item->product_name ?? $item->product?->name ?? '-' }}</span>
                                            @if ($item->product_sku ?? $item->product?->sku)
                                                <span class="text-xs text-base-content/50 ml-1">({{ $item->product_sku ?? $item->product->sku }})</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} ₺</td>
                                        <td class="text-right font-medium">{{ number_format((float) ($item->total_price ?? $item->quantity * $item->unit_price), 2, ',', '.') }} ₺</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-base gap-2">Özet</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-base-content/70">Ara toplam</dt>
                            <dd>{{ number_format((float) $order->subtotal, 2, ',', '.') }} ₺</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/70">KDV (%{{ number_format((float) $order->tax_rate, 0) }})</dt>
                            <dd>{{ number_format((float) $order->tax_amount, 2, ',', '.') }} ₺</dd>
                        </div>
                        <div class="flex justify-between font-semibold text-base pt-2 border-t border-base-300">
                            <dt>Toplam</dt>
                            <dd>{{ number_format((float) ($order->total_amount ?? $order->total), 2, ',', '.') }} ₺</dd>
                        </div>
                    </dl>
                    @if ($order->dealer_note)
                        <div class="mt-4 pt-4 border-t border-base-300">
                            <p class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1">Bayi notu</p>
                            <p class="text-sm">{{ $order->dealer_note }}</p>
                        </div>
                    @endif
                    @if ($order->admin_note)
                        <div class="mt-2">
                            <p class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1">Admin notu</p>
                            <p class="text-sm">{{ $order->admin_note }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

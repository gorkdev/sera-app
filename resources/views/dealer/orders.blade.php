@extends('layouts.app')

@section('title', 'Siparişlerim')

@section('content')
<div class="container mx-auto px-4 lg:px-6 py-6">
    <h1 class="text-2xl font-bold mb-6">Siparişlerim</h1>

    @if ($activeCarts->isNotEmpty())
        <div class="card bg-base-100 shadow-sm border border-base-300/50 mb-8">
            <div class="card-body">
                <h2 class="card-title text-lg">Aktif rezervasyonlar</h2>
                <p class="text-sm text-base-content/70 mb-4">Sepette rezerve edilmiş ürünler (henüz sipariş tamamlanmadı).</p>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Parti</th>
                                <th>Ürün</th>
                                <th>Adet</th>
                                <th>Tutar</th>
                                <th>Süre bitişi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeCarts as $cart)
                                @foreach ($cart->items as $item)
                                    <tr>
                                        <td>{{ $cart->party?->name ?? '#' . $cart->party_id }}</td>
                                        <td>{{ $item->product?->name ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format((float) ($item->quantity * ($item->unit_price ?? 0)), 2, ',', '.') }} ₺</td>
                                        <td>
                                            @if ($cart->timer_expires_at)
                                                <span class="text-xs {{ now()->gt($cart->timer_expires_at) ? 'text-error' : 'text-base-content/70' }}">
                                                    {{ $cart->timer_expires_at->format('d.m.Y H:i') }}
                                                    @if (now()->gt($cart->timer_expires_at))
                                                        (doldu)
                                                    @endif
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title text-lg">Tamamlanan siparişler</h2>
            <div class="overflow-x-auto">
                @if ($orders->isEmpty())
                    <p class="text-base-content/70 py-8 text-center">Henüz siparişiniz bulunmuyor.</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                                <th>Toplam</th>
                                <th>Teslimat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="font-mono font-medium">{{ $order->order_number ?? '-' }}</td>
                                    <td>{{ $order->created_at?->format('d.m.Y H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($order->orderStatus)
                                            <span class="badge badge-{{ $order->orderStatus->color ?? 'neutral' }} badge-sm">
                                                {{ $order->orderStatus->name }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="font-semibold">{{ number_format($order->total_amount ?? $order->total ?? 0, 2, ',', '.') }} ₺</td>
                                    <td>{{ $order->delivery_type === 'pickup' ? 'Teslim al' : ($order->delivery_type === 'delivery' ? 'Kargo' : '-') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

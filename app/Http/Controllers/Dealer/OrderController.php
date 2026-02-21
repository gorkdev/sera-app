<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $dealer = $request->user('dealer');
        if (! $dealer) {
            abort(403);
        }

        $orders = Order::where('dealer_id', $dealer->id)
            ->with(['orderStatus', 'party', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $carts = Cart::where('dealer_id', $dealer->id)
            ->where('status', Cart::STATUS_ACTIVE)
            ->with(['party', 'items.product'])
            ->orderByDesc('updated_at')
            ->get();

        return view('dealer.orders', [
            'orders' => $orders,
            'activeCarts' => $carts,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index');
    }

    public function show(Order $order): View
    {
        $order->load(['dealer', 'party', 'orderStatus', 'items.product', 'items.partyStock']);

        return view('admin.orders.show', ['order' => $order]);
    }
}

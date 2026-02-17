<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;

class DealerController extends Controller
{
    public function index()
    {
        return view('dealer.index');
    }
}

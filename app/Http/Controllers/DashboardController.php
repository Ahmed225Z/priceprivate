<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductLimitChecker;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, ProductLimitChecker $limits)
    {
        $merchant = $request->user()->merchant;

        return view('dashboard.index', [
            'merchant' => $merchant,
            'products' => $merchant->products()->orderBy('sort_order')->get(),
            'remaining' => $limits->remaining($merchant),
        ]);
    }
}

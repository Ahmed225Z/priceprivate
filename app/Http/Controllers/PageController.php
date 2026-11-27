<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;

class PageController extends Controller
{
    public function home()
    {
        return view('marketing.home');
    }

    public function howItWorks()
    {
        return view('marketing.how');
    }

    /** Prices and limits come from the plans table, never hard-coded in Blade (SRS 11). */
    public function pricing()
    {
        return view('marketing.pricing', [
            'plans' => Plan::where('is_active', true)->orderByDesc('price')->get(),
        ]);
    }

    public function faq()
    {
        return view('marketing.faq');
    }
}

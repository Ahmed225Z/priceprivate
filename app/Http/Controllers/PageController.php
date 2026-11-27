<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Setting;

class PageController extends Controller
{
    public function home()
    {
        return view('marketing.home', ['plans' => Plan::where('is_active', true)->orderBy('price')->get()]);
    }

    public function howItWorks()
    {
        return view('marketing.how');
    }

    /** Prices and limits come from the plans table, never hard-coded in Blade (SRS 11). */
    public function pricing()
    {
        return view('marketing.pricing', [
            'plans' => Plan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function faq()
    {
        return view('marketing.faq');
    }

    public function contact()
    {
        return view('marketing.contact', [
            'whatsapp' => Setting::get('contact_whatsapp'),
            'email' => Setting::get('contact_email'),
        ]);
    }

    public function terms()
    {
        return view('marketing.terms');
    }

    public function privacy()
    {
        return view('marketing.privacy');
    }

    public function report()
    {
        return view('marketing.report');
    }

    /** Real, active stores only. Empty state until real merchants publish (SRS 7.6). */
    public function examples()
    {
        return view('marketing.examples', [
            'stores' => Merchant::query()->where('status', 'active')->where('products_count', '>=', 3)->orderByDesc('products_count')->limit(12)->get(),
        ]);
    }
}

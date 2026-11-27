<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Merchant;

class SeoController extends Controller
{
    public function robots()
    {
        $lines = ['User-agent: *', 'Disallow: /dashboard', 'Disallow: /admin', 'Disallow: /auth', 'Disallow: /payment', 'Disallow: /onboarding', 'Sitemap: '.url('/sitemap.xml')];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    /** Marketing pages and active store pages. Product pages are left out on purpose (many, and price-sensitive). */
    public function sitemap()
    {
        $urls = collect(['/', '/how-it-works', '/pricing', '/faq'])->map(fn ($p) => url($p));
        $stores = Merchant::query()->where('status', 'active')->whereNotNull('slug')->limit(5000)->pluck('slug')->map(fn ($s) => url('/'.$s));

        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls->merge($stores) as $u) {
            $xml .= '<url><loc>'.e($u).'</loc></url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}

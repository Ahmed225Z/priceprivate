<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Enums\PrintTemplate;
use App\Http\Controllers\Controller;
use App\Services\PrintSheetService;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function generate(Request $request, PrintSheetService $svc)
    {
        $data = $request->validate([
            'template' => ['required', 'in:label_sheet,store_card'],
            'product_ids' => ['nullable', 'array', 'max:'.PrintSheetService::MAX_PRODUCTS],
            'product_ids.*' => ['integer'],
        ]);

        $m = $request->user()->merchant;
        $template = PrintTemplate::from($data['template']);

        // Only this merchant's own products, whatever ids were posted.
        $products = $m->products()
            ->when(! empty($data['product_ids']), fn ($q) => $q->whereIn('id', $data['product_ids']))
            ->orderBy('sort_order')
            ->limit(PrintSheetService::MAX_PRODUCTS)
            ->get();

        if ($template === PrintTemplate::LabelSheet && $products->isEmpty()) {
            return back()->withErrors(['product_ids' => 'اختار منتج واحد على الأقل.']);
        }

        $path = $svc->generate($m, $template, $products);

        return response()->download($path)->deleteFileAfterSend(false);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ShareTargetType;
use App\Models\AdSlot;
use App\Models\Category;
use App\Services\AdService;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class MenuController extends Controller
{
    public function show(Request $request, string $slug): Response|RedirectResponse
    {
        return $this->render($request, $slug, null);
    }

    public function category(Request $request, string $slug, string $category): Response|RedirectResponse
    {
        return $this->render($request, $slug, $category);
    }

    private function render(Request $request, string $slug, ?string $categorySlug): Response|RedirectResponse
    {
        $merchant = Merchant::withTrashed()->where('slug', $slug)->first();

        if (! $merchant) {
            return response()->view('public.state', [
                'title' => 'القائمة دي مش موجودة',
                'message' => 'القائمة دي مش موجودة.',
                'cta' => true,
            ], 404);
        }
        if ($merchant->trashed() || $merchant->status === 'deleted') {
            return response()->view('public.state', ['title' => 'القائمة دي مش متاحة', 'message' => 'صاحب المحل شال القائمة دي.'], 410);
        }
        if ($merchant->status === 'suspended') {
            return response()->view('public.state', ['title' => 'القائمة دي موقوفة مؤقتًا', 'message' => 'القائمة دي موقوفة مؤقتًا.'], 403);
        }

        $category = null;
        $type = ShareTargetType::Store;
        if ($categorySlug !== null) {
            $category = Category::where('merchant_id', $merchant->id)
                ->where('slug', $categorySlug)->where('is_visible', true)->first();
            if (! $category) {
                return redirect('/'.$merchant->slug);
            }
            $type = ShareTargetType::Category;
        }

        $products = $merchant->products()
            ->where('is_visible', true)
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->orderBy('sort_order')
            ->get();

        $request->attributes->set('scan.merchant', $merchant);
        $request->attributes->set('scan.type', $type);
        $request->attributes->set('scan.target_id', $category?->id);

        return response()->view('public.menu', [
            'merchant' => $merchant,
            'category' => $category,
            'categories' => $merchant->categories()->where('is_visible', true)->orderBy('sort_order')->get(),
            'products' => $products,
            'adAfterFirst' => app(AdService::class)->slotFor($merchant, AdSlot::LIST_AFTER_FIRST_CATEGORY),
            'adFooter' => app(AdService::class)->slotFor($merchant, AdSlot::LIST_FOOTER),
        ]);
    }
}

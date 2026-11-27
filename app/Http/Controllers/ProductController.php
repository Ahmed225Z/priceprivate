<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProductResolutionStatus as S;
use App\Enums\ShareTargetType;
use App\Models\Product;
use App\Services\ProductResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /** Longer share link: /{slug}/p/{code}. Route params are passed positionally, so it gets its own signature. */
    public function showWithSlug(Request $request, ProductResolver $resolver, string $slug, string $code): Response|RedirectResponse
    {
        return $this->show($request, $resolver, $code);
    }

    public function show(Request $request, ProductResolver $resolver, string $code): Response|RedirectResponse
    {
        $r = $resolver->resolve(strtoupper($code));
        $merchant = $r->merchant;

        switch ($r->status) {
            case S::ProductDeleted:
                return redirect('/'.$merchant->slug)
                    ->with('notice', 'المنتج ده اتشال من القائمة. دي باقي أسعار '.$merchant->store_name);

            case S::MerchantSuspended:
                return response()->view('public.state', [
                    'title' => 'القائمة دي موقوفة مؤقتًا',
                    'message' => 'القائمة دي موقوفة مؤقتًا.',
                ], 403);

            case S::MerchantDeleted:
                return response()->view('public.state', [
                    'title' => 'القائمة دي مش متاحة',
                    'message' => 'صاحب المحل شال القائمة دي.',
                ], 410);

            case S::NotFound:
                return response()->view('public.state', [
                    'title' => 'الكود ده مش موجود',
                    'message' => 'الكود ده مش موجود. اتأكد من الكود وجرّب تاني.',
                    'cta' => true,
                ], 404);
        }

        $product = $r->product;
        $request->attributes->set('scan.merchant', $merchant);
        $request->attributes->set('scan.type', ShareTargetType::Product);
        $request->attributes->set('scan.target_id', $product->id);

        $related = $product->category_id
            ? Product::query()
                ->where('category_id', $product->category_id)
                ->where('is_visible', true)
                ->whereKeyNot($product->id)
                ->orderBy('sort_order')
                ->limit(6)
                ->get()
            : collect();

        return response()->view('public.product', [
            'product' => $product,
            'merchant' => $merchant,
            'showPrice' => $r->shouldShowPrice(),
            'related' => $related,
        ]);
    }
}

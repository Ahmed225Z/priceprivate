<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Actions\UpdateProductPrice;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ImageProcessor;
use App\Services\ProductLimitChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $m = $request->user()->merchant;

        return view('dashboard.products', [
            'merchant' => $m,
            'products' => $m->products()->with('category')->orderBy('sort_order')->get(),
            'categories' => $m->categories()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, ProductLimitChecker $limits, UpdateProductPrice $price, ImageProcessor $images): RedirectResponse
    {
        $m = $request->user()->merchant;

        // Layer 2 of the limit check (SRS 7.5): enforced in the action as well as middleware.
        if (! $limits->canAdd($m)) {
            return back()->withErrors(['name' => 'وصلت للحد الأقصى للمنتجات في خطتك.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('merchant_id', $m->id)],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999', 'decimal:0,2'],
            'unit' => ['nullable', 'string', 'max:20'],
            'short_description' => ['nullable', 'string', 'max:140'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product = $m->products()->create([
            'name' => $data['name'],
            'category_id' => $data['category_id'] ?? null,
            'unit' => $data['unit'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'image_path' => $request->hasFile('image') ? $images->store($request->file('image'), 'products') : null,
            'is_visible' => true,
            'sort_order' => (int) $m->products()->max('sort_order') + 1,
            'currency' => 'EGP',
        ]);

        $m->increment('products_count');
        if (isset($data['price']) && $data['price'] !== '') {
            $price->handle($product, $data['price'], bumpCache: false);
        }
        $m->bumpCacheVersion();

        return back();
    }

    public function reorder(Request $request, \App\Actions\ReorderProducts $action): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:1000'], 'ids.*' => ['integer']]);
        $action->handle($request->user()->merchant, $data['ids']);

        return response()->json(['ok' => true]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $product->update(['is_visible' => ! $product->is_visible]);
        $product->merchant->bumpCacheVersion();

        return back();
    }

    /** Soft delete only: the printed code must keep resolving (SRS section 4). */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $merchant = $product->merchant;
        $product->delete();
        \DB::table('merchants')->where('id', $merchant->id)->update(['products_count' => \DB::raw('CASE WHEN products_count > 0 THEN products_count - 1 ELSE 0 END')]); // never below 0 (unsigned on MySQL)
        $merchant->bumpCacheVersion();

        return back();
    }

    /** Quick price editor: one form, many prices, one cache bump at the end (SRS 7.3). */
    public function updatePrices(Request $request, UpdateProductPrice $action): RedirectResponse
    {
        $request->validate([
            'prices' => ['required', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999', 'decimal:0,2'],
        ]);

        $m = $request->user()->merchant;
        $products = $m->products()->whereIn('id', array_keys($request->input('prices')))->get()->keyBy('id');
        $changed = 0;

        foreach ($request->input('prices') as $id => $value) {
            $product = $products->get((int) $id);   // ids that are not this merchant's are ignored
            if ($product && $action->handle($product, $value, bumpCache: false)) {
                $changed++;
            }
        }

        if ($changed > 0) {
            $m->bumpCacheVersion();
        }

        return back()->with('notice', "اتحفظ {$changed} سعر.");
    }
}

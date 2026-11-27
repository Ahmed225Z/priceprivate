<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.categories', [
            'categories' => $request->user()->merchant->categories()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);
        $m = $request->user()->merchant;
        $m->categories()->create([
            'name' => $data['name'],
            'sort_order' => (int) $m->categories()->max('sort_order') + 1,
            'is_visible' => true,
        ]);
        $m->bumpCacheVersion();

        return back();
    }

    public function destroy(Request $request, int $category): RedirectResponse
    {
        // Slugs are only unique per merchant, so look up by id (not route-model binding on slug).
        $category = Category::findOrFail($category);
        $this->authorize('delete', $category);
        // Products go to "uncategorized" (category_id null), never deleted: FK is nullOnDelete.
        $category->delete();
        $request->user()->merchant->bumpCacheVersion();

        return back();
    }
}

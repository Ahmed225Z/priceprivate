<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\PriceUpdated;
use App\Models\PriceHistory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/** SRS §7.3. Null price means "not available right now". Decimals only, never floats in the DB. */
final class UpdateProductPrice
{
    /** @return bool true if the price actually changed */
    public function handle(Product $product, null|int|float|string $newPrice, bool $bumpCache = true): bool
    {
        $new = $newPrice === null || $newPrice === '' ? null : number_format((float) $newPrice, 2, '.', '');
        $old = $product->price === null ? null : number_format((float) $product->price, 2, '.', '');

        if ($old === $new) {
            return false;
        }

        DB::transaction(function () use ($product, $old, $new): void {
            $product->forceFill(['price' => $new, 'price_updated_at' => now()])->save();

            PriceHistory::create([
                'product_id' => $product->id,
                'old_price' => $old,
                'new_price' => $new,
                'changed_at' => now(),
            ]);

            $product->merchant->forceFill(['prices_updated_at' => now()])->save();
        });

        PriceUpdated::dispatch($product, $bumpCache);

        return true;
    }
}

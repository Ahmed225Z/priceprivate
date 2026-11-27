<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductResolutionStatus as S;
use App\Models\Product;

/**
 * SRS §8.3: a printed code must never end in a blank 404.
 * Subscription expiry is deliberately NOT a state here (decision D8).
 */
final class ProductResolver
{
    public function resolve(string $code): ProductResolution
    {
        if (! CodeGenerator::isValid($code)) {
            return new ProductResolution(S::NotFound);
        }

        $product = Product::withTrashed()
            ->with(['merchant' => fn ($q) => $q->withTrashed()])
            ->where('public_code', $code)
            ->first();

        if (! $product || ! $product->merchant) {
            return new ProductResolution(S::NotFound);
        }

        $merchant = $product->merchant;

        if ($merchant->trashed() || $merchant->status === 'deleted') {
            return new ProductResolution(S::MerchantDeleted, merchant: $merchant);
        }
        if ($merchant->status === 'suspended') {
            return new ProductResolution(S::MerchantSuspended, merchant: $merchant);
        }
        if ($product->trashed()) {
            return new ProductResolution(S::ProductDeleted, merchant: $merchant);
        }
        if (! $product->is_visible) {
            return new ProductResolution(S::Hidden, $product, $merchant);
        }

        return new ProductResolution(S::Ok, $product, $merchant);
    }
}

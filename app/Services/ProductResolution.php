<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductResolutionStatus;
use App\Models\Merchant;
use App\Models\Product;

final readonly class ProductResolution
{
    public function __construct(
        public ProductResolutionStatus $status,
        public ?Product $product = null,
        public ?Merchant $merchant = null,
    ) {}

    /** 200 | 302 | 403 | 404 | 410 (SRS §8.3). */
    public function httpStatus(): int
    {
        return match ($this->status) {
            ProductResolutionStatus::Ok, ProductResolutionStatus::Hidden => 200,
            ProductResolutionStatus::ProductDeleted => 302,
            ProductResolutionStatus::MerchantSuspended => 403,
            ProductResolutionStatus::MerchantDeleted => 410,
            ProductResolutionStatus::NotFound => 404,
        };
    }

    public function shouldShowPrice(): bool
    {
        return $this->status === ProductResolutionStatus::Ok;
    }
}

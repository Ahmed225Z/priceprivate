<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;

class PriceUpdated
{
    use Dispatchable;

    public function __construct(public Product $product, public bool $bumpCache = true) {}
}

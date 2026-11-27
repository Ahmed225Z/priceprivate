<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PriceUpdated;

class InvalidateMerchantCache
{
    public function handle(PriceUpdated $event): void
    {
        if ($event->bumpCache) {
            $event->product->merchant->bumpCacheVersion();
        }
    }
}

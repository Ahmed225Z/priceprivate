<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Merchant;
use App\Models\Setting;

/** SRS §7.5. The free limit comes from settings (default 25, still pending client decision, SRS §31 Q2). */
final class ProductLimitChecker
{
    public const DEFAULT_FREE_LIMIT = 25;

    /** null = unlimited */
    public function limitFor(Merchant $m): ?int
    {
        $sub = $m->activeSubscription()->with('plan')->first();

        if ($sub && $sub->plan) {
            return $sub->plan->product_limit;
        }

        return (int) Setting::get(Setting::FREE_PRODUCT_LIMIT, self::DEFAULT_FREE_LIMIT);
    }

    public function remaining(Merchant $m): ?int
    {
        $limit = $this->limitFor($m);

        return $limit === null ? null : max(0, $limit - $m->products()->count());
    }

    public function canAdd(Merchant $m): bool
    {
        $remaining = $this->remaining($m);

        return $remaining === null || $remaining > 0;
    }
}

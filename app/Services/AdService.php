<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdSlot;
use App\Models\Merchant;

/**
 * SRS section 13. Free plan only. Never above the price. Max 2 ads on a list page, 1 on a product page.
 */
final class AdService
{
    public function __construct(private SubscriptionService $subs) {}

    /** @return AdSlot|null the slot to render for this placement, or null for none */
    public function slotFor(Merchant $m, string $code): ?AdSlot
    {
        if ($this->subs->hasPaidFeatures($m)) {
            return null;
        }

        return AdSlot::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->first();
    }
}

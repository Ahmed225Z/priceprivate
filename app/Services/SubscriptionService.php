<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;

/**
 * SRS section 12. Expiry NEVER takes the public page down (D8). It only brings back ads, the default colour,
 * removes the verified badge, and hides (never deletes) products above the free limit, newest first.
 */
final class SubscriptionService
{
    public const GRACE_DAYS = 7;

    public function activate(Payment $payment): Subscription
    {
        $plan = $payment->plan;
        $merchant = $payment->merchant;

        $current = $merchant->subscriptions()
            ->whereIn('status', ['active', 'grace'])
            ->orderByDesc('ends_at')
            ->first();

        // Renewal before expiry extends from ends_at, not from today.
        $base = $current && $current->ends_at && $current->ends_at->isFuture() ? $current->ends_at : now();
        $ends = $base->copy()->addDays($plan->duration_days);

        $attrs = [
            'plan_id' => $plan->id,
            'status' => 'active',
            'ends_at' => $ends,
            'grace_ends_at' => $ends->copy()->addDays(self::GRACE_DAYS),
            'cancelled_at' => null,
        ];

        if ($current) {
            $current->forceFill($attrs)->save();
            $sub = $current;
        } else {
            $sub = $merchant->subscriptions()->create($attrs + ['starts_at' => now()]);
        }

        $payment->forceFill(['subscription_id' => $sub->id])->save();

        // Bring back only the products the plan limit hid.
        $merchant->products()->whereNotNull('auto_hidden_at')->update(['is_visible' => true, 'auto_hidden_at' => null]);
        $merchant->bumpCacheVersion();

        return $sub;
    }

    /** Daily job: active -> grace -> free. @return int number of subscriptions moved */
    public function roll(): int
    {
        $moved = 0;

        Subscription::where('status', 'active')->where('ends_at', '<', now())->each(function (Subscription $s) use (&$moved) {
            $s->forceFill(['status' => 'grace'])->save();
            $moved++;
        });

        Subscription::where('status', 'grace')->where('grace_ends_at', '<', now())->each(function (Subscription $s) use (&$moved) {
            $s->forceFill(['status' => 'expired'])->save();
            $this->enforceFreeLimit($s->merchant);
            $s->merchant->bumpCacheVersion();
            $moved++;
        });

        return $moved;
    }

    public function currentPlan(Merchant $m): Plan
    {
        $sub = $m->activeSubscription()->with('plan')->first();

        return $sub?->plan ?? Plan::where('code', Plan::FREE)->firstOrFail();
    }

    public function hasPaidFeatures(Merchant $m): bool
    {
        return $m->activeSubscription()->exists();
    }

    /** Hide the newest products above the free limit. Never delete. */
    private function enforceFreeLimit(Merchant $m): void
    {
        $limit = (int) (Plan::where('code', Plan::FREE)->value('product_limit') ?? ProductLimitChecker::DEFAULT_FREE_LIMIT);

        $m->products()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')
            ->get()->slice($limit)
            ->each(fn (Product $p) => $p->forceFill(['is_visible' => false, 'auto_hidden_at' => now()])->save());
    }
}

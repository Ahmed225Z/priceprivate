<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'plan_id' => Plan::factory(),
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'plan_id' => Plan::factory()->free(),
            'ends_at' => null,
        ]);
    }

    /** في مهلة السماح — المميزات لسه شغالة (§10.3). */
    public function grace(): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_GRACE,
            'ends_at' => now()->subDay(),
            'grace_ends_at' => now()->addDays(Subscription::GRACE_DAYS - 1),
        ]);
    }

    /** منتهي — 🚨 القائمة العامة لازم تفضل شغالة برضه (D8). */
    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_EXPIRED,
            'ends_at' => now()->subDays(30),
            'grace_ends_at' => now()->subDays(23),
        ]);
    }

    /** على وشك الانتهاء — لاختبار التنبيهات (§8.2ز). */
    public function expiringInDays(int $days): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays($days),
        ]);
    }
}

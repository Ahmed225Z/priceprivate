<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Plan> */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Plan::PRO,
            'name' => 'الخطة المدفوعة',
            'price' => 200,
            'currency' => 'EGP',
            'duration_days' => 365,
            'product_limit' => null,
            'features' => ['no_ads', 'custom_color', 'verified_badge', 'priority_support'],
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'code' => Plan::FREE,
            'name' => 'الخطة المجانية',
            'price' => 0,
            'duration_days' => 0,
            'product_limit' => 25,
            'features' => [],
        ]);
    }
}

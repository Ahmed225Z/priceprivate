<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * PROJECT_SPEC.md §10.1
 * ⚠️ product_limit للخطة المجانية لسه محتاج قرار العميل (§31 Q2 — المقترح 25).
 *    القيمة هنا مبدئية وبتتعدّل من لوحة الإدارة بدون نشر كود.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['code' => Plan::FREE],
            [
                'name' => 'الخطة المجانية',
                'price' => 0,
                'currency' => 'EGP',
                'duration_days' => 0,
                'product_limit' => 25,
                'features' => [],
                'is_active' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['code' => Plan::PRO],
            [
                'name' => 'الخطة المدفوعة',
                'price' => 200,
                'currency' => 'EGP',
                'duration_days' => 365,
                'product_limit' => null,     // غير محدود
                'features' => [
                    'no_ads',
                    'custom_color',
                    'verified_badge',
                    'priority_support',
                ],
                'is_active' => true,
            ],
        );
    }
}

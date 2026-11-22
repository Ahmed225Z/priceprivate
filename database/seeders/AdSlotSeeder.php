<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdSlot;
use Illuminate\Database\Seeder;

/**
 * PROJECT_SPEC.md §11
 * 🚨 مفيش أي مكان إعلان فوق السعر. كلها متعطّلة افتراضيًا لحد ما AdSense يتفعّل.
 */
class AdSlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            [
                'code' => AdSlot::LIST_AFTER_FIRST_CATEGORY,
                'name' => 'صفحة القائمة — بعد أول قسم',
                'min_height' => 100,
            ],
            [
                'code' => AdSlot::LIST_FOOTER,
                'name' => 'صفحة القائمة — الفوتر',
                'min_height' => 100,
            ],
            [
                'code' => AdSlot::PRODUCT_BELOW_CONTACT,
                'name' => 'صفحة المنتج — تحت أزرار الاتصال',
                'min_height' => 100,
            ],
        ];

        foreach ($slots as $slot) {
            AdSlot::query()->updateOrCreate(
                ['code' => $slot['code']],
                [
                    'name' => $slot['name'],
                    'min_height' => $slot['min_height'],
                    'is_active' => false,
                    'type' => 'adsense',
                ],
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/** PROJECT_SPEC.md §12, §15.1 (settings) */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // §31 Q2 — القيمة النهائية محتاجة قرار العميل.
        Setting::put(Setting::FREE_PRODUCT_LIMIT, 25);
        Setting::put(Setting::ADS_ENABLED, false);
    }
}

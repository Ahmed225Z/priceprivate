<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * تاجر تجريبي كامل للتطوير والاختبار اليدوي.
 * PROJECT_SPEC.md §21.2 — لازم يكون فيه حالات حقيقية: منتج مخفي، منتج من غير سعر،
 * ومنتج سعره قديم، عشان نقدر نختبر التوجيه الذكي وشارات التحديث.
 *
 * الأسعار غير مدوّرة عن قصد (§13.6).
 */
class DemoMerchantSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'demo@priceprivate.test'],
            [
                'name' => 'أحمد صاحب المحل',
                'role' => User::ROLE_MERCHANT,
                'provider' => 'google',
                'provider_id' => 'demo-google-id',
                'email_verified_at' => now(),
            ],
        );

        $merchant = Merchant::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'store_name' => 'سوبر ماركت الأمانة',
                'slug' => 'el-amana',
                'governorate' => 'الجيزة',
                'business_type' => 'سوبر ماركت وبقالة',
                'phone' => '01012345678',
                'whatsapp' => '01012345678',
                'address' => 'شارع الهرم، فيصل',
                'is_verified' => true,
                'status' => 'active',
                'default_qr_target_type' => 'store',
                'prices_updated_at' => now()->subHours(3),
            ],
        );

        $merchant->products()->forceDelete();
        $merchant->categories()->delete();

        $catalogue = [
            'بقالة جافة' => [
                ['أرز مصري شعير 1 كيلو', 42.50, 'كيلو'],
                ['سكر معبأ 1 كيلو', 38, 'كيلو'],
                ['مكرونة اسباجتي 400 جم', 17.25, 'علبة'],
                ['شاي العروسة 100 كيس', 112, 'علبة'],
                ['دقيق فاخر 1 كيلو', 29.75, 'كيلو'],
                ['ملح طعام 1 كيلو', 8.50, 'كيلو'],
                ['فول مدمس معلب', 24, 'علبة'],
                ['عدس أصفر 1 كيلو', 68.25, 'كيلو'],
            ],
            'زيوت وسمن' => [
                ['زيت عافية 1 لتر', 95.50, 'لتر'],
                ['زيت دوار الشمس 800 مل', 78, 'علبة'],
                ['سمنة بلدي 1 كيلو', 285, 'كيلو'],
                ['زيت زيتون 500 مل', 317.75, 'علبة'],
                ['سمن نباتي 1 كيلو', 148.50, 'كيلو'],
            ],
            'ألبان وأجبان' => [
                ['لبن جهينة كامل الدسم 1 لتر', 48, 'لتر'],
                ['جبنة بيضاء 1 كيلو', 132.50, 'كيلو'],
                ['جبنة رومي 1 كيلو', 395, 'كيلو'],
                ['زبادي جهينة', 12.75, 'قطعة'],
                ['بيض بلدي طبق 30', 158, 'طبق'],
            ],
            'منظفات' => [
                ['صابون سائل 1 لتر', 64.50, 'لتر'],
                ['مسحوق غسيل 2 كيلو', 187, 'علبة'],
                ['كلوركس 1 لتر', 32.25, 'لتر'],
                ['منظف أرضيات 1 لتر', 45, 'لتر'],
            ],
            'مشروبات' => [
                ['مياه معدنية 1.5 لتر', 9.50, 'قطعة'],
                ['عصير تفاح 1 لتر', 38.75, 'لتر'],
                ['مشروب غازي 1 لتر', 27, 'قطعة'],
            ],
        ];

        $categorySort = 0;

        foreach ($catalogue as $categoryName => $items) {
            $category = Category::query()->create([
                'merchant_id' => $merchant->id,
                'name' => $categoryName,
                'slug' => Str::slug($categoryName) ?: Str::lower(Str::random(8)),
                'sort_order' => $categorySort++,
                'is_visible' => true,
            ]);

            $productSort = 0;

            foreach ($items as [$name, $price, $unit]) {
                Product::query()->create([
                    'merchant_id' => $merchant->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'price' => $price,
                    'unit' => $unit,
                    'currency' => 'EGP',
                    'is_visible' => true,
                    'sort_order' => $productSort++,
                    'price_updated_at' => now()->subHours(random_int(1, 72)),
                ]);
            }
        }

        // حالات حافة لازم تكون موجودة للاختبار اليدوي (§6.5, §6.4)
        $first = Product::query()->where('merchant_id', $merchant->id)->first();

        Product::query()->create([
            'merchant_id' => $merchant->id,
            'category_id' => $first?->category_id,
            'name' => 'منتج مخفي مؤقتًا (للاختبار)',
            'price' => 99.50,
            'unit' => 'قطعة',
            'is_visible' => false,
            'sort_order' => 99,
            'price_updated_at' => now()->subDays(2),
        ]);

        Product::query()->create([
            'merchant_id' => $merchant->id,
            'category_id' => $first?->category_id,
            'name' => 'منتج من غير سعر (للاختبار)',
            'price' => null,
            'is_visible' => true,
            'sort_order' => 98,
        ]);

        Product::query()->create([
            'merchant_id' => $merchant->id,
            'category_id' => $first?->category_id,
            'name' => 'منتج سعره قديم (للاختبار)',
            'price' => 73.25,
            'unit' => 'كيلو',
            'is_visible' => true,
            'sort_order' => 97,
            'price_updated_at' => now()->subDays(45),
        ]);

        $merchant->update([
            'products_count' => $merchant->products()->count(),
        ]);

        // اشتراك مجاني نشط
        $freePlan = Plan::query()->where('code', Plan::FREE)->first();

        if ($freePlan) {
            Subscription::query()->updateOrCreate(
                ['merchant_id' => $merchant->id, 'plan_id' => $freePlan->id],
                [
                    'status' => Subscription::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'ends_at' => null,
                ],
            );
        }

        $this->command?->info("تاجر تجريبي جاهز: /{$merchant->slug} — {$merchant->products()->count()} منتج");
    }
}

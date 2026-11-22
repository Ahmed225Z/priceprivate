<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 *
 * ملاحظة: public_code بيتولّد تلقائيًا في Product::creating — ممنوع تحديده هنا.
 * الأسعار غير مدوّرة عن قصد (185 مش 200) — §13.6.
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->randomElement([
                'زيت عافية 1 لتر', 'سكر معبأ 1 كيلو', 'أرز مصري 1 كيلو',
                'مكرونة اسباجتي 400 جم', 'شاي العروسة 100 كيس', 'جبنة بيضاء 1 كيلو',
                'صابون سائل 1 لتر', 'لبن جهينة 1 لتر', 'سمنة بلدي 1 كيلو',
            ]),
            'slug' => Str::lower(Str::random(8)),
            'price' => fake()->randomElement([
                38.50, 47, 62.75, 85, 112.25, 138, 185, 215.50, 264, 317.75,
            ]),
            'currency' => 'EGP',
            'unit' => fake()->randomElement([null, 'قطعة', 'كيلو', 'لتر', 'علبة']),
            'is_visible' => true,
            'sort_order' => fake()->numberBetween(0, 50),
            'price_updated_at' => now()->subDays(fake()->numberBetween(0, 10)),
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }

    /** منتج من غير سعر → الصفحة بتعرض «السعر غير متاح حاليًا» (§6.4). */
    public function withoutPrice(): static
    {
        return $this->state(fn () => ['price' => null]);
    }

    /** سعر قديم → شارة التحديث الصفراء (§6.4 بند 5). */
    public function stalePrice(): static
    {
        return $this->state(fn () => ['price_updated_at' => now()->subDays(45)]);
    }
}

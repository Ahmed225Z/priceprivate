<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'name' => fake()->randomElement([
                'زيوت وسمن', 'مكرونة وأرز', 'منظفات', 'مشروبات',
                'ألبان وأجبان', 'بقالة جافة', 'معلبات',
            ]),
            'slug' => Str::lower(Str::random(8)),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_visible' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }
}

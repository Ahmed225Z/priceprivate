<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Merchant> */
class MerchantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'محل الأمانة', 'سوبر ماركت النور', 'معرض الشيماء', 'توكيل المصري',
            'مؤسسة البركة', 'محل أبو يوسف', 'مركز الهدى', 'بيت الجملة',
        ]).' '.fake()->randomElement(['للتجارة', 'للأدوات', '']);

        return [
            'user_id' => User::factory(),
            'store_name' => trim($name),
            'slug' => Str::lower(Str::random(10)),
            'governorate' => fake()->randomElement(config('egypt.governorates')),
            'business_type' => fake()->randomElement(config('egypt.business_types')),
            'phone' => '01'.fake()->numberBetween(0, 2).fake()->numerify('########'),
            'whatsapp' => '01'.fake()->numberBetween(0, 2).fake()->numerify('########'),
            'status' => 'active',
            'default_qr_target_type' => 'store',
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['is_verified' => true]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }
}

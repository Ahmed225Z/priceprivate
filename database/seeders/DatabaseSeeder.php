<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            AdSlotSeeder::class,
            SettingSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@priceprivate.test'],
            [
                'name' => 'أدمن المنصة',
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ],
        );

        if (app()->environment('local', 'staging')) {
            $this->call(DemoMerchantSeeder::class);
        }
    }
}

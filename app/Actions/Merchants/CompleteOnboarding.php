<?php

declare(strict_types=1);

namespace App\Actions\Merchants;

use App\Models\Merchant;
use App\Models\User;

final class CompleteOnboarding
{
    /** @param array{store_name:string,slug:string,governorate:string,business_type:string,phone:string,whatsapp?:?string,address?:?string,working_hours?:?array} $data */
    public function handle(User $user, array $data): Merchant
    {
        return $user->merchant()->firstOr(function () use ($user, $data) {
            return Merchant::create([
                'user_id' => $user->id,
                'store_name' => $data['store_name'],
                'slug' => $data['slug'],
                'governorate' => $data['governorate'],
                'business_type' => $data['business_type'],
                'phone' => $data['phone'],
                'whatsapp' => $data['whatsapp'] ?? null,
                'address' => $data['address'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'default_qr_target_type' => 'store',
            ]);
        });
    }
}

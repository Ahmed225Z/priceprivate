<?php

declare(strict_types=1);

namespace App\Actions\Merchants;

use App\Models\User;

/** SRS §6: provider+id first, then email link (only when the provider returned an email), else a new merchant user. */
final class FindOrCreateUser
{
    public function handle(string $provider, string $providerId, ?string $email, ?string $name, ?string $avatar): User
    {
        $user = User::where('provider', $provider)->where('provider_id', $providerId)->first();

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->forceFill(['provider' => $provider, 'provider_id' => $providerId])->save();
            }
        }

        if (! $user) {
            $user = new User([
                'name' => $name ?: 'تاجر جديد',
                'email' => $email,
                'avatar' => $avatar,
                'role' => User::ROLE_MERCHANT,
            ]);
            $user->forceFill(['provider' => $provider, 'provider_id' => $providerId])->save();
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }
}

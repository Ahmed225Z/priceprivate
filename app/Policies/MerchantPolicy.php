<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Merchant;
use App\Models\User;

class MerchantPolicy
{
    public function update(User $user, Merchant $merchant): bool
    {
        return $user->id === $merchant->user_id;
    }
}

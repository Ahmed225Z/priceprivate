<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    private function owns(User $user, Product $product): bool
    {
        return $product->merchant !== null && $product->merchant->user_id === $user->id;
    }

    public function view(User $user, Product $product): bool { return $this->owns($user, $product); }

    public function update(User $user, Product $product): bool { return $this->owns($user, $product); }

    public function delete(User $user, Product $product): bool { return $this->owns($user, $product); }
}

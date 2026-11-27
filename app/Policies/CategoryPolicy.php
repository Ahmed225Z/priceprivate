<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    private function owns(User $user, Category $category): bool
    {
        return $category->merchant !== null && $category->merchant->user_id === $user->id;
    }

    public function view(User $user, Category $category): bool { return $this->owns($user, $category); }

    public function update(User $user, Category $category): bool { return $this->owns($user, $category); }

    public function delete(User $user, Category $category): bool { return $this->owns($user, $category); }
}

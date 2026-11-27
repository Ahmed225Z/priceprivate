<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Resources\Resource;

/** Admin-only by default. Moderators get Reports (override canAccess). */
abstract class AdminResource extends Resource
{
    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}

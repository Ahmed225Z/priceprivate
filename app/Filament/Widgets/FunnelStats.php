<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Merchant;
use App\Models\ScanStat;
use App\Models\ShareTarget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FunnelStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'قمع الإكمال';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    protected function getStats(): array
    {
        $registered = Merchant::query()->count();

        return [
            Stat::make('1. سجّل', $registered),
            Stat::make('2. أول منتج', Merchant::query()->where('products_count', '>', 0)->count()),
            Stat::make('3. أول كود', ShareTarget::query()->distinct('merchant_id')->count('merchant_id')),
            Stat::make('4. أول مسح', ScanStat::query()->distinct('merchant_id')->count('merchant_id')),
        ];
    }
}

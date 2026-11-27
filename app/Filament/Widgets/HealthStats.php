<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\ScanStat;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HealthStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'مؤشرات المنصة';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    protected function getStats(): array
    {
        $active = Merchant::query()->where('status', 'active');
        $total = (clone $active)->count();
        $fresh = (clone $active)->where('prices_updated_at', '>=', now()->subDays(7))->count();
        $ratio = $total > 0 ? round($fresh / $total * 100) : 0;

        return [
            Stat::make('تجار جدد النهارده', Merchant::query()->whereDate('created_at', today())->count()),
            Stat::make('التجار النشطين', $total),
            Stat::make('المشتركين (مدفوع)', Subscription::query()->whereIn('status', ['active', 'grace'])->whereHas('plan', fn ($q) => $q->where('price', '>', 0))->count()),
            Stat::make('الإيراد (جنيه)', number_format((float) Payment::query()->where('status', 'paid')->sum('amount'), 2)),
            Stat::make('مسح النهارده', (int) ScanStat::query()->whereDate('date', today())->sum('count')),
            Stat::make('حدّثوا سعر آخر 7 أيام', $ratio.'%')->description("{$fresh} من {$total} تاجر"),
        ];
    }
}

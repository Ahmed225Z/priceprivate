<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;

final class PriceFormatter
{
    public static function format(?string $price): ?string
    {
        if ($price === null) {
            return null;
        }

        $n = (float) $price;

        return fmod($n, 1.0) === 0.0 ? number_format($n, 0) : number_format($n, 2);
    }

    /** @return array{0:string,1:string} [state, label] */
    public static function freshness(?Carbon $at): array
    {
        if (! $at) {
            return ['neutral', 'مفيش تاريخ تحديث'];
        }
        if ($at->isToday()) {
            return ['ok', 'اتحدّث النهاردة'];
        }

        $days = (int) $at->diffInDays(now(), true);

        if ($days <= 7) {
            return ['neutral', "اتحدّث من {$days} يوم"];
        }
        if ($days <= 30) {
            return ['neutral', "آخر تحديث من {$days} يوم"];
        }

        return ['warn', "آخر تحديث من {$days} يوم"];
    }

    public static function whatsappUrl(?string $phone, string $productName, string $productUrl): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '2'.$digits;
        }
        $text = "السلام عليكم، حابب أسأل عن: {$productName} — {$productUrl}";

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }
}

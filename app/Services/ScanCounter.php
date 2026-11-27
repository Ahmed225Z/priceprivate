<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ShareTargetType;
use App\Models\Merchant;
use Illuminate\Support\Facades\DB;

/** SRS §8.6: daily aggregate, no personal data. */
final class ScanCounter
{
    public function record(Merchant $merchant, ShareTargetType $type, ?int $targetId, string $source): void
    {
        DB::table('scan_stats')->upsert(
            [[
                'merchant_id' => $merchant->id,
                'target_type' => $type->value,
                'target_id' => $targetId,
                'date' => today()->toDateString(),
                'source' => $source,
                'count' => 1,
            ]],
            ['merchant_id', 'target_type', 'target_id', 'date', 'source'],
            ['count' => DB::raw('count + 1')],
        );
    }

    /** ?s=qr -> qr, ?s=l -> link, anything else -> direct. */
    public static function sourceFrom(?string $s): string
    {
        return match ($s) {
            'qr' => 'qr',
            'l' => 'link',
            default => 'direct',
        };
    }
}

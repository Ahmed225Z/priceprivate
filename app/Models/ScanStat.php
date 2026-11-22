<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PROJECT_SPEC.md §6.9, §15.1 (scan_stats)
 * عدّ مجمّع يوميًا — ⛔ ممنوع أي بيانات شخصية للعميل النهائي.
 */
class ScanStat extends Model
{
    public $timestamps = false;

    public const SOURCE_QR = 'qr';

    public const SOURCE_LINK = 'link';

    public const SOURCE_DIRECT = 'direct';

    protected $fillable = [
        'merchant_id',
        'target_type',
        'target_id',
        'date',
        'source',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'count' => 'integer',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** PROJECT_SPEC.md §15.1 (share_targets), §6.6 */
class ShareTarget extends Model
{
    public $timestamps = false;

    public const TYPE_STORE = 'store';

    public const TYPE_CATEGORY = 'category';

    public const TYPE_PRODUCT = 'product';

    protected $fillable = [
        'merchant_id',
        'type',
        'target_id',
        'label',
        'shared_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'shared_at' => 'datetime',
        ];
    }

    /** One row per merchant + target. A QR created or a link shared both land here. */
    public static function record(Merchant $m, string $type, ?int $targetId, bool $shared = false): self
    {
        $row = static::query()->firstOrCreate(
            ['merchant_id' => $m->id, 'type' => $type, 'target_id' => $targetId],
            ['created_at' => now()],
        );

        if ($shared && ! $row->shared_at) {
            $row->forceFill(['shared_at' => now()])->save();
        }

        return $row;
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}

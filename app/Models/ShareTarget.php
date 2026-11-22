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
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}

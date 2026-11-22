<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PROJECT_SPEC.md §11, §15.1 (ad_slots)
 *
 * 🚨 ممنوع منعًا باتًا أي إعلان فوق السعر أو يغطيه.
 * الإعلانات بتظهر في الخطة المجانية فقط.
 */
class AdSlot extends Model
{
    public const LIST_AFTER_FIRST_CATEGORY = 'list_after_first_category';

    public const LIST_FOOTER = 'list_footer';

    public const PRODUCT_BELOW_CONTACT = 'product_below_contact';

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'type',
        'adsense_code',
        'banner_path',
        'target_url',
        'min_height',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'min_height' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** الإعلان المباشر (المباع) له الأولوية على AdSense (§11). */
    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function isRunning(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        return ($this->starts_at === null || $this->starts_at->lte($now))
            && ($this->ends_at === null || $this->ends_at->gte($now));
    }
}

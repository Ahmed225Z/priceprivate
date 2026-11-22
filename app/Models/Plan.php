<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PROJECT_SPEC.md §10.1, §15.1 (plans)
 * السعر وحد المنتجات بيتعدّلوا من لوحة الإدارة بدون نشر كود (§12).
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    public const FREE = 'free';

    public const PRO = 'pro';

    protected $fillable = [
        'code',
        'name',
        'price',
        'currency',
        'duration_days',
        'product_limit',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'duration_days' => 'integer',
            'product_limit' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->code === self::FREE;
    }

    /** null = منتجات غير محدودة */
    public function hasUnlimitedProducts(): bool
    {
        return $this->product_limit === null;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MerchantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PROJECT_SPEC.md §15.1 (merchants), §6.3, §8.2و
 */
class Merchant extends Model
{
    /** @use HasFactory<MerchantFactory> */
    use HasFactory;

    use SoftDeletes;

    /** المدة اللي التاجر يقدر يغيّر فيها الرابط (§8.2و). */
    public const SLUG_CHANGE_WINDOW_DAYS = 7;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'logo_path',
        'brand_color',
        'governorate',
        'business_type',
        'phone',
        'whatsapp',
        'address',
        'working_hours',
        'default_qr_target_type',
        'default_qr_target_id',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'is_verified' => 'boolean',
            'prices_updated_at' => 'datetime',
            'slug_changed_at' => 'datetime',
            'products_count' => 'integer',
            'cache_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'grace'])
            ->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scanStats(): HasMany
    {
        return $this->hasMany(ScanStat::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function publicUrl(): string
    {
        return url('/'.$this->slug);
    }

    /** هل التاجر لسه يقدر يغيّر الرابط؟ (مرة واحدة خلال أول 7 أيام) */
    public function canChangeSlug(): bool
    {
        return $this->slug_changed_at === null
            && $this->created_at?->diffInDays(now()) < self::SLUG_CHANGE_WINDOW_DAYS;
    }

    /**
     * إبطال كاش كل صفحات التاجر العامة فورًا (§17).
     * بتتنادى من Events: تعديل سعر، إضافة/حذف/إخفاء منتج، تعديل بيانات المحل، تغيير الخطة.
     */
    public function bumpCacheVersion(): void
    {
        $this->newQuery()->whereKey($this->getKey())->increment('cache_version');
    }

    /** The link the merchant put in the main QR code (SRS 8.4). Falls back to the store page if the target is gone. */
    public function defaultTargetUrl(): string
    {
        if ($this->default_qr_target_type === 'product' && $this->default_qr_target_id) {
            $p = $this->products()->find($this->default_qr_target_id);
            if ($p) {
                return $p->publicUrl();
            }
        }

        if ($this->default_qr_target_type === 'category' && $this->default_qr_target_id) {
            $c = $this->categories()->find($this->default_qr_target_id);
            if ($c) {
                return url('/'.$this->slug.'/'.$c->slug);
            }
        }

        return $this->publicUrl();
    }
}

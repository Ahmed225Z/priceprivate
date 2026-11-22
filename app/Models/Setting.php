<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * PROJECT_SPEC.md §12, §15.1 (settings)
 * إعدادات قابلة للتعديل من لوحة الإدارة بدون نشر كود.
 */
class Setting extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    /** حد المنتجات في الخطة المجانية — §31 Q2 (لسه محتاج قرار العميل). */
    public const FREE_PRODUCT_LIMIT = 'free_product_limit';

    public const ADS_ENABLED = 'ads_enabled';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'updated_at' => 'datetime',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(
            "setting:{$key}",
            fn () => static::query()->find($key)?->value ?? $default,
        );
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }
}

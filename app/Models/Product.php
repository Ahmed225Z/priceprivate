<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\CodeGenerator;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * PROJECT_SPEC.md §15.1 (products), §6.1, §6.2
 *
 * 🔒 public_code ثابت للأبد — الملصق المطبوع في المحل ما ينفعش يبطّل يشتغل.
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'merchant_id',
        'category_id',
        'name',
        'slug',
        'image_path',
        'short_description',
        'price',
        'currency',
        'unit',
        'is_visible',
        'sort_order',
    ];

    /**
     * public_code مش في الـ fillable أصلًا، وكمان محمي صراحةً
     * عشان ما يتغيّرش بالغلط عن طريق forceFill أو setAttribute.
     */
    protected $guarded = ['public_code'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'price_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->public_code)) {
                $product->public_code = app(CodeGenerator::class)->uniqueCode();
            }

            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name) ?: Str::lower(Str::random(8));
            }
        });

        /**
         * 🔒 القاعدة الذهبية رقم 1 (§6.1):
         * تغيير اسم المنتج أو سعره أو قسمه لا يغيّر الكود أبدًا.
         * أي محاولة لتعديل public_code بعد الإنشاء بترمي استثناء.
         */
        static::updating(function (Product $product): void {
            if ($product->isDirty('public_code')) {
                throw new RuntimeException(
                    'public_code is immutable: printed QR labels would stop working. '
                    .'See PROJECT_SPEC.md §6.1 (decision D3).',
                );
            }
        });
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    /** مفتاح الراوت: المنتج بيتجاب بالكود مش بالـ id (§16). */
    public function getRouteKeyName(): string
    {
        return 'public_code';
    }

    /** الرابط المباشر للسعر — ده اللي بيتحط في كود QR (§6.2). */
    public function publicUrl(): string
    {
        return url('/p/'.$this->public_code);
    }
}

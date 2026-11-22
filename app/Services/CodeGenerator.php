<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * توليد public_code للمنتجات.
 *
 * PROJECT_SPEC.md §6.2:
 * - 6 خانات، أبجدية Base32 بدون الحروف الملتبسة (0, O, 1, I, L).
 * - عشوائي غير متسلسل — محدش يقدر يخمّن أكواد تاجر تاني.
 * - فريد على مستوى المنصة كلها.
 * - يتولّد مرة واحدة فقط عند إنشاء المنتج، و**ما يتغيّرش أبدًا** بعد كده (§6.1 / D3).
 */
final class CodeGenerator
{
    /** أبجدية بدون 0 و O و 1 و I و L لمنع الالتباس عند القراءة من ملصق مطبوع. */
    public const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public const LENGTH = 6;

    private const MAX_ATTEMPTS = 20;

    /**
     * يولّد كود فريد غير مستخدم في جدول products (شاملًا المحذوفة soft-deleted،
     * عشان الأكواد المطبوعة القديمة ما تتعاد استخدامها لمنتج تاني).
     */
    public function uniqueCode(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = $this->randomCode();

            if (! $this->exists($code)) {
                return $code;
            }
        }

        throw new RuntimeException(
            'Failed to generate a unique product code after '.self::MAX_ATTEMPTS.' attempts.',
        );
    }

    /** كود عشوائي بطول ثابت باستخدام مولّد آمن تشفيريًا. */
    public function randomCode(): string
    {
        $alphabetSize = strlen(self::ALPHABET);
        $code = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $alphabetSize - 1)];
        }

        return $code;
    }

    /** هل الكود مستخدم قبل كده؟ (بيشمل المنتجات المحذوفة soft-deleted) */
    public function exists(string $code): bool
    {
        return DB::table((new Product)->getTable())
            ->where('public_code', $code)
            ->exists();
    }

    /** هل الكود مطابق للصيغة المسموح بها؟ */
    public static function isValid(string $code): bool
    {
        return (bool) preg_match(
            '/^['.self::ALPHABET.']{'.self::LENGTH.'}$/',
            $code,
        );
    }
}

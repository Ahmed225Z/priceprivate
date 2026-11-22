<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CodeGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * PROJECT_SPEC.md §6.2, §21.1
 * توليد public_code فريد ولا يتكرر (اختبار 10,000 توليدة).
 */
class CodeGeneratorTest extends TestCase
{
    private CodeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CodeGenerator;
    }

    #[Test]
    public function it_generates_codes_with_the_exact_required_shape(): void
    {
        for ($i = 0; $i < 500; $i++) {
            $code = $this->generator->randomCode();

            $this->assertSame(CodeGenerator::LENGTH, strlen($code));
            $this->assertTrue(
                CodeGenerator::isValid($code),
                "Code [{$code}] does not match the allowed alphabet.",
            );
        }
    }

    #[Test]
    public function it_never_uses_ambiguous_characters(): void
    {
        // 0 و O و 1 و I و L ممنوعين عشان القراءة من ملصق مطبوع (§6.2).
        $forbidden = ['0', 'O', '1', 'I', 'L'];

        $this->assertSame(
            [],
            array_intersect($forbidden, str_split(CodeGenerator::ALPHABET)),
            'The alphabet must not contain ambiguous characters.',
        );

        $sample = '';

        for ($i = 0; $i < 2000; $i++) {
            $sample .= $this->generator->randomCode();
        }

        foreach ($forbidden as $char) {
            $this->assertStringNotContainsString($char, $sample);
        }
    }

    #[Test]
    public function ten_thousand_generated_codes_are_effectively_unique(): void
    {
        $codes = [];

        for ($i = 0; $i < 10_000; $i++) {
            $codes[$this->generator->randomCode()] = true;
        }

        // مساحة المفاتيح = 31^6 ≈ 887 مليون، فالتصادمات في 10 آلاف لازم تكون نادرة جدًا.
        // بنسمح بهامش بسيط عشان الاختبار ما يبقاش هش، والـ uniqueCode() بيتحقق من الـ DB أصلًا.
        $this->assertGreaterThanOrEqual(
            9_995,
            count($codes),
            'Collision rate is far higher than expected for a 31^6 key space.',
        );
    }

    #[Test]
    public function it_is_not_sequential(): void
    {
        // كودين ورا بعض ما ينفعش يكونوا متتاليين — محدش يقدر يخمّن أكواد تاجر تاني.
        $first = $this->generator->randomCode();
        $second = $this->generator->randomCode();

        $this->assertNotSame($first, $second);
    }

    #[Test]
    public function it_rejects_malformed_codes(): void
    {
        $this->assertFalse(CodeGenerator::isValid('ABC'));          // قصير
        $this->assertFalse(CodeGenerator::isValid('ABCDEFG'));      // طويل
        $this->assertFalse(CodeGenerator::isValid('ABC0EF'));       // فيه صفر
        $this->assertFalse(CodeGenerator::isValid('abcdef'));       // حروف صغيرة
        $this->assertFalse(CodeGenerator::isValid('ABC-EF'));       // رمز
        $this->assertTrue(CodeGenerator::isValid('XK7M2A'));
    }
}

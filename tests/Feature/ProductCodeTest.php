<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CodeGenerator;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * PROJECT_SPEC.md §6.1, §6.2, §15.2, §21.1 — القرار D3.
 *
 * 🔒 الملصق المطبوع في المحل لازم يفضل شغال للأبد.
 */
class ProductCodeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_new_product_gets_a_valid_code_automatically(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->public_code);
        $this->assertTrue(CodeGenerator::isValid($product->public_code));
    }

    #[Test]
    public function codes_are_unique_across_the_whole_platform(): void
    {
        $products = Product::factory()->count(60)->create();

        $this->assertCount(
            60,
            $products->pluck('public_code')->unique(),
            'Two products ended up sharing a public_code.',
        );
    }

    #[Test]
    public function renaming_a_product_does_not_change_its_code(): void
    {
        $product = Product::factory()->create(['name' => 'زيت عافية 1 لتر']);
        $original = $product->public_code;

        $product->update(['name' => 'زيت عافية 2 لتر']);

        $this->assertSame($original, $product->fresh()->public_code);
    }

    #[Test]
    public function changing_the_price_does_not_change_the_code(): void
    {
        $product = Product::factory()->create(['price' => 95.50]);
        $original = $product->public_code;

        $product->update(['price' => 112.25]);

        $this->assertSame($original, $product->fresh()->public_code);
    }

    #[Test]
    public function moving_a_product_to_another_category_does_not_change_the_code(): void
    {
        $product = Product::factory()->create();
        $original = $product->public_code;

        $product->update(['category_id' => null]);

        $this->assertSame($original, $product->fresh()->public_code);
    }

    #[Test]
    public function hiding_a_product_does_not_change_the_code(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $original = $product->public_code;

        $product->update(['is_visible' => false]);

        $this->assertSame($original, $product->fresh()->public_code);
    }

    #[Test]
    public function the_code_cannot_be_overwritten_even_deliberately(): void
    {
        $product = Product::factory()->create();

        $this->expectException(RuntimeException::class);

        $product->public_code = 'XXXXXX';
        $product->save();
    }

    #[Test]
    public function the_code_is_not_mass_assignable(): void
    {
        $product = Product::factory()->create();
        $original = $product->public_code;

        // preventSilentlyDiscardingAttributes مفعّل في AppServiceProvider (§14.3)،
        // فمحاولة الـ mass assignment بترمي استثناء بدل ما تتجاهل بصمت.
        try {
            $product->fill(['public_code' => 'ZZZZZZ']);
            $this->fail('Mass assigning public_code should have been rejected.');
        } catch (MassAssignmentException) {
            // ده المتوقع.
        }

        $this->assertSame($original, $product->fresh()->public_code);
    }

    #[Test]
    public function updating_a_product_cannot_smuggle_a_new_code_through_mass_assignment(): void
    {
        $product = Product::factory()->create();
        $original = $product->public_code;

        try {
            $product->update(['name' => 'اسم جديد', 'public_code' => 'ZZZZZZ']);
        } catch (MassAssignmentException) {
            // مرفوض — وده المطلوب.
        }

        $this->assertSame($original, $product->fresh()->public_code);
    }

    #[Test]
    public function a_soft_deleted_product_keeps_its_code_so_fallback_can_resolve_it(): void
    {
        // §6.5: كود لمنتج محذوف لازم يوصّل لقائمة المحل مش 404.
        $product = Product::factory()->create();
        $code = $product->public_code;

        $product->delete();

        $this->assertDatabaseHas('products', [
            'public_code' => $code,
        ]);

        $this->assertNotNull(Product::withTrashed()->where('public_code', $code)->first());
    }

    #[Test]
    public function a_deleted_products_code_is_never_handed_to_a_new_product(): void
    {
        $product = Product::factory()->create();
        $code = $product->public_code;
        $product->delete();

        $this->assertTrue(
            app(CodeGenerator::class)->exists($code),
            'A soft-deleted code must still count as taken.',
        );
    }

    #[Test]
    public function the_route_key_is_the_public_code(): void
    {
        $product = Product::factory()->create();

        $this->assertSame('public_code', $product->getRouteKeyName());
        $this->assertStringEndsWith('/p/'.$product->public_code, $product->publicUrl());
    }
}

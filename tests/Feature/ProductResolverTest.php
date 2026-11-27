<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductResolutionStatus as S;
use App\Models\Merchant;
use App\Models\Product;
use App\Services\ProductResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** SRS §8.3 behaviour table: every row has a test. */
class ProductResolverTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $merchant = [], array $product = []): Product
    {
        $m = Merchant::factory()->create($merchant);

        return Product::factory()->create(array_merge(['merchant_id' => $m->id, 'category_id' => null], $product));
    }

    #[Test]
    public function ok_returns_200_and_shows_price(): void
    {
        $p = $this->product();
        $r = app(ProductResolver::class)->resolve($p->public_code);
        $this->assertSame(S::Ok, $r->status);
        $this->assertSame(200, $r->httpStatus());
        $this->assertTrue($r->shouldShowPrice());
        $this->get('/p/'.$p->public_code)->assertOk()->assertSee($p->name);
    }

    #[Test]
    public function hidden_returns_200_without_price(): void
    {
        $p = $this->product(product: ['is_visible' => false, 'price' => 123.45]);
        $r = app(ProductResolver::class)->resolve($p->public_code);
        $this->assertSame(S::Hidden, $r->status);
        $this->assertFalse($r->shouldShowPrice());
        $this->get('/p/'.$p->public_code)->assertOk()->assertSee('المنتج ده مش متاح دلوقتي')->assertDontSee('123.45');
    }

    #[Test]
    public function deleted_product_redirects_to_store(): void
    {
        $p = $this->product();
        $slug = $p->merchant->slug;
        $p->delete();
        $this->assertSame(S::ProductDeleted, app(ProductResolver::class)->resolve($p->public_code)->status);
        $this->get('/p/'.$p->public_code)->assertRedirect('/'.$slug);
    }

    #[Test]
    public function suspended_merchant_returns_403_without_contact_data(): void
    {
        $p = $this->product(['status' => 'suspended', 'phone' => '01011112222']);
        $this->get('/p/'.$p->public_code)->assertStatus(403)->assertDontSee('01011112222');
    }

    #[Test]
    public function deleted_merchant_returns_410(): void
    {
        $p = $this->product();
        $p->merchant->delete();
        $this->assertSame(S::MerchantDeleted, app(ProductResolver::class)->resolve($p->public_code)->status);
        $this->get('/p/'.$p->public_code)->assertStatus(410);
    }

    #[Test]
    public function unknown_or_invalid_code_returns_friendly_404(): void
    {
        $this->assertSame(S::NotFound, app(ProductResolver::class)->resolve('ZZZZZZ')->status);
        $this->assertSame(S::NotFound, app(ProductResolver::class)->resolve('abc')->status);
        $this->get('/p/ZZZZZZ')->assertStatus(404)->assertSee('الكود ده مش موجود');
    }

    #[Test]
    public function expired_subscription_does_not_affect_the_page(): void
    {
        $p = $this->product();
        $this->assertSame(S::Ok, app(ProductResolver::class)->resolve($p->public_code)->status);
    }

    #[Test]
    public function scan_is_counted_with_source_and_no_personal_data(): void
    {
        $p = $this->product();
        $this->get('/p/'.$p->public_code.'?s=qr')->assertOk();
        $this->get('/p/'.$p->public_code.'?s=qr')->assertOk();
        $this->get('/p/'.$p->public_code)->assertOk();

        $this->assertDatabaseHas('scan_stats', ['merchant_id' => $p->merchant_id, 'target_type' => 'product', 'target_id' => $p->id, 'source' => 'qr', 'count' => 2]);
        $this->assertDatabaseHas('scan_stats', ['source' => 'direct', 'count' => 1]);
    }

    #[Test]
    public function p_route_is_not_swallowed_by_the_slug_catch_all(): void
    {
        $p = $this->product();
        $this->get('/p/'.$p->public_code)->assertOk()->assertSee($p->name);
        $this->get('/'.$p->merchant->slug.'/p/'.$p->public_code)->assertOk()->assertSee($p->name);
        $this->get('/'.$p->merchant->slug)->assertOk()->assertSee($p->merchant->store_name);
    }
}

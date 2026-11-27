<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\UpdateProductPrice;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ProductLimitChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProductPriceTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        $m = Merchant::factory()->create();

        return Product::factory()->create(array_merge(['merchant_id' => $m->id, 'category_id' => null, 'price' => 50], $attrs));
    }

    #[Test]
    public function it_updates_price_history_and_bumps_cache_version(): void
    {
        $p = $this->product();
        $before = $p->merchant->cache_version;

        $changed = app(UpdateProductPrice::class)->handle($p, 95.5);

        $this->assertTrue($changed);
        $this->assertSame('95.50', $p->fresh()->price);
        $this->assertDatabaseHas('price_history', ['product_id' => $p->id, 'old_price' => '50.00', 'new_price' => '95.50']);
        $this->assertSame($before + 1, $p->merchant->fresh()->cache_version);
        $this->assertNotNull($p->merchant->fresh()->prices_updated_at);
    }

    #[Test]
    public function same_price_writes_nothing(): void
    {
        $p = $this->product();
        $this->assertFalse(app(UpdateProductPrice::class)->handle($p, 50));
        $this->assertDatabaseCount('price_history', 0);
    }

    #[Test]
    public function null_price_means_not_available(): void
    {
        $p = $this->product();
        app(UpdateProductPrice::class)->handle($p, null);
        $this->assertNull($p->fresh()->price);
    }

    #[Test]
    public function the_public_code_never_changes_when_price_changes(): void
    {
        $p = $this->product();
        $code = $p->public_code;
        app(UpdateProductPrice::class)->handle($p, 99);
        $this->assertSame($code, $p->fresh()->public_code);
    }

    #[Test]
    public function free_limit_comes_from_settings(): void
    {
        $m = Merchant::factory()->create();
        Setting::put(Setting::FREE_PRODUCT_LIMIT, 2);
        $checker = app(ProductLimitChecker::class);

        $this->assertSame(2, $checker->limitFor($m));
        Product::factory()->count(2)->create(['merchant_id' => $m->id, 'category_id' => null]);
        $this->assertFalse($checker->canAdd($m));
        $this->assertSame(0, $checker->remaining($m));
    }
}

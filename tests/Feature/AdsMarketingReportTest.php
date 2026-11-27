<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdSlot;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdsMarketingReportTest extends TestCase
{
    use RefreshDatabase;

    private function adSlot(): void
    {
        AdSlot::create([
            'code' => AdSlot::PRODUCT_BELOW_CONTACT, 'name' => 'x', 'is_active' => true,
            'type' => 'adsense', 'adsense_code' => '<div id="ad-marker"></div>', 'min_height' => 120,
        ]);
    }

    #[Test]
    public function free_merchants_show_the_ad_below_the_contact_buttons_never_above_price(): void
    {
        $this->adSlot();
        $m = Merchant::factory()->create();
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null, 'price' => 77]);

        $html = $this->get('/p/'.$p->public_code)->assertOk()->assertSee('ad-marker', false)->getContent();
        $this->assertLessThan(strpos($html, 'ad-marker'), strpos($html, '77'));
        $this->assertStringContainsString('min-height:120px', $html);
    }

    #[Test]
    public function paid_merchants_show_no_ads(): void
    {
        $this->adSlot();
        Plan::factory()->free()->create();
        $pro = Plan::factory()->create();
        $m = Merchant::factory()->create();
        $pay = Payment::factory()->create(['merchant_id' => $m->id, 'plan_id' => $pro->id]);
        app(SubscriptionService::class)->activate($pay);
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null]);

        $this->get('/p/'.$p->public_code)->assertOk()->assertDontSee('ad-marker', false);
    }

    #[Test]
    public function inactive_slots_are_not_shown(): void
    {
        $this->adSlot();
        AdSlot::query()->update(['is_active' => false]);
        $m = Merchant::factory()->create();
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null]);
        $this->get('/p/'.$p->public_code)->assertDontSee('ad-marker', false);
    }

    #[Test]
    public function pricing_reads_the_plans_table(): void
    {
        Plan::factory()->free()->create();
        Plan::factory()->create(['price' => 321]);
        $this->get('/pricing')->assertOk()->assertSee('321')->assertSee('الأكثر طلبًا');
    }

    #[Test]
    public function marketing_pages_load_and_do_not_shadow_the_slug_route(): void
    {
        foreach (['/', '/how-it-works', '/pricing', '/faq', '/terms', '/privacy', '/contact'] as $u) {
            $this->get($u)->assertOk();
        }
    }

    #[Test]
    public function reports_store_a_hashed_ip_and_are_rate_limited(): void
    {
        $m = Merchant::factory()->create();
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/report/product/'.$p->id, ['reason' => 'wrong_price'])->assertRedirect();
        }
        $this->post('/report/product/'.$p->id, ['reason' => 'wrong_price'])->assertStatus(429);

        $r = \App\Models\Report::first();
        $this->assertSame(64, strlen($r->reporter_ip_hash));
        $this->assertNotSame('127.0.0.1', $r->reporter_ip_hash);
    }
}

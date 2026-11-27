<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\Payments\MockGateway;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function setupPayment(): Payment
    {
        Plan::factory()->free()->create();
        $pro = Plan::factory()->create(['duration_days' => 365]);
        $m = Merchant::factory()->create();

        return Payment::factory()->create(['merchant_id' => $m->id, 'plan_id' => $pro->id]);
    }

    private function hook(Payment $p, string $txn = 'tx-1', string $status = 'paid', ?string $sig = null)
    {
        $body = json_encode(['order_id' => $p->gateway_order_id, 'transaction_id' => $txn, 'status' => $status]);

        return $this->call('POST', '/payment/webhook/mock', [], [], [], [
            'HTTP_X_SIGNATURE' => $sig ?? MockGateway::sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }

    #[Test]
    public function a_valid_signature_activates_the_subscription(): void
    {
        $p = $this->setupPayment();
        $this->hook($p)->assertOk();

        $this->assertSame('paid', $p->fresh()->status);
        $sub = $p->merchant->subscriptions()->first();
        $this->assertSame('active', $sub->status);
        $this->assertEqualsWithDelta(now()->addDays(365)->timestamp, $sub->ends_at->timestamp, 5);
        $this->assertTrue(app(SubscriptionService::class)->hasPaidFeatures($p->merchant));
    }

    #[Test]
    public function a_wrong_signature_is_rejected_and_nothing_changes(): void
    {
        $p = $this->setupPayment();
        $this->hook($p, sig: 'nope')->assertForbidden();

        $this->assertSame('pending', $p->fresh()->status);
        $this->assertSame(0, Subscription::count());
    }

    #[Test]
    public function the_same_webhook_twice_makes_one_subscription(): void
    {
        $p = $this->setupPayment();
        $this->hook($p)->assertOk();
        $this->hook($p)->assertOk();

        $this->assertSame(1, Subscription::count());
        $this->assertSame(1, Payment::where('status', 'paid')->count());
    }

    #[Test]
    public function a_failed_payment_does_not_activate(): void
    {
        $p = $this->setupPayment();
        $this->hook($p, status: 'failed')->assertOk();
        $this->assertSame('failed', $p->fresh()->status);
        $this->assertSame(0, Subscription::count());
    }

    #[Test]
    public function the_callback_page_never_activates(): void
    {
        $p = $this->setupPayment();
        $this->get('/payment/callback?order='.$p->gateway_order_id)->assertOk();
        $this->assertSame('pending', $p->fresh()->status);
        $this->assertSame(0, Subscription::count());
    }

    #[Test]
    public function renewing_before_expiry_extends_from_ends_at(): void
    {
        $p = $this->setupPayment();
        $svc = app(SubscriptionService::class);
        $first = $svc->activate($p);
        $endsBefore = $first->ends_at->copy();

        $p2 = Payment::factory()->create(['merchant_id' => $p->merchant_id, 'plan_id' => $p->plan_id]);
        $second = $svc->activate($p2);

        $this->assertSame($first->id, $second->id);
        $this->assertEqualsWithDelta($endsBefore->copy()->addDays(365)->timestamp, $second->ends_at->timestamp, 5);
    }

    #[Test]
    public function expiry_goes_grace_then_free_and_the_public_page_stays_up(): void
    {
        $p = $this->setupPayment();
        Plan::where('code', Plan::FREE)->update(['product_limit' => 2]);
        $m = $p->merchant;
        $products = Product::factory()->count(4)->create(['merchant_id' => $m->id, 'category_id' => null]);
        foreach ($products as $i => $prod) {
            $prod->update(['sort_order' => $i]);
        }
        $sub = app(SubscriptionService::class)->activate($p);
        $svc = app(SubscriptionService::class);

        $sub->forceFill(['ends_at' => now()->subDay(), 'grace_ends_at' => now()->addDays(6)])->save();
        $svc->roll();
        $this->assertSame('grace', $sub->fresh()->status);
        $this->assertTrue($svc->hasPaidFeatures($m));

        $sub->forceFill(['grace_ends_at' => now()->subMinute()])->save();
        $svc->roll();
        $this->assertSame('expired', $sub->fresh()->status);
        $this->assertFalse($svc->hasPaidFeatures($m));

        // Excess products are hidden, never deleted, newest last.
        $this->assertSame(4, $m->products()->count());
        $this->assertSame(2, $m->products()->where('is_visible', true)->count());

        // D8: the public page for a still-visible product is still 200.
        $visible = $m->products()->where('is_visible', true)->first();
        $this->get('/p/'.$visible->public_code)->assertOk();
        $this->get('/'.$m->slug)->assertOk();

        // Renewal brings back exactly those products.
        $p3 = Payment::factory()->create(['merchant_id' => $m->id, 'plan_id' => $p->plan_id]);
        $svc->activate($p3);
        $this->assertSame(4, $m->products()->where('is_visible', true)->count());
    }

    #[Test]
    public function a_product_the_merchant_hid_stays_hidden_after_renewal(): void
    {
        $p = $this->setupPayment();
        $m = $p->merchant;
        $mine = Product::factory()->hidden()->create(['merchant_id' => $m->id, 'category_id' => null]);
        app(SubscriptionService::class)->activate($p);
        $this->assertFalse($mine->fresh()->is_visible);
    }

    #[Test]
    public function upgrade_creates_a_pending_payment_and_redirects(): void
    {
        Plan::factory()->free()->create();
        Plan::factory()->create();
        $m = Merchant::factory()->create();

        $res = $this->actingAs($m->user)->post('/dashboard/subscription/upgrade');
        $res->assertRedirect();
        $this->assertDatabaseHas('payments', ['merchant_id' => $m->id, 'status' => 'pending', 'gateway' => 'mock']);
    }
}

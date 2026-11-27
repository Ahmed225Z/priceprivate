<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PrintTemplate;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Services\PrintSheetService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudioInvoiceSeoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_remaining_print_templates_produce_pdfs(): void
    {
        Storage::fake('public');
        $m = Merchant::factory()->create();
        $products = Product::factory()->count(2)->create(['merchant_id' => $m->id, 'category_id' => null]);

        foreach ([PrintTemplate::ShelfTalker, PrintTemplate::TableStand, PrintTemplate::Poster] as $t) {
            $path = app(PrintSheetService::class)->generate($m, $t, $products);
            $this->assertStringStartsWith('%PDF', file_get_contents($path), $t->value);
            unlink($path);
        }
    }

    #[Test]
    public function reorder_changes_sort_order_and_ignores_foreign_ids(): void
    {
        $a = Merchant::factory()->create();
        $b = Merchant::factory()->create();
        $p1 = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null, 'sort_order' => 0]);
        $p2 = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null, 'sort_order' => 1]);
        $foreign = Product::factory()->create(['merchant_id' => $b->id, 'category_id' => null, 'sort_order' => 7]);

        $this->actingAs($a->user)->postJson('/dashboard/products/reorder', ['ids' => [$p2->id, $foreign->id, $p1->id]])->assertOk();

        $this->assertSame(0, $p2->fresh()->sort_order);
        $this->assertSame(1, $p1->fresh()->sort_order);
        $this->assertSame(7, $foreign->fresh()->sort_order);
    }

    #[Test]
    public function qr_studio_previews_only_own_targets_and_changes_default_target(): void
    {
        Storage::fake('public');
        $a = Merchant::factory()->create();
        $b = Merchant::factory()->create();
        $mine = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null]);
        $theirs = Product::factory()->create(['merchant_id' => $b->id, 'category_id' => null]);

        $this->actingAs($a->user)->get('/dashboard/qr/preview?target=product:'.$mine->id.'&size=300')->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->actingAs($a->user)->get('/dashboard/qr/preview?target=product:'.$theirs->id)->assertNotFound();
        $this->actingAs($a->user)->get('/dashboard/qr/preview?target=store&fg=zzz')->assertSessionHasErrors('fg');

        $this->actingAs($a->user)->post('/dashboard/qr/default', ['target' => 'product:'.$mine->id])->assertRedirect();
        $this->assertSame($mine->publicUrl(), $a->fresh()->defaultTargetUrl());

        // Target deleted for good: the code falls back to the store page.
        $mine->forceDelete();
        $this->assertSame($a->fresh()->publicUrl(), $a->fresh()->defaultTargetUrl());
    }

    #[Test]
    public function paying_creates_an_invoice_pdf_only_the_owner_can_download(): void
    {
        Plan::factory()->free()->create();
        $pro = Plan::factory()->create();
        $m = Merchant::factory()->create();
        $other = Merchant::factory()->create();
        $pay = Payment::factory()->create(['merchant_id' => $m->id, 'plan_id' => $pro->id]);

        $body = json_encode(['order_id' => $pay->gateway_order_id, 'transaction_id' => 'tx-9', 'status' => 'paid']);
        $this->call('POST', '/payment/webhook/mock', [], [], [], [
            'HTTP_X_SIGNATURE' => \App\Services\Payments\MockGateway::sign($body), 'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();

        $inv = $pay->fresh()->invoice;
        $this->assertNotNull($inv);
        $this->assertStringStartsWith('%PDF', file_get_contents($inv->pdf_path));

        $this->actingAs($m->user)->get('/dashboard/invoices/'.$inv->id)->assertOk();
        $this->actingAs($other->user)->get('/dashboard/invoices/'.$inv->id)->assertForbidden();
        $this->actingAs($m->user)->get('/dashboard/subscription')->assertOk()->assertSee($inv->number);
        unlink($inv->pdf_path);
    }

    #[Test]
    public function seo_and_security_headers(): void
    {
        $m = Merchant::factory()->create();
        $hidden = Product::factory()->hidden()->create(['merchant_id' => $m->id, 'category_id' => null]);

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /dashboard');
        $this->get('/sitemap.xml')->assertOk()->assertSee($m->slug);
        $this->get('/p/'.$hidden->public_code)->assertSee('noindex', false);
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    #[Test]
    public function creating_a_qr_and_sharing_are_recorded_for_the_funnel(): void
    {
        Storage::fake('public');
        $a = Merchant::factory()->create();
        $b = Merchant::factory()->create();
        $mine = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null]);
        $theirs = Product::factory()->create(['merchant_id' => $b->id, 'category_id' => null]);

        $this->actingAs($a->user)->get('/dashboard/qr/preview?target=product:'.$mine->id)->assertOk();
        $this->actingAs($a->user)->get('/dashboard/qr/preview?target=product:'.$mine->id)->assertOk();
        $this->assertDatabaseCount('share_targets', 1);
        $this->assertNull(\App\Models\ShareTarget::first()->shared_at);

        $this->actingAs($a->user)->get('/dashboard/qr/preview?target=product:'.$theirs->id)->assertNotFound();
        $this->assertDatabaseCount('share_targets', 1);

        $this->actingAs($a->user)->postJson('/dashboard/share', ['target' => 'store'])->assertOk();
        $this->actingAs($a->user)->postJson('/dashboard/share', ['target' => 'product:'.$mine->id])->assertOk();
        $this->actingAs($a->user)->postJson('/dashboard/share', ['target' => 'product:'.$theirs->id])->assertNotFound();
        $this->assertDatabaseCount('share_targets', 2);
        $this->assertSame(2, \App\Models\ShareTarget::whereNotNull('shared_at')->count());
        $this->assertSame(0, \App\Models\ShareTarget::where('merchant_id', $b->id)->count());
    }
}

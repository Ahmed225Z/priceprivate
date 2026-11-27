<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\Product;
use App\Services\QrOptions;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QrServiceTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $m = Merchant::factory()->create();

        return Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null]);
    }

    #[Test]
    public function it_generates_png_and_svg_and_caches_them(): void
    {
        Storage::fake('public');
        $p = $this->product();
        $svc = app(QrService::class);

        $png = $svc->forProduct($p, new QrOptions(format: 'png', size: 300));
        $svg = $svc->forProduct($p, new QrOptions(format: 'svg', size: 300));

        Storage::disk('public')->assertExists($png);
        Storage::disk('public')->assertExists($svg);
        $this->assertStringStartsWith("\x89PNG", Storage::disk('public')->get($png));
        $this->assertSame($png, $svc->forProduct($p, new QrOptions(format: 'png', size: 300)));
    }

    #[Test]
    public function the_route_serves_an_immutable_cached_image(): void
    {
        Storage::fake('public');
        $p = $this->product();

        $res = $this->get('/qr/'.$p->public_code.'.png');
        $res->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringContainsString('immutable', $res->headers->get('Cache-Control'));
    }

    #[Test]
    public function a_deleted_product_still_has_its_qr(): void
    {
        Storage::fake('public');
        $p = $this->product();
        $p->delete();

        $this->get('/qr/'.$p->public_code.'.svg')->assertOk();
    }

    #[Test]
    public function unknown_code_is_404(): void
    {
        $this->get('/qr/ZZZZZZ.png')->assertNotFound();
    }
}

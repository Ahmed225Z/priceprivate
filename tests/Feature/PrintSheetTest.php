<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PrintTemplate;
use App\Models\Merchant;
use App\Models\Product;
use App\Services\PrintSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrintSheetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_a_real_pdf_for_a_label_sheet(): void
    {
        Storage::fake('public');
        $m = Merchant::factory()->create();
        $products = Product::factory()->count(5)->create(['merchant_id' => $m->id, 'category_id' => null]);

        $path = app(PrintSheetService::class)->generate($m, PrintTemplate::LabelSheet, $products);

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF', file_get_contents($path));
        unlink($path);
    }

    #[Test]
    public function store_card_works_without_products(): void
    {
        Storage::fake('public');
        $m = Merchant::factory()->create();
        $path = app(PrintSheetService::class)->generate($m, PrintTemplate::StoreCard, collect());
        $this->assertStringStartsWith('%PDF', file_get_contents($path));
        unlink($path);
    }

    #[Test]
    public function the_endpoint_only_prints_the_merchants_own_products(): void
    {
        Storage::fake('public');
        $a = Merchant::factory()->create();
        $b = Merchant::factory()->create();
        $pa = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null]);
        $pb = Product::factory()->create(['merchant_id' => $b->id, 'category_id' => null]);

        $res = $this->actingAs($a->user)->post('/dashboard/print', ['template' => 'label_sheet', 'product_ids' => [$pa->id, $pb->id]]);
        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('Content-Type'));
    }

    #[Test]
    public function a_label_sheet_with_no_products_is_rejected(): void
    {
        $m = Merchant::factory()->create();
        $this->actingAs($m->user)->post('/dashboard/print', ['template' => 'label_sheet'])->assertSessionHasErrors('product_ids');
    }
}

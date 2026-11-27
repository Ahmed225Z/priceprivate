<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MerchantCrudTest extends TestCase
{
    use RefreshDatabase;

    private function merchant(): Merchant
    {
        return Merchant::factory()->create();
    }

    #[Test]
    public function merchant_can_add_a_product_with_a_price(): void
    {
        $m = $this->merchant();
        $this->actingAs($m->user)->post('/dashboard/products', ['name' => 'سكر 1 كيلو', 'price' => '38.50'])->assertRedirect();

        $p = Product::where('merchant_id', $m->id)->firstOrFail();
        $this->assertSame('38.50', $p->price);
        $this->assertSame(1, $m->fresh()->products_count);
        $this->assertSame(6, strlen($p->public_code));
    }

    #[Test]
    public function product_limit_blocks_adding(): void
    {
        Setting::put(Setting::FREE_PRODUCT_LIMIT, 1);
        $m = $this->merchant();
        $this->actingAs($m->user)->post('/dashboard/products', ['name' => 'أ']);
        $this->actingAs($m->user)->post('/dashboard/products', ['name' => 'ب'])->assertSessionHasErrors('name');
        $this->assertSame(1, $m->products()->count());
    }

    #[Test]
    public function bulk_price_save_ignores_other_merchants_products(): void
    {
        $a = $this->merchant();
        $b = $this->merchant();
        $pa = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null, 'price' => 10]);
        $pb = Product::factory()->create(['merchant_id' => $b->id, 'category_id' => null, 'price' => 10]);

        $this->actingAs($a->user)->post('/dashboard/products/prices', ['prices' => [$pa->id => '12.5', $pb->id => '99']]);

        $this->assertSame('12.50', $pa->fresh()->price);
        $this->assertSame('10.00', $pb->fresh()->price);
    }

    #[Test]
    public function deleting_a_product_is_soft_and_other_merchants_get_403(): void
    {
        $a = $this->merchant();
        $b = $this->merchant();
        $p = Product::factory()->create(['merchant_id' => $a->id, 'category_id' => null]);

        $this->actingAs($b->user)->delete('/dashboard/products/'.$p->public_code)->assertForbidden();
        $this->actingAs($a->user)->delete('/dashboard/products/'.$p->public_code)->assertRedirect();

        $this->assertSoftDeleted('products', ['id' => $p->id]);
        $this->get('/p/'.$p->public_code)->assertRedirect('/'.$a->slug);
    }

    #[Test]
    public function deleting_a_category_keeps_its_products(): void
    {
        $m = $this->merchant();
        $c = Category::factory()->create(['merchant_id' => $m->id]);
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => $c->id]);

        $this->actingAs($m->user)->delete('/dashboard/categories/'.$c->id)->assertRedirect();

        $this->assertNull($p->fresh()->category_id);
    }

    #[Test]
    public function hiding_a_product_hides_its_price_publicly(): void
    {
        $m = $this->merchant();
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null, 'price' => 77]);

        $this->actingAs($m->user)->post('/dashboard/products/'.$p->public_code.'/toggle');

        $this->get('/p/'.$p->public_code)->assertOk()->assertSee('المنتج ده مش متاح دلوقتي');
    }
}

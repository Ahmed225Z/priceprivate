<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Merchants\FindOrCreateUser;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthAndPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function find_or_create_matches_provider_then_email_then_creates(): void
    {
        $a = app(FindOrCreateUser::class);

        $u1 = $a->handle('google', 'g1', 'a@x.com', 'A', null);
        $this->assertSame(User::ROLE_MERCHANT, $u1->role);

        $this->assertTrue($u1->is($a->handle('google', 'g1', 'other@x.com', 'A', null)));

        $linked = $a->handle('facebook', 'f1', 'a@x.com', 'A', null);
        $this->assertTrue($u1->is($linked));

        $noEmail = $a->handle('facebook', 'f2', null, 'B', null);
        $this->assertFalse($u1->is($noEmail));
        $this->assertNotNull($u1->fresh()->last_login_at);
    }

    #[Test]
    public function guests_are_sent_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    #[Test]
    public function user_without_merchant_goes_to_onboarding(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertRedirect('/onboarding');
    }

    #[Test]
    public function onboarding_creates_a_merchant_and_rejects_reserved_slugs(): void
    {
        $user = User::factory()->create();
        $payload = [
            'store_name' => 'محل الأمانة',
            'slug' => 'amana-store',
            'governorate' => config('egypt.governorates')[0],
            'business_type' => config('egypt.business_types')[0],
            'phone' => '01012345678',
        ];

        $this->actingAs($user)->post('/onboarding', array_merge($payload, ['slug' => 'dashboard']))->assertSessionHasErrors('slug');
        $this->actingAs($user)->post('/onboarding', array_merge($payload, ['phone' => '123']))->assertSessionHasErrors('phone');
        $this->actingAs($user)->post('/onboarding', $payload)->assertRedirect('/dashboard');

        $this->assertDatabaseHas('merchants', ['user_id' => $user->id, 'slug' => 'amana-store']);
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('محل الأمانة');
    }

    #[Test]
    public function merchant_cannot_touch_another_merchants_product(): void
    {
        $a = Merchant::factory()->create();
        $b = Merchant::factory()->create();
        $pb = Product::factory()->create(['merchant_id' => $b->id, 'category_id' => null]);

        $this->assertFalse($a->user->can('update', $pb));
        $this->assertFalse($a->user->can('delete', $pb));
        $this->assertTrue($b->user->can('update', $pb));
    }

    #[Test]
    public function facebook_login_is_hidden_until_enabled(): void
    {
        $this->get('/login')->assertOk()->assertSee('الدخول بجوجل')->assertDontSee('الدخول بفيسبوك');
        $this->get('/auth/facebook/redirect')->assertNotFound();
    }
}

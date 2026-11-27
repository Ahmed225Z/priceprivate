<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Payments\Pages\ManagePayments;
use App\Filament\Resources\Reports\Pages\ManageReports;
use App\Models\ActivityLog;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    #[Test]
    public function access_is_limited_to_staff(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $m = Merchant::factory()->create();
        $this->actingAs($m->user)->get('/admin')->assertForbidden();

        $this->actingAs($this->staff('admin'))->get('/admin')->assertOk()->assertSee('قمع الإكمال');
        $this->actingAs($this->staff('moderator'))->get('/admin/reports')->assertOk();
        $this->actingAs($this->staff('moderator'))->get('/admin/merchants')->assertForbidden();
        $this->actingAs($this->staff('admin'))->get('/admin/merchants')->assertOk();
    }

    #[Test]
    public function every_admin_page_renders(): void
    {
        Merchant::factory()->create();
        $this->actingAs($this->staff('admin'));
        foreach (['merchants', 'reports', 'payments', 'subscriptions', 'ad-slots', 'plans', 'settings', 'activity-logs'] as $p) {
            $this->get('/admin/'.$p)->assertOk();
        }
    }

    #[Test]
    public function acting_on_a_report_hides_the_product_and_is_logged(): void
    {
        $mod = $this->staff('moderator');
        $m = Merchant::factory()->create();
        $p = Product::factory()->create(['merchant_id' => $m->id, 'category_id' => null]);
        $r = Report::query()->create(['reportable_type' => $p->getMorphClass(), 'reportable_id' => $p->id, 'reason' => 'spam', 'status' => 'new']);

        $this->actingAs($mod);
        Livewire::test(ManageReports::class)->callTableAction('mark_actioned', $r, ['action_note' => 'سبام']);

        $this->assertFalse((bool) $p->fresh()->is_visible);
        $this->assertSame('actioned', $r->fresh()->status);
        $this->assertSame($mod->id, $r->fresh()->handled_by);
        $this->assertDatabaseHas('activity_log', ['event' => 'report.actioned', 'causer_id' => $mod->id]);
    }

    #[Test]
    public function manual_activation_makes_the_merchant_paid_and_is_logged(): void
    {
        Plan::factory()->free()->create();
        $pro = Plan::factory()->create();
        $m = Merchant::factory()->create();
        $pay = Payment::factory()->create(['merchant_id' => $m->id, 'plan_id' => $pro->id, 'status' => 'pending']);
        $admin = $this->staff('admin');

        $this->actingAs($admin);
        Livewire::test(ManagePayments::class)->callTableAction('manualActivate', $pay);

        $this->assertSame('paid', $pay->fresh()->status);
        $this->assertNotNull($pay->fresh()->subscription_id);
        $this->assertDatabaseHas('activity_log', ['event' => 'payment.manual_activate', 'causer_id' => $admin->id]);
    }

    #[Test]
    public function impersonation_rejects_get_guests_and_missing_csrf(): void
    {
        $m = Merchant::factory()->create();
        $admin = $this->staff('admin');

        $this->actingAs($admin)->get('/admin-tools/impersonate/'.$m->user_id)->assertStatus(405);
        $this->post('/admin-tools/impersonate-leave')->assertForbidden(); // not impersonating

        auth()->logout();
        $this->post('/admin-tools/impersonate/'.$m->user_id)->assertRedirect('/login');

        // Laravel skips the CSRF check while testing, so assert the routes sit behind it.
        foreach (['admin.impersonate.start', 'admin.impersonate.leave'] as $name) {
            $mw = app('router')->getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('web', $mw);
            $this->assertContains('auth', $mw);
            $this->assertSame(['POST'], array_values(array_diff(app('router')->getRoutes()->getByName($name)->methods(), ['HEAD'])));
        }
    }

    #[Test]
    public function impersonation_from_the_admin_table_is_admin_only(): void
    {
        $m = Merchant::factory()->create();
        $this->actingAs($this->staff('admin'));
        \Livewire\Livewire::test(\App\Filament\Resources\Merchants\Pages\ManageMerchants::class)->callTableAction('impersonate', $m);
        $this->assertAuthenticatedAs($m->user);
        $this->assertDatabaseHas('activity_log', ['event' => 'impersonate.start']);
    }

    #[Test]
    public function impersonation_is_admin_only_logged_and_shows_a_bar(): void
    {
        $m = Merchant::factory()->create();
        $other = Merchant::factory()->create();
        $admin = $this->staff('admin');

        $this->actingAs($other->user)->post('/admin-tools/impersonate/'.$m->user_id)->assertForbidden();
        $this->actingAs($this->staff('moderator'))->post('/admin-tools/impersonate/'.$m->user_id)->assertForbidden();

        $this->actingAs($admin)->post('/admin-tools/impersonate/'.$m->user_id)->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($m->user);
        $this->get('/dashboard')->assertOk()->assertSee('رجوع للأدمن');
        $this->assertDatabaseHas('activity_log', ['event' => 'impersonate.start', 'causer_id' => $admin->id]);

        $this->post('/admin-tools/impersonate-leave')->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('activity_log', ['event' => 'impersonate.stop', 'causer_id' => $admin->id]);
        $this->assertSame(2, ActivityLog::query()->count());
    }
}

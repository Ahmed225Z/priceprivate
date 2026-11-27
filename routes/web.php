<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Merchant\CategoryController;
use App\Http\Controllers\Merchant\PrintController;
use App\Http\Controllers\Merchant\QrStudioController;
use App\Http\Controllers\Merchant\SubscriptionController;
use App\Http\Controllers\Merchant\ProductController as MerchantProductController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QrImageController;
use App\Http\Middleware\TrackScan;
use Illuminate\Support\Facades\Route;

// SRS §5: the order is mandatory. Static routes first, the slug catch-all LAST.

// 1) Marketing (placeholder until P7)
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/how-it-works', [PageController::class, 'howItWorks']);
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/faq', [PageController::class, 'faq']);
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::view('/terms', 'marketing.terms');
Route::view('/privacy', 'marketing.privacy');
Route::view('/contact', 'marketing.contact');

// 2) Auth
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirect'])->whereIn('provider', ['google', 'facebook']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])->whereIn('provider', ['google', 'facebook']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store']);
});

// 3) Merchant dashboard
Route::middleware(['auth', 'merchant', 'onboarded'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/products', [MerchantProductController::class, 'index'])->name('dashboard.products');
    Route::post('/products', [MerchantProductController::class, 'store']);
    Route::post('/products/prices', [MerchantProductController::class, 'updatePrices']);
    Route::post('/products/{product}/toggle', [MerchantProductController::class, 'toggle']);
    Route::delete('/products/{product}', [MerchantProductController::class, 'destroy']);

    Route::get('/subscription', [SubscriptionController::class, 'show']);
    Route::get('/invoices/{invoice}', [SubscriptionController::class, 'invoice'])->whereNumber('invoice');
    Route::get('/qr', [QrStudioController::class, 'index']);
    Route::get('/qr/preview', [QrStudioController::class, 'preview']);
    Route::post('/qr/default', [QrStudioController::class, 'setDefault']);
    Route::post('/share', [QrStudioController::class, 'share']);
    Route::post('/products/reorder', [MerchantProductController::class, 'reorder']);
    Route::post('/subscription/upgrade', [PaymentController::class, 'upgrade']);
    Route::post('/print', [PrintController::class, 'generate'])->middleware('throttle:10,60');

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

// 4) Payments
Route::get('/payment/callback', [PaymentController::class, 'callback']);
Route::post('/payment/webhook/{gateway}', [PaymentController::class, 'webhook']);

// 5) Public assets
Route::get('/qr/{code}.{ext}', [QrImageController::class, 'show'])
    ->where('code', '[A-HJ-NP-Za-hj-np-z2-9]{6}')
    ->whereIn('ext', ['png', 'svg'])
    ->name('qr.show');

Route::post('/report/{type}/{id}', [ReportController::class, 'store'])->middleware('throttle:3,60')->whereNumber('id');

// 6) Direct product page, before the catch-all
Route::get('/p/{code}', [ProductController::class, 'show'])
    ->where('code', '[A-HJ-NP-Za-hj-np-z2-9]{6}')
    ->middleware(TrackScan::class)
    ->name('product.show');

// 7) Catch-all: keep these LAST
Route::get('/{slug}', [MenuController::class, 'show'])
    ->where('slug', '[a-z0-9][a-z0-9-]{1,38}[a-z0-9]')
    ->middleware(TrackScan::class)
    ->name('menu.show');

Route::get('/{slug}/p/{code}', [ProductController::class, 'showWithSlug'])
    ->where('slug', '[a-z0-9][a-z0-9-]{1,38}[a-z0-9]')
    ->where('code', '[A-HJ-NP-Za-hj-np-z2-9]{6}')
    ->middleware(TrackScan::class);

Route::get('/{slug}/{category}', [MenuController::class, 'category'])
    ->where('slug', '[a-z0-9][a-z0-9-]{1,38}[a-z0-9]')
    ->middleware(TrackScan::class)
    ->name('menu.category');

Route::middleware('auth')->group(function () {
    Route::post('/admin-tools/impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonateController::class, 'start'])->name('admin.impersonate.start')->whereNumber('user');
    Route::post('/admin-tools/impersonate-leave', [\App\Http\Controllers\Admin\ImpersonateController::class, 'leave'])->name('admin.impersonate.leave');
});

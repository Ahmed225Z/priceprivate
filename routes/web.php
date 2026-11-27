<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Merchant\CategoryController;
use App\Http\Controllers\Merchant\ProductController as MerchantProductController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QrImageController;
use App\Http\Middleware\TrackScan;
use Illuminate\Support\Facades\Route;

// SRS §5: the order is mandatory. Static routes first, the slug catch-all LAST.

// 1) Marketing (placeholder until P7)
Route::get('/', function () {
    return view('welcome');
})->name('home');

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

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

// 5) Public assets
Route::get('/qr/{code}.{ext}', [QrImageController::class, 'show'])
    ->where('code', '[A-HJ-NP-Za-hj-np-z2-9]{6}')
    ->whereIn('ext', ['png', 'svg'])
    ->name('qr.show');

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

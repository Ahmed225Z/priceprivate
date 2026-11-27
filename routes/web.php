<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QrImageController;
use App\Http\Middleware\TrackScan;
use Illuminate\Support\Facades\Route;

// SRS §5: the order is mandatory. Static routes first, the slug catch-all LAST.

// 1) Marketing (placeholder until P7)
Route::get('/', function () {
    return view('welcome');
})->name('home');

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

Route::get('/{slug}/p/{code}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9][a-z0-9-]{1,38}[a-z0-9]')
    ->where('code', '[A-HJ-NP-Za-hj-np-z2-9]{6}')
    ->middleware(TrackScan::class);

Route::get('/{slug}/{category}', [MenuController::class, 'category'])
    ->where('slug', '[a-z0-9][a-z0-9-]{1,38}[a-z0-9]')
    ->middleware(TrackScan::class)
    ->name('menu.category');

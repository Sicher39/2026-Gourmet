<?php

use App\Http\Controllers\Compliance\ConsentController;
use App\Http\Controllers\Compliance\CookieConfigController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\EventGalleryController;
use App\Http\Controllers\Front\GdprController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\MenuController;
use App\Http\Controllers\Front\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/api/compliance/cookie-config', CookieConfigController::class)->name('api.compliance.cookie-config');
Route::post('/api/compliance/consent', ConsentController::class)->middleware('throttle:60,1')->name('api.compliance.consent');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware('vue')->name('front.')->group(function () {
    Route::get('/', HomeController::class)->name('index');
    Route::inertia('/gourmet-ponavka', 'PonavkaBranch')->name('ponavka-branch');
    Route::inertia('/gourmet-u-vankovky', 'VankovkaBranch')->name('vankovka-branch');
    Route::inertia('/zasady-cookies', 'CookiePolicy')->name('cookies');
});

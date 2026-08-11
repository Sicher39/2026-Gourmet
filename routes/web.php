<?php

use App\Http\Controllers\Compliance\ConsentController;
use App\Http\Controllers\Compliance\CookieConfigController;
use App\Http\Controllers\Front\BranchController;
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
    Route::get('/gourmet-ponavka', [BranchController::class, 'ponavka'])->name('ponavka-branch');
    Route::get('/gourmet-u-vankovky', [BranchController::class, 'vankovka'])->name('vankovka-branch');
    Route::get('/screen-ponavka-branch', [BranchController::class, 'screenPonavka'])->name('screen-ponavka-branch');
    Route::get('/screen-vankovka-branch', [BranchController::class, 'screenVankovka'])->name('screen-vankovka-branch');
    Route::get('/ochrana-osobnich-udaju', GdprController::class)->name('gdpr');
    Route::inertia('/zasady-cookies', 'CookiePolicy')->name('cookies');
});

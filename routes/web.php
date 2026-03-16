<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('prevent_admin_customer')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/products', [HomeController::class, 'products'])->name('products.index');
    Route::get('/products/{slug}', [HomeController::class, 'productShow'])->name('products.show');
    Route::get('/category/{slug}', [HomeController::class, 'categoryShow'])->name('category.show');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

    // Blog
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

    // City landing pages
    Route::get('/makhana-in-{district}', [HomeController::class, 'districtLanding'])->name('city.landing.district');
    Route::get('/buy-makhana-online-{city}', [HomeController::class, 'cityLanding'])->name('city.landing.city');
    Route::get('/seo/k/{id}-{slug?}', [HomeController::class, 'keywordLanding'])
        ->whereNumber('id')
        ->name('seo.keyword.landing');
});

// SEO - Robots.txt
Route::get('/robots.txt', function () {
    $service = new \App\Modules\Shared\Services\RobotsTxtService();
    return response($service->generate(), 200, ['Content-Type' => 'text/plain']);
})->name('robots.txt');

// PWA - Manifest and Service Worker
Route::get('/manifest.json', function () {
    $service = new \App\Modules\Shared\Services\PwaService();
    return response($service->generateManifest(), 200, ['Content-Type' => 'application/json']);
})->name('pwa.manifest');

Route::get('/service-worker.js', function () {
    $service = new \App\Modules\Shared\Services\PwaService();
    return response($service->generateServiceWorker(), 200, ['Content-Type' => 'application/javascript']);
})->name('pwa.service-worker');

Route::get('/offline.html', function () {
    $service = new \App\Modules\Shared\Services\PwaService();
    return response($service->generateOfflinePage(), 200, ['Content-Type' => 'text/html']);
})->name('pwa.offline');

// SEO Sitemaps
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-index.xml', [SitemapController::class, 'indexXml'])->name('sitemap.index');
Route::get('/sitemap-main.xml', [SitemapController::class, 'mainXml'])->name('sitemap.main');
Route::get('/sitemap-products.xml', [SitemapController::class, 'productsXml'])->name('sitemap.products');
Route::get('/sitemap-cities.xml', [SitemapController::class, 'citiesXml'])->name('sitemap.cities');
Route::get('/sitemap-blog.xml', [SitemapController::class, 'blogXml'])->name('sitemap.blog');
Route::get('/sitemap-keywords-{part}.xml', [SitemapController::class, 'keywordsXml'])
    ->whereNumber('part')
    ->name('sitemap.keywords');

// Email verification route (opens in browser from email link)
Route::get('/email/verify/{id}/{hash}', function () {
    return Inertia::render('Auth/VerifyEmail');
})->middleware(['signed'])->name('verification.verify');

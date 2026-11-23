<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\SiteController;
use App\Support\ImageUpload;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/admin.php';
require __DIR__.'/donations.php';

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/about', fn () => redirect()->to(url('about-us').'/', 301));
Route::get('/updates', [SiteController::class, 'updates'])->name('site.updates');
Route::get('/contact-us', [ContactController::class, 'show'])->name('site.contact');
Route::post('/contact-us', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('site.contact.store');
Route::get('/donation-history', [SiteController::class, 'gallery'])->name('site.gallery');
Route::get('/gallery', fn () => redirect()->to(url('donation-history').'/', 301));
Route::get('/events', [SiteController::class, 'events'])->name('site.events');
Route::get('/events/{slug}', [SiteController::class, 'event'])->where('slug', '[a-z0-9-]+')->name('site.event');
Route::get('/search', [SiteController::class, 'search'])->name('site.search');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('site.sitemap');
Route::get('/robots.txt', [SiteController::class, 'robots'])->name('site.robots');
Route::get('/site/{slug}', [SiteController::class, 'oldDirectory'])->where('slug', '[a-z0-9-]+');
Route::get('/nasenyi', fn () => redirect()->route('site.home', status: 301));
Route::get('/product/donation-to-nasenyi-jewish-community', fn () => redirect()->to(url('donation').'/', 301));

Route::get('/storage/{folder}/{filename}', function (string $folder, string $filename) {
    $type = ImageUpload::mimeForExtension(strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)));
    abort_unless(in_array($folder, ['content', 'gallery'], true) && $type !== null && config('filesystems.disks.public.driver') === 'local', 404);

    $root = realpath(Storage::disk('public')->path($folder));
    abort_if($root === false, 404);
    $file = realpath(Storage::disk('public')->path($folder.'/'.$filename));
    abort_unless($file !== false && dirname($file) === $root && is_file($file) && is_readable($file), 404);

    return response()->file($file, [
        'Content-Type' => $type,
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('folder', 'content|gallery')
    ->where('filename', '[A-Za-z0-9_-]{1,120}\.(?:jpe?g|png|webp|avif|gif|bmp|ico)')
    ->name('site.uploaded-image');

Route::get('/{slug}', [SiteController::class, 'page'])->where('slug', '[a-z0-9-]+')->name('site.page');

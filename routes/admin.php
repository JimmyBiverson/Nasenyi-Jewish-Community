<?php

use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ContentMediaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationReportController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\RequireStaff;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function (): void {
    Route::get('login', [SessionController::class, 'create'])->middleware('guest')->name('login');
    Route::post('login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:8,1'])->name('admin.login.store');
    Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth')->name('admin.logout');

    Route::name('admin.')->middleware(['auth', 'auth.session', RequireStaff::class])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('content', ContentController::class)->except('show');
        Route::prefix('content/{content}/media')->name('content.media.')->controller(ContentMediaController::class)
            ->group(function (): void {
                Route::get('/', 'edit')->name('edit');
                Route::patch('featured', 'featured')->name('featured');
                Route::patch('banner', 'header')->name('header');
                Route::patch('section/{image}', 'section')->where('image', '[0-9a-f]{64}')->name('section');
                Route::patch('slide/{slide}', 'slide')->whereNumber('slide')->name('slide');
            });
        Route::prefix('manage/{collection}')
            ->where(['collection' => 'gallery|events|causes'])
            ->name('collections.')
            ->controller(CollectionController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{record}/edit', 'edit')->whereNumber('record')->name('edit');
                Route::patch('{record}', 'update')->whereNumber('record')->name('update');
                Route::delete('{record}', 'destroy')->whereNumber('record')->name('destroy');
            });

        Route::middleware(RequireStaff::class.':admin')->group(function (): void {
            Route::get('donations', [DonationReportController::class, 'index'])->name('donations.index');
            Route::get('donations/{donation}', [DonationReportController::class, 'show'])->name('donations.show');
            Route::get('inbox', [InboxController::class, 'index'])->name('inbox.index');
            Route::get('inbox/{message}', [InboxController::class, 'show'])->name('inbox.show');
            Route::patch('inbox/{message}', [InboxController::class, 'update'])->name('inbox.update');
            Route::delete('inbox/{message}', [InboxController::class, 'destroy'])->name('inbox.destroy');
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/pesapal', [SettingsController::class, 'payments'])->middleware('throttle:6,1')->name('settings.payments');
            Route::resource('users', UserController::class)->except('show');
        });
    });
});

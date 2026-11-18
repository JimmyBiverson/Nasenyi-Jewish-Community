<?php

use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;

Route::prefix('donation')->name('donations.')->controller(DonationController::class)->group(function (): void {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'store')->middleware('throttle:8,1,donation-start')->name('store');
    Route::get('/callback', 'callback')->middleware('throttle:120,1,donation-callback')->name('callback');
    Route::get('/ipn', 'ipn')->middleware('throttle:120,1,donation-ipn')->name('ipn');
    Route::get('/cancel/{donation:reference}', 'cancel')->whereUuid('donation')
        ->middleware('signed:relative,OrderTrackingId,OrderNotificationType,OrderMerchantReference')->name('cancel');
    Route::get('/status/{donation:reference}', 'status')->whereUuid('donation')->middleware('signed:relative')->name('status');
    Route::post('/status/{donation:reference}/refresh', 'refreshStatus')->whereUuid('donation')
        ->middleware(['signed:relative', 'throttle:8,1,donation-status'])->name('refresh');
});

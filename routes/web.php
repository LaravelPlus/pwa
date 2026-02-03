<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Pwa\Http\Controllers\BackgroundSyncController;
use LaravelPlus\Pwa\Http\Controllers\ManifestController;
use LaravelPlus\Pwa\Http\Controllers\PushSubscriptionController;
use LaravelPlus\Pwa\Http\Controllers\ServiceWorkerController;

Route::middleware('web')->group(function (): void {
    Route::get('manifest.webmanifest', ManifestController::class)->name('pwa.manifest');
    Route::get('sw.js', ServiceWorkerController::class)->name('pwa.serviceworker');
    Route::get('offline', fn () => response()->file(
        __DIR__ . '/../resources/stubs/offline.html',
        ['Content-Type' => 'text/html'],
    ))->name('pwa.offline');
});

Route::middleware(['web', 'auth'])->prefix('api/pwa')->name('pwa.')->group(function (): void {
    Route::post('push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    Route::post('sync', BackgroundSyncController::class)->name('sync');
});

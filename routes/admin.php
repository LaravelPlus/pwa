<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Pwa\Http\Controllers\Admin\PwaController;

$config = config('pwa.admin', []);
$prefix = $config['prefix'] ?? 'admin/pwa';
$middleware = $config['middleware'] ?? ['web', 'auth'];

Route::middleware($middleware)
    ->prefix($prefix)
    ->name('admin.pwa.')
    ->group(function (): void {
        Route::get('/', [PwaController::class, 'index'])->name('index');
        Route::patch('/', [PwaController::class, 'update'])->name('update');
    });

<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Controllers;

use Illuminate\Http\Response;
use LaravelPlus\Pwa\Services\ServiceWorkerService;

final class ServiceWorkerController
{
    public function __construct(
        private(set) ServiceWorkerService $serviceWorkerService,
    ) {}

    public function __invoke(): Response
    {
        return response($this->serviceWorkerService->render())
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Service-Worker-Allowed', '/');
    }
}

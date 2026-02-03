<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\Pwa\Services\ServiceWorkerService;

final class GenerateServiceWorkerCommand extends Command
{
    protected $signature = 'pwa:generate-sw';

    protected $description = 'Generate the service worker file to public/sw.js.';

    public function handle(ServiceWorkerService $serviceWorkerService): int
    {
        $path = public_path('sw.js');

        file_put_contents($path, $serviceWorkerService->render());

        $this->components->info("Service worker generated at: {$path}");

        return self::SUCCESS;
    }
}

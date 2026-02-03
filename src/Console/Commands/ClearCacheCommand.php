<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Console\Commands;

use Illuminate\Console\Command;

final class ClearCacheCommand extends Command
{
    protected $signature = 'pwa:clear-cache';

    protected $description = 'Invalidate the PWA service worker cache by bumping the version.';

    public function handle(): int
    {
        $version = 'v' . time();
        $path = storage_path('pwa-cache-version.txt');

        file_put_contents($path, $version);

        $this->components->info("PWA cache version set to: {$version}");
        $this->line('Clients will refresh their cache on next service worker fetch.');

        return self::SUCCESS;
    }
}
